<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Movimiento;
use Carbon\Carbon;

class Presupuesto extends Model
{
    protected $fillable = [
        'user_id',
        'categoria_id',
        'monto_asignado',
        'monto_gastado',
        'mes',
        'anio',
    ];

    protected static function booted()
    {
        // Usar 'saving' en lugar de 'creating' para que el recálculo se dispare
        // tanto al CREAR como al EDITAR (cambio de mes, año o categoría).
        static::saving(function ($presupuesto) {
            $carbonFecha = Carbon::parse("1 {$presupuesto->mes}");
            $totalGastado = Movimiento::where('user_id', $presupuesto->user_id)
                ->where('categoria_id', $presupuesto->categoria_id)
                ->where('tipo', 'gasto')
                ->whereYear('fecha', $presupuesto->anio)
                ->whereMonth('fecha', $carbonFecha->month)
                ->sum('monto');

            $presupuesto->monto_gastado = $totalGastado;
        });
    }

      //Relacion de muchos a uno con el modelo user
    public function user()
    {
        return $this->belongsTo(User::Class);
    }
     //Relacion de muchos a uno con el modelo categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::Class);
    }
}
