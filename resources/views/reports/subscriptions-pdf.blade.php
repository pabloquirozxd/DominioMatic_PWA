<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Suscripciones - {{ $company->name ?? 'DominioMatic' }}</title>
    <style>
        @page {
            margin: 25px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.4;
        }

        /* Header Corporativo */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border-bottom: 2px solid #0072A8;
            padding-bottom: 8px;
        }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #0072A8;
            margin: 0;
        }
        .header-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
        }
        .meta-info {
            font-size: 9.5px;
            color: #374151;
            text-align: right;
        }
        .meta-info strong {
            color: #111827;
        }

        /* Caja de Resumen */
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0072A8;
            padding: 8px 12px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .summary-box td {
            font-size: 11px;
        }

        /* Tabla Principal */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        table.data-table th {
            background-color: #0072A8;
            color: #ffffff;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 6px;
            text-align: left;
            border: 1px solid #005f8c;
        }
        table.data-table td {
            border: 1px solid #e5e7eb;
            padding: 6px;
            vertical-align: middle;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        /* Utilidades y Badges */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-semibold { font-weight: bold; }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-active { background-color: #dcfce7; color: #15803d; }
        .badge-canceled { background-color: #fee2e2; color: #b91c1c; }
        .badge-pending { background-color: #fef3c7; color: #b45309; }
        .badge-default { background-color: #f3f4f6; color: #4b5563; }

        /* Pie de página flotante */
        .footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8.5px;
            color: #9ca3af;
            border-top: 1px solid #f3f4f6;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: bottom;">
                <h1 class="header-title">Reporte de Suscripciones</h1>
                <div class="header-subtitle">{{ $company->name ?? 'Empresa no especificada' }}</div>
            </td>
            <td style="width: 45%; vertical-align: bottom;" class="meta-info">
                <div>Generado por: <strong>{{ $user->name }}</strong></div>
                <div>Fecha: <strong>{{ $generatedAt->format('d/m/Y H:i') }}</strong></div>
                <div>Total suscripciones: <strong>{{ $subscriptions->count() }}</strong></div>
            </td>
        </tr>
    </table>

    <!-- TARJETA DE RESUMEN ACUMULADO -->
    <table class="summary-box" style="width: 100%;">
        <tr>
            <td style="width: 50%;">
                <strong>Métrica General:</strong> Catálogo de suscripciones activas y registradas.
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Total Neto Acumulado:</strong> 
                <span style="font-size: 13px; color: #0072A8; font-weight: bold;">
                    USD {{ number_format((float) $subscriptions->sum('total_neto'), 2) }}
                </span>
            </td>
        </tr>
    </table>

    <!-- TABLA DE DATOS -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22%;">Cliente</th>
                <th style="width: 22%;">Producto / Servicio</th>
                <th style="width: 11%;" class="text-right">Precio</th>
                <th style="width: 10%;" class="text-right">Desc.</th>
                <th style="width: 12%;" class="text-right">Total Neto</th>
                <th style="width: 8%;" class="text-center">Inicio</th>
                <th style="width: 8%;" class="text-center">Vence</th>
                <th style="width: 7%;" class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($subscriptions as $subscription)
                @php
                    // Resolver cliente (Empresa o Persona)
                    $clientName = $subscription->client->company_name 
                        ?? trim(($subscription->client->first_name ?? '') . ' ' . ($subscription->client->last_name ?? ''));

                    // Resolver badge de estado
                    $status = strtolower($subscription->status ?? '');
                    $badgeClass = match ($status) {
                        'activa', 'active' => 'badge-active',
                        'cancelada', 'canceled' => 'badge-canceled',
                        'pendiente', 'pending' => 'badge-pending',
                        default => 'badge-default',
                    };
                @endphp
                <tr>
                    <td class="font-semibold" style="color: #111827;">
                        {{ $clientName ?: 'Sin cliente' }}
                    </td>
                    <td style="color: #374151;">
                        {{ optional($subscription->product)->name ?? 'Sin producto' }}
                    </td>
                    <td class="text-right" style="color: #4b5563;">
                        {{ number_format((float) $subscription->price_list, 2) }}
                    </td>
                    <td class="text-right" style="color: #dc2626;">
                        {{ number_format((float) $subscription->discount, 2) }}
                    </td>
                    <td class="text-right font-semibold" style="color: #0284c7;">
                        {{ number_format((float) $subscription->total_neto, 2) }}
                    </td>
                    <td class="text-center" style="color: #6b7280;">
                        {{ optional($subscription->starts_at)->format('d/m/Y') }}
                    </td>
                    <td class="text-center" style="color: #6b7280;">
                        {{ optional($subscription->expires_at)->format('d/m/Y') }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">
                            {{ ucfirst($subscription->status ?? 'N/A') }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="color: #9ca3af; padding: 20px;">
                        No existen suscripciones registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        DominioMatic PWA — Documento generado automáticamente.
    </div>

</body>
</html>