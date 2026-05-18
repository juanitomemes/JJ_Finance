<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Presupuesto;
use Carbon\Carbon;

class Movimiento extends Model
{
    protected $fillable = [
        'user_id',
        'categoria_id',
        'cuenta_id',
        'meta_id',
        'tipo',
        'monto',
        'descripcion',
        'foto',
        'fecha',
    ];

    //Relacion de muchos a uno con el modelo usuario
    public function user()
    {
        return $this->belongsTo(User::Class);
    }
    //Relacion de muchos a uno con el modelo categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    //Relacion de muchos a uno con el modelo cuenta
    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class);
    }

    public function metaAhorro()
    {
        return $this->belongsTo(MetaAhorro::class, 'meta_id');
    }

    // Eventos para actualizar de forma robusta el presupuesto asociado y la cuenta
    protected static function booted()
    {
        static::saved(function ($movimiento) {
            // Sincronizar el presupuesto nuevo/actual
            if ($movimiento->tipo === 'gasto') {
                self::actualizarPresupuestoConDatos($movimiento->user_id, $movimiento->categoria_id, $movimiento->fecha);
            }

            // Recalcular saldo de la cuenta afectada
            if ($movimiento->cuenta_id) {
                Cuenta::recalcularSaldo($movimiento->cuenta_id);
            }

            if ($movimiento->meta_id) {
                MetaAhorro::recalcularMontoActual($movimiento->meta_id);
            }

            // Si cambió de categoría, usuario, fecha o tipo, recalcular también el presupuesto anterior
            if ($movimiento->wasChanged(['user_id', 'categoria_id', 'fecha', 'tipo'])) {
                $oldUserId = $movimiento->getOriginal('user_id') ?? $movimiento->user_id;
                $oldCategoriaId = $movimiento->getOriginal('categoria_id') ?? $movimiento->categoria_id;
                $oldFecha = $movimiento->getOriginal('fecha') ?? $movimiento->fecha;
                $oldTipo = $movimiento->getOriginal('tipo') ?? $movimiento->tipo;

                if ($oldTipo === 'gasto') {
                    self::actualizarPresupuestoConDatos($oldUserId, $oldCategoriaId, $oldFecha);
                }
            }

            // Si se cambió de cuenta, actualizar la cuenta anterior también
            if ($movimiento->wasChanged('cuenta_id')) {
                $oldCuentaId = $movimiento->getOriginal('cuenta_id');
                if ($oldCuentaId) {
                    Cuenta::recalcularSaldo($oldCuentaId);
                }
            }

            // Si cambió de meta, actualizar la meta anterior también
            if ($movimiento->wasChanged('meta_id')) {
                $oldMetaId = $movimiento->getOriginal('meta_id');
                if ($oldMetaId) {
                    MetaAhorro::recalcularMontoActual($oldMetaId);
                }
            }
            
            // Si cambió el monto o tipo del movimiento y ya calculamos la nueva cuenta, 
            // no hace falta otro trigger, pero wasChanged de monto/tipo ya fue evaluado arriba para presupuesto.
            // La cuenta nueva ya fue recalculada con el nuevo monto/tipo arriba.
        });

        static::deleted(function ($movimiento) {
            if ($movimiento->tipo === 'gasto') {
                self::actualizarPresupuestoConDatos($movimiento->user_id, $movimiento->categoria_id, $movimiento->fecha);
            }

            if ($movimiento->cuenta_id) {
                Cuenta::recalcularSaldo($movimiento->cuenta_id);
            }

            if ($movimiento->meta_id) {
                MetaAhorro::recalcularMontoActual($movimiento->meta_id);
            }
        });
    }

    /**
     * Recalcula y actualiza de forma segura el monto_gastado del presupuesto para el periodo dado.
     */
    public static function actualizarPresupuestoConDatos($userId, $categoriaId, $fecha)
    {
        if (!$userId || !$categoriaId || !$fecha) {
            return;
        }

        $carbonFecha = Carbon::parse($fecha);
        $mesName = $carbonFecha->format('F'); // Nombre del mes en inglés, ej: "May"
        $anio = $carbonFecha->year;

        $presupuesto = Presupuesto::where('user_id', $userId)
            ->where('categoria_id', $categoriaId)
            ->where('mes', $mesName)
            ->where('anio', $anio)
            ->first();

        if ($presupuesto) {
            // Calcular la suma de todos los movimientos de tipo 'gasto' para este usuario, categoría y periodo
            $totalGastado = self::where('user_id', $userId)
                ->where('categoria_id', $categoriaId)
                ->where('tipo', 'gasto')
                ->whereYear('fecha', $anio)
                ->whereMonth('fecha', $carbonFecha->month)
                ->sum('monto');

            $presupuesto->monto_gastado = $totalGastado;
            $presupuesto->save();

            // Alerta si supera el presupuesto asignado
            if ($presupuesto->monto_gastado > $presupuesto->monto_asignado) {
                $excedente = $presupuesto->monto_gastado - $presupuesto->monto_asignado;
                
                // Formatear montos a moneda legible
                $formGastado = '$' . number_format($presupuesto->monto_gastado, 2);
                $formAsignado = '$' . number_format($presupuesto->monto_asignado, 2);
                $formExcedente = '$' . number_format($excedente, 2);

                $categoriaNombre = $presupuesto->categoria?->nombre ?? 'la categoría';
                $mesesEspanol = [
                    'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
                    'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
                    'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
                    'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
                ];
                $mesEs = $mesesEspanol[$mesName] ?? $mesName;

                if (class_exists(\Filament\Notifications\Notification::class)) {
                    \Filament\Notifications\Notification::make()
                        ->title('¡Presupuesto Excedido!')
                        ->body("Has superado el límite de {$formAsignado} para la categoría {$categoriaNombre} en {$mesEs} de {$anio}. Gasto total: {$formGastado} (Excedente: {$formExcedente}).")
                        ->warning()
                        ->persistent()
                        ->send();
                }
            }
        }
    }
}
