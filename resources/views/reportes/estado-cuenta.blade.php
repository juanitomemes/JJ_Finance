<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Cuenta</title>
    <style>
        @page {
            margin: 40px 50px;
        }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            color: #334155; /* Slate 700 */
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #0f766e; /* Teal 700 */
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .header table {
            width: 100%;
        }
        .header td {
            vertical-align: middle;
        }
        .title {
            color: #1e293b; /* Slate 900 */
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 5px 0;
        }
        .subtitle {
            color: #64748b; /* Slate 500 */
            font-size: 14px;
            margin: 0;
        }
        .info-box {
            text-align: right;
        }
        .info-box p {
            margin: 3px 0;
            font-size: 12px;
        }
        .info-box strong {
            color: #0f766e; /* Teal 700 */
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e293b;
            margin-top: 30px;
            margin-bottom: 10px;
            border-bottom: 1px solid #cbd5e1; /* Slate 300 */
            padding-bottom: 5px;
        }

        /* Resumen Cards */
        .summary-cards {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: separate;
            border-spacing: 10px 0; /* Add horizontal spacing between table cells */
            margin-left: -10px; /* Offset spacing */
            margin-right: -10px;
        }
        .summary-card {
            background-color: #f8fafc; /* Slate 50 */
            border: 1px solid #e2e8f0; /* Slate 200 */
            padding: 15px;
            text-align: center;
            border-radius: 6px;
            width: 33.33%;
        }
        .summary-label {
            display: block;
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .summary-value {
            display: block;
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
        }
        .text-success { color: #059669; } /* Emerald 600 */
        .text-danger { color: #e11d48; }  /* Rose 600 */

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .data-table th {
            background-color: #1e293b; /* Slate 900 */
            color: #ffffff;
            font-size: 11px;
            text-transform: uppercase;
            padding: 10px;
            text-align: left;
        }
        .data-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0; /* Slate 200 */
            font-size: 12px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc; /* Slate 50 */
        }
        .badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            color: #fff;
            text-transform: uppercase;
            display: inline-block;
        }
        .badge-ingreso { background-color: #10b981; } /* Emerald 500 */
        .badge-gasto { background-color: #f43f5e; } /* Rose 500 */
        .badge-ahorro { background-color: #3b82f6; } /* Blue 500 */
        
        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8; /* Slate 400 */
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <h1 class="title">Estado de Cuenta</h1>
                    <p class="subtitle">Reporte Financiero Mensual</p>
                </td>
                <td class="info-box">
                    <p>Usuario: <strong>{{ $usuario->name }}</strong></p>
                    <p>Periodo: <strong>{{ $mesNombre }} {{ $anio }}</strong></p>
                    <p>Fecha Generación: <strong>{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</strong></p>
                </td>
            </tr>
        </table>
    </div>

    <!-- KPIs Mensuales -->
    <table class="summary-cards">
        <tr>
            <td class="summary-card">
                <span class="summary-label">Total Ingresos</span>
                <span class="summary-value text-success">${{ number_format($totalIngresos, 2) }}</span>
            </td>
            <td class="summary-card">
                <span class="summary-label">Total Gastos</span>
                <span class="summary-value text-danger">${{ number_format($totalGastos, 2) }}</span>
            </td>
            <td class="summary-card">
                <span class="summary-label">Balance Neto</span>
                <span class="summary-value {{ $balanceNeto >= 0 ? 'text-success' : 'text-danger' }}">
                    ${{ number_format($balanceNeto, 2) }}
                </span>
            </td>
        </tr>
    </table>

    <div class="section-title">Resumen de Cuentas (Saldos Actuales)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Cuenta / Monedero</th>
                <th>Tipo</th>
                <th style="text-align: right;">Saldo Actual</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cuentas as $cuenta)
                <tr>
                    <td><strong>{{ $cuenta->nombre }}</strong></td>
                    <td style="text-transform: capitalize;">{{ $cuenta->tipo }}</td>
                    <td style="text-align: right; font-weight: bold;">
                        ${{ number_format($cuenta->saldo_actual, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">No hay cuentas registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Detalle de Movimientos del Periodo</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Concepto / Descripción</th>
                <th>Cuenta</th>
                <th>Categoría</th>
                <th>Tipo</th>
                <th style="text-align: right;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $movimiento)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>
                    <td>{!! strip_tags($movimiento->descripcion) !!}</td>
                    <td>{{ $movimiento->cuenta->nombre ?? 'N/A' }}</td>
                    <td>{{ $movimiento->categoria->nombre ?? 'N/A' }}</td>
                    <td>
                        <span class="badge badge-{{ $movimiento->tipo }}">
                            {{ $movimiento->tipo }}
                        </span>
                    </td>
                    <td style="text-align: right; font-weight: bold;" class="{{ $movimiento->tipo === 'ingreso' ? 'text-success' : ($movimiento->tipo === 'gasto' ? 'text-danger' : '') }}">
                        ${{ number_format($movimiento->monto, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No se encontraron movimientos en este periodo.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generado automáticamente por el Sistema de Finanzas Personales. Este documento es un reporte informativo interno.
    </div>

</body>
</html>
