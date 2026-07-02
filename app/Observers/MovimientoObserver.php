<?php

namespace App\Observers;

use App\Models\Movimiento;
use App\Models\Cuenta;
use App\Models\MetaAhorro;
use App\Models\Presupuesto;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class MovimientoObserver
{
    public function created(Movimiento $movimiento): void
    {
        $this->aplicarEfecto($movimiento, 1, false);
    }

    public function updated(Movimiento $movimiento): void
    {
        // Revertir efecto de los valores originales
        $this->aplicarEfecto($movimiento, -1, true);
        
        // Aplicar efecto de los nuevos valores
        $this->aplicarEfecto($movimiento, 1, false);
    }

    public function deleted(Movimiento $movimiento): void
    {
        $this->aplicarEfecto($movimiento, -1, false);
    }

    public function restored(Movimiento $movimiento): void
    {
        $this->aplicarEfecto($movimiento, 1, false);
    }

    public function forceDeleted(Movimiento $movimiento): void
    {
        // forceDeleted no revierte el delta porque el registro ya fue eliminado
        // con soft-delete (deleted) previamente, que ya revirtió los saldos.
    }

    /**
     * Aplica el delta (suma o resta) a la cuenta, meta y presupuesto asociados.
     * @param int $multiplicador 1 para aplicar, -1 para revertir.
     * @param bool $usarOriginales Si es true, extrae los valores de getOriginal().
     */
    private function aplicarEfecto(Movimiento $movimiento, int $multiplicador, bool $usarOriginales): void
    {
        $monto = $usarOriginales ? ($movimiento->getOriginal('monto') ?? 0) : $movimiento->monto;
        $tipo = $usarOriginales ? ($movimiento->getOriginal('tipo') ?? '') : $movimiento->tipo;
        $cuenta_id = $usarOriginales ? $movimiento->getOriginal('cuenta_id') : $movimiento->cuenta_id;
        $cuenta_destino_id = $usarOriginales ? $movimiento->getOriginal('cuenta_destino_id') : $movimiento->cuenta_destino_id;
        $meta_id = $usarOriginales ? $movimiento->getOriginal('meta_id') : $movimiento->meta_id;
        $categoria_id = $usarOriginales ? $movimiento->getOriginal('categoria_id') : $movimiento->categoria_id;
        $fecha = $usarOriginales ? $movimiento->getOriginal('fecha') : $movimiento->fecha;
        $user_id = $usarOriginales ? $movimiento->getOriginal('user_id') : $movimiento->user_id;

        if (!$monto || !$tipo || !$user_id) {
            return;
        }

        $valorEfectivo = $monto * $multiplicador;

        // 1. Afectar Cuenta
        if ($cuenta_id) {
            $cuenta = Cuenta::find($cuenta_id);
            if ($cuenta) {
                if ($tipo === 'ingreso') {
                    $cuenta->saldo_actual += $valorEfectivo;
                } elseif (in_array($tipo, ['gasto', 'ahorro', 'transferencia'])) {
                    $cuenta->saldo_actual -= $valorEfectivo;
                }
                $cuenta->saveQuietly();
            }
        }

        // 1.1 Afectar Cuenta Destino (Solo transferencias)
        if ($tipo === 'transferencia' && $cuenta_destino_id) {
            $cuentaDestino = Cuenta::find($cuenta_destino_id);
            if ($cuentaDestino) {
                $cuentaDestino->saldo_actual += $valorEfectivo;
                $cuentaDestino->saveQuietly();
            }
        }

        // 2. Afectar Meta de Ahorro
        if ($meta_id) {
            $meta = MetaAhorro::find($meta_id);
            if ($meta) {
                if ($tipo === 'ahorro') {
                    $meta->monto_actual += $valorEfectivo;
                } elseif ($tipo === 'ingreso') {
                    $meta->monto_actual -= $valorEfectivo; // Retiros de meta
                }
                $meta->monto_actual = max(0, $meta->monto_actual);
                $meta->saveQuietly();

                if ($multiplicador > 0 && $meta->monto_actual >= $meta->monto_objetivo && $meta->monto_objetivo > 0) {
                    Notification::make()
                        ->title('🎉 ¡Meta Alcanzada!')
                        ->body("Has completado tu meta: **{$meta->nombre}**.")
                        ->success()
                        ->persistent()
                        ->send();
                }
            }
        }

        // 3. Afectar Presupuesto
        if ($tipo === 'gasto' && $categoria_id && $fecha) {
            $carbonFecha = Carbon::parse($fecha);
            $presupuesto = Presupuesto::where('user_id', $user_id)
                ->where('categoria_id', $categoria_id)
                ->where('mes', $carbonFecha->format('F'))
                ->where('anio', $carbonFecha->year)
                ->first();

            if ($presupuesto) {
                $presupuesto->monto_gastado += $valorEfectivo;
                $presupuesto->saveQuietly();

                if ($multiplicador > 0 && $presupuesto->monto_gastado > $presupuesto->monto_asignado) {
                    $excedente = $presupuesto->monto_gastado - $presupuesto->monto_asignado;
                    $formGastado = '$' . number_format($presupuesto->monto_gastado, 2);
                    $formAsignado = '$' . number_format($presupuesto->monto_asignado, 2);
                    $formExcedente = '$' . number_format($excedente, 2);
                    $catNombre = $presupuesto->categoria?->nombre ?? 'la categoría';

                    Notification::make()
                        ->title('¡Presupuesto Excedido!')
                        ->body("Límite de {$formAsignado} superado para {$catNombre}. Gasto total: {$formGastado} (Excedente: {$formExcedente}).")
                        ->warning()
                        ->persistent()
                        ->send();
                }
            }
        }
    }
}
