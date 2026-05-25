<?php

namespace App\Filament\Widgets;

use App\Models\Movimiento;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IngresosGastosChart extends ChartWidget
{
    protected static ?string $heading = 'Flujo de Caja (Últimos 6 meses)';
    protected static ?int $sort = 2;
    
    // Set column span to full for a wider, beautiful chart
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $mesesEspanol = [
            'January' => 'Ene', 'February' => 'Feb', 'March' => 'Mar',
            'April' => 'Abr', 'May' => 'May', 'June' => 'Jun',
            'July' => 'Jul', 'August' => 'Ago', 'September' => 'Sep',
            'October' => 'Oct', 'November' => 'Nov', 'December' => 'Dic'
        ];

        // --- OPTIMIZACIÓN: 1 sola consulta SQL agrupada en lugar de 12 consultas individuales ---
        $fechaInicio = Carbon::now()->subMonths(5)->startOfMonth();

        $driver = DB::connection()->getDriverName();
        $yearRaw = $driver === 'pgsql' ? 'EXTRACT(YEAR FROM fecha)' : ($driver === 'sqlite' ? "strftime('%Y', fecha)" : 'YEAR(fecha)');
        $monthRaw = $driver === 'pgsql' ? 'EXTRACT(MONTH FROM fecha)' : ($driver === 'sqlite' ? "strftime('%m', fecha)" : 'MONTH(fecha)');

        $rawData = Movimiento::select(
                DB::raw("$yearRaw as anio"),
                DB::raw("$monthRaw as mes_num"),
                'tipo',
                DB::raw('SUM(monto) as total')
            )
            ->where('user_id', auth()->id())
            ->where('fecha', '>=', $fechaInicio)
            ->whereIn('tipo', ['ingreso', 'gasto'])
            ->groupBy(DB::raw($yearRaw), DB::raw($monthRaw), 'tipo')
            ->get()
            ->groupBy(fn ($row) => "{$row->anio}-{$row->mes_num}");

        // Construir los arrays en el orden correcto (de más antiguo a más reciente)
        $meses = [];
        $ingresosData = [];
        $gastosData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = "{$date->year}-{$date->month}";
            $mesAbrev = $mesesEspanol[$date->format('F')] ?? $date->format('M');

            $meses[] = "{$mesAbrev} {$date->year}";

            $grupo = $rawData->get($key, collect());

            $ingresosData[] = (float) ($grupo->firstWhere('tipo', 'ingreso')?->total ?? 0);
            $gastosData[]   = (float) ($grupo->firstWhere('tipo', 'gasto')?->total ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $ingresosData,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                    'fill' => 'start',
                ],
                [
                    'label' => 'Gastos',
                    'data' => $gastosData,
                    'backgroundColor' => '#ef4444',
                    'borderColor' => '#ef4444',
                    'fill' => 'start',
                ],
            ],
            'labels' => $meses,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
