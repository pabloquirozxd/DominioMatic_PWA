<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Suscripciones</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .meta { margin-bottom: 18px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1D4E89; color: white; padding: 8px; text-align: left; }
        td { border-bottom: 1px solid #e5e7eb; padding: 7px; }
        .summary { margin-bottom: 14px; }
        .footer { margin-top: 18px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Reporte de Suscripciones</h1>

    <div class="meta">
        <strong>Empresa:</strong> {{ $company->name ?? 'Sin empresa' }}<br>
        <strong>Generado por:</strong> {{ $user->name }}<br>
        <strong>Fecha:</strong> {{ $generatedAt->format('d/m/Y H:i') }}<br>
        <strong>Total de suscripciones:</strong> {{ $subscriptions->count() }}
    </div>

    <div class="summary">
        <strong>Total neto acumulado:</strong>
        {{ number_format((float) $subscriptions->sum('total_neto'), 2) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Contacto</th>
                <th>Producto / Servicio</th>
                <th>Precio</th>
                <th>Descuento</th>
                <th>Total neto</th>
                <th>Inicio</th>
                <th>Vencimiento</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($subscriptions as $subscription)
                <tr>
                    <td>
                        {{ trim(optional($subscription->contact)->first_name . ' ' . optional($subscription->contact)->last_name) ?: 'Sin contacto' }}
                    </td>
                    <td>{{ optional($subscription->product)->name ?? 'Sin producto' }}</td>
                    <td>{{ number_format((float) $subscription->price_list, 2) }}</td>
                    <td>{{ number_format((float) $subscription->discount, 2) }}</td>
                    <td>{{ number_format((float) $subscription->total_neto, 2) }}</td>
                    <td>{{ optional($subscription->starts_at)->format('d/m/Y') }}</td>
                    <td>{{ optional($subscription->expires_at)->format('d/m/Y') }}</td>
                    <td>{{ $subscription->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No existen suscripciones registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        DominioMatic PWA - Reporte generado automáticamente.
    </div>
</body>
</html>