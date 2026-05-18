<?php

namespace App\Filament\Widgets;

use App\Models\Presupuesto;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class PresupuestosChart extends ChartWidget
{
    protected static ?string $heading = 'Estado de Presupuestos (Mes Actual)';
    protected static ?int $sort = 3;

    // Use full width for better visualization
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $mesIngles = Carbon::now()->format('F');
        $anioActual = Carbon::now()->year;

        // Obtener todos los presupuestos del mes actual junto con sus categorías
        $presupuestos = Presupuesto::with('categoria')
            ->where('user_id', auth()->id())
            ->where('mes', $mesIngles)
            ->where('anio', $anioActual)
            ->get();

        $labels = [];
        $asignadoData = [];
        $gastadoData = [];

        foreach ($presupuestos as $presupuesto) {
            $nombreCategoria = $presupuesto->categoria ? $presupuesto->categoria->nombre : 'Sin Categoría';
            $labels[] = $nombreCategoria;
            $asignadoData[] = $presupuesto->monto_asignado;
            $gastadoData[] = $presupuesto->monto_gastado;
        }

        // Si no hay datos, mostrar algo por defecto para evitar una gráfica vacía fea
        if ($presupuestos->isEmpty()) {
            $labels = ['Sin presupuestos registrados este mes'];
            $asignadoData = [0];
            $gastadoData = [0];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Presupuesto Asignado',
                    'data' => $asignadoData,
                    'backgroundColor' => '#3b82f6', // Blue 500
                    'borderColor' => '#3b82f6',
                ],
                [
                    'label' => 'Monto Gastado',
                    'data' => $gastadoData,
                    'backgroundColor' => '#f59e0b', // Amber 500
                    'borderColor' => '#f59e0b',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
