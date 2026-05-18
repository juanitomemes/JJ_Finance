<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaAhorro extends Model
{
    protected $fillable = [
        'user_id',
        'nombre',
        'monto_objetivo',
        'monto_actual',
        'fecha_limite',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'meta_id');
    }

    public static function recalcularMontoActual(int $metaId): void
    {
        $meta = self::find($metaId);
        if (!$meta) return;

        // Sumar todos los movimientos de tipo 'ahorro' destinados a esta meta
        $aportes = Movimiento::where('meta_id', $metaId)
            ->where('tipo', 'ahorro')
            ->sum('monto');

        // Restar retiros (opcional: movimientos tipo 'ingreso' que salieron de la meta de vuelta a una cuenta)
        $retiros = Movimiento::where('meta_id', $metaId)
            ->where('tipo', 'ingreso')
            ->sum('monto');

        $meta->monto_actual = max(0, $aportes - $retiros);
        $meta->saveQuietly();

        // Notificación si se cumplió la meta
        if ($meta->monto_actual >= $meta->monto_objetivo && $meta->monto_objetivo > 0) {
            if (class_exists(\Filament\Notifications\Notification::class)) {
                \Filament\Notifications\Notification::make()
                    ->title('🎉 ¡Meta Alcanzada!')
                    ->body("¡Felicidades! Has completado el 100% de tu meta: **{$meta->nombre}**.")
                    ->success()
                    ->persistent()
                    ->send();
            }
        }
    }
}
