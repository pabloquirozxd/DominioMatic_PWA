<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .meta { margin-bottom: 18px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1D4E89; color: white; padding: 8px; text-align: left; }
        td { border-bottom: 1px solid #e5e7eb; padding: 7px; }
        .footer { margin-top: 18px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Reporte de Productos</h1>

    <div class="meta">
        <strong>Empresa:</strong> {{ $company->name ?? 'Sin empresa' }}<br>
        <strong>Generado por:</strong> {{ $user->name }}<br>
        <strong>Fecha:</strong> {{ $generatedAt->format('d/m/Y H:i') }}<br>
        <strong>Total de productos:</strong> {{ $products->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio lista</th>
                <th>Tipo</th>
                <th>Stock</th>
                <th>Registro</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->description }}</td>
                    <td>{{ number_format((float) $product->price_list, 2) }}</td>
                    <td>{{ $product->is_infinite ? 'Ilimitado' : 'Finito' }}</td>
                    <td>{{ $product->is_infinite ? 'No aplica' : $product->stock }}</td>
                    <td>{{ optional($product->created_at)->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No existen productos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        DominioMatic PWA - Reporte generado automáticamente.
    </div>
</body>
</html>