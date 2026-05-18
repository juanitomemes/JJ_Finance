<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    protected $fillable = [
        'user_id',
        'nombre',
        'tipo',
        'saldo_inicial',
        'saldo_actual',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }

    protected static function booted()
    {
        static::creating(function ($cuenta) {
            $cuenta->saldo_actual = $cuenta->saldo_inicial;
        });

        static::updating(function ($cuenta) {
            if ($cuenta->isDirty('saldo_inicial')) {
                $ingresos = Movimiento::where('cuenta_id', $cuenta->id)
                    ->where('tipo', 'ingreso')
                    ->sum('monto');

                $gastos = Movimiento::where('cuenta_id', $cuenta->id)
                    ->where('tipo', 'gasto')
                    ->sum('monto');

                $ahorros = Movimiento::where('cuenta_id', $cuenta->id)
                    ->where('tipo', 'ahorro')
                    ->sum('monto');

                $cuenta->saldo_actual = $cuenta->saldo_inicial + $ingresos - $gastos - $ahorros;
            }
        });
    }

    /**
     * Recalcula el saldo actual sumando ingresos y restando gastos.
     */
    public static function recalcularSaldo(int $cuentaId): void
    {
        $cuenta = self::find($cuentaId);
        if (!$cuenta) return;

        $ingresos = Movimiento::where('cuenta_id', $cuentaId)
            ->where('tipo', 'ingreso')
            ->sum('monto');

        $gastos = Movimiento::where('cuenta_id', $cuentaId)
            ->where('tipo', 'gasto')
            ->sum('monto');

        $ahorros = Movimiento::where('cuenta_id', $cuentaId)
            ->where('tipo', 'ahorro')
            ->sum('monto');

        $cuenta->saldo_actual = $cuenta->saldo_inicial + $ingresos - $gastos - $ahorros;
        $cuenta->saveQuietly(); // Evita bucles infinitos de eventos
    }
}
