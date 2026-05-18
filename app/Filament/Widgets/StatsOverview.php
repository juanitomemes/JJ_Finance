<?php

namespace App\Filament\Widgets;

use App\Models\Movimiento;
use App\Models\Presupuesto;
use App\Models\Cuenta;
use App\Models\MetaAhorro;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $mesActual = Carbon::now()->month;
        $anioActual = Carbon::now()->year;
        $mesIngles = Carbon::now()->format('F');

        $ingresos = Movimiento::where('user_id', auth()->id())
            ->where('tipo', 'ingreso')
            ->whereMonth('fecha', $mesActual)
            ->whereYear('fecha', $anioActual)
            ->sum('monto');

        $gastos = Movimiento::where('user_id', auth()->id())
            ->where('tipo', 'gasto')
            ->whereMonth('fecha', $mesActual)
            ->whereYear('fecha', $anioActual)
            ->sum('monto');

        $presupuestoAsignado = Presupuesto::where('user_id', auth()->id())
            ->where('mes', $mesIngles)
            ->where('anio', $anioActual)
            ->sum('monto_asignado');

        $balance = $ingresos - $gastos;

        $capitalTotal = Cuenta::where('user_id', auth()->id())->sum('saldo_actual');
        $totalAhorrado = MetaAhorro::where('user_id', auth()->id())->sum('monto_actual');

        $mesesEspanol = [
            'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
            'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
            'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
            'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
        ];
        $mesNombre = $mesesEspanol[$mesIngles] ?? $mesIngles;

        return [
            Stat::make('Ingresos del Mes', '$' . number_format($ingresos, 2))
                ->description("Total ingresado en {$mesNombre} {$anioActual}")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            Stat::make('Gastos del Mes', '$' . number_format($gastos, 2))
                ->description("Total gastado en {$mesNombre} {$anioActual}")
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            
            Stat::make('Balance General', '$' . number_format($balance, 2))
                ->description('Presupuesto total asignado: $' . number_format($presupuestoAsignado, 2))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($balance >= 0 ? 'success' : 'danger'),

            Stat::make('Capital Total', '$' . number_format($capitalTotal, 2))
                ->description('Balance en todas tus cuentas')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('primary'),

            Stat::make('Ahorro Total', '$' . number_format($totalAhorrado, 2))
                ->description('Fondos guardados en tus metas')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning'),
        ];
    }
}
