<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateToNeon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:migrate-to-neon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copia los datos de la base de datos MySQL local a la base de datos PostgreSQL en Neon.tech';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Verificar si la variable DATABASE_URL de Neon está en el .env local
        if (!env('DATABASE_URL')) {
            $this->error('Falta la variable DATABASE_URL en tu archivo .env local.');
            $this->line('Por favor, agrega DATABASE_URL="postgresql://usuario:pass..." a tu .env y vuelve a intentar.');
            return;
        }

        $this->info('🚀 Iniciando migración de datos de MySQL (Local) a PostgreSQL (Neon)...');

        // Definir el orden de las tablas (cuidando las llaves foráneas: primero padres, luego hijos)
        $tablas = [
            'users',
            'cuentas',
            'categorias',
            'movimientos',
            'presupuestos',
            // Agrega aquí otras tablas si existen (ej. 'password_reset_tokens', 'sessions', etc.)
        ];

        // Obtener conexión local (MySQL) y remota (Neon PostgreSQL)
        $localDb = DB::connection('mysql');
        $remoteDb = DB::connection('pgsql');

        foreach ($tablas as $tabla) {
            $this->warn("Procesando tabla: {$tabla}");

            // Obtener todos los registros de la tabla local
            $registros = $localDb->table($tabla)->get();

            if ($registros->isEmpty()) {
                $this->info("  - No hay datos en '{$tabla}', saltando.");
                continue;
            }

            // Deshabilitar temporalmente llaves foráneas en Neon (la sintaxis en Postgres es diferente, 
            // así que en su lugar limpiaremos la tabla y luego insertaremos)
            
            // Borramos los datos existentes en Neon para no duplicar (Opcional, ten cuidado)
            $remoteDb->table($tabla)->delete();

            $barra = $this->output->createProgressBar($registros->count());
            $barra->start();

            // Insertar los registros en lotes para no saturar la memoria
            $lotes = $registros->chunk(100);

            foreach ($lotes as $lote) {
                // Convertir los objetos a arrays asociativos
                $datosInsertar = $lote->map(function ($item) use ($tabla) {
                    $array = (array) $item;
                    
                    // Ajuste de tipos de datos de MySQL a Postgres si es necesario
                    // Por ejemplo, los tinyint(1) de MySQL se leen como enteros, Postgres los toma igual o como booleanos
                    return $array;
                })->toArray();

                // Insertar lote en Neon
                $remoteDb->table($tabla)->insert($datosInsertar);
                $barra->advance($lote->count());
            }

            $barra->finish();
            $this->newLine();
            $this->info("  - ✅ {$registros->count()} registros copiados en '{$tabla}'.");
        }

        // Actualizar las secuencias en PostgreSQL para que los IDs auto-incrementales no choquen
        // Postgres necesita que le digamos "Hey, el último ID usado fue el X, el siguiente debe ser X+1"
        $this->warn("Actualizando secuencias de IDs en PostgreSQL...");
        foreach ($tablas as $tabla) {
            try {
                $maxId = $remoteDb->table($tabla)->max('id') ?? 0;
                // Esto es código SQL nativo de PostgreSQL para ajustar el autoincremento
                $remoteDb->statement("SELECT setval(pg_get_serial_sequence('{$tabla}', 'id'), {$maxId} + 1, false)");
            } catch (\Exception $e) {
                // Si la tabla no tiene 'id' auto-incremental, saltamos el error
            }
        }

        $this->info('🎉 ¡Migración de datos a Neon completada con éxito!');
    }
}
