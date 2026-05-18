<?php

namespace App\Filament\Pages;

use App\Models\Cuenta;
use App\Models\Movimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class Reportes extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Centro de Reportes';

    protected static ?string $title = 'Centro de Reportes';

    protected static string $view = 'filament.pages.reportes';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generar_estado_cuenta')
                ->label('Generar PDF (Estado de Cuenta)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Select::make('mes')
                        ->label('Mes del Reporte')
                        ->options([
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                        ])
                        ->default(Carbon::now()->month)
                        ->required(),
                    Select::make('anio')
                        ->label('Año')
                        ->options(function () {
                            $anios = Movimiento::selectRaw('YEAR(fecha) as anio')
                                ->where('user_id', auth()->id())
                                ->distinct()
                                ->pluck('anio', 'anio')
                                ->toArray();
                            
                            $anioActual = Carbon::now()->year;
                            if (empty($anios)) {
                                $anios[$anioActual] = $anioActual;
                            } elseif (!isset($anios[$anioActual])) {
                                $anios[$anioActual] = $anioActual;
                            }
                            krsort($anios);
                            return $anios;
                        })
                        ->default(Carbon::now()->year)
                        ->required(),
                    Select::make('cuenta_id')
                        ->label('Cuenta Específica (Opcional)')
                        ->placeholder('Todas las Cuentas')
                        ->options(Cuenta::where('user_id', auth()->id())->pluck('nombre', 'id'))
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    $mes = $data['mes'];
                    $anio = $data['anio'];
                    $cuentaId = $data['cuenta_id'] ?? null;

                    $query = Movimiento::with(['cuenta', 'categoria'])
                        ->where('user_id', auth()->id())
                        ->whereMonth('fecha', $mes)
                        ->whereYear('fecha', $anio)
                        ->orderBy('fecha', 'asc');

                    if ($cuentaId) {
                        $query->where('cuenta_id', $cuentaId);
                    }

                    $movimientos = $query->get();

                    if ($movimientos->isEmpty()) {
                        Notification::make()
                            ->title('Sin datos')
                            ->body('No se encontraron movimientos para el periodo seleccionado.')
                            ->warning()
                            ->send();
                        return;
                    }

                    $totalIngresos = $movimientos->where('tipo', 'ingreso')->sum('monto');
                    $totalGastos = $movimientos->where('tipo', 'gasto')->sum('monto');
                    $balanceNeto = $totalIngresos - $totalGastos;

                    $cuentasQuery = Cuenta::where('user_id', auth()->id());
                    if ($cuentaId) {
                        $cuentasQuery->where('id', $cuentaId);
                    }
                    $cuentas = $cuentasQuery->get();

                    $mesesEspanol = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];

                    $pdfData = [
                        'usuario' => auth()->user(),
                        'mesNombre' => $mesesEspanol[(int)$mes],
                        'anio' => $anio,
                        'movimientos' => $movimientos,
                        'totalIngresos' => $totalIngresos,
                        'totalGastos' => $totalGastos,
                        'balanceNeto' => $balanceNeto,
                        'cuentas' => $cuentas,
                    ];

                    $pdf = Pdf::loadView('reportes.estado-cuenta', $pdfData);
                    $nombreArchivo = 'Estado_Cuenta_' . $mesesEspanol[(int)$mes] . '_' . $anio . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $nombreArchivo);
                })
        ];
    }
}
