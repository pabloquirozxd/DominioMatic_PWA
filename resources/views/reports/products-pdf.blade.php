<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productos</title>
    <style>
        @page {
            margin: 30px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.4;
        }
        /* Header en 2 columnas con tabla */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #0072A8;
            padding-bottom: 10px;
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
            margin-top: 3px;
        }
        .meta-table {
            text-align: right;
            font-size: 9.5px;
            color: #374151;
        }
        .meta-table strong {
            color: #111827;
        }

        /* Tabla Principal */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #0072A8;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 8px;
            text-align: left;
        }
        table.data-table td {
            border-bottom: 1px solid #e5e7eb;
            padding: 8px;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        /* Clases de utilidad y badging */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-semibold { font-weight: bold; }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8.5px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-infinite {
            background-color: #f3f4f6;
            color: #4b5563;
        }
        .badge-finite {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        /* Footer flotante */
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

    <!-- Encabezado con estructura B2B -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: bottom;">
                <h1 class="header-title">Reporte de Productos</h1>
                <div class="header-subtitle">{{ $company->name ?? 'Empresa no especificada' }}</div>
            </td>
            <td style="width: 45%; vertical-align: bottom;" class="meta-table">
                <div>Generado por: <strong>{{ $user->name }}</strong></div>
                <div>Fecha de emisión: <strong>{{ $generatedAt->format('d/m/Y H:i') }}</strong></div>
                <div>Total ítems: <strong>{{ $products->count() }}</strong></div>
            </td>
        </tr>
    </table>

    <!-- Tabla con anchos definidos y formateo financiero -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22%;">Nombre</th>
                <th style="width: 33%;">Descripción</th>
                <th style="width: 15%;" class="text-right">Precio Lista</th>
                <th style="width: 10%;" class="text-center">Tipo</th>
                <th style="width: 10%;" class="text-center">Stock</th>
                <th style="width: 10%;" class="text-center">Registro</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td class="font-semibold" style="color: #111827;">
                        {{ $product->name }}
                    </td>
                    <td style="color: #4b5563;">
                        {{ $product->description ?? '—' }}
                    </td>
                    <td class="text-right font-semibold" style="color: #0284c7;">
                        {{ $product->currency ?? 'USD' }} {{ number_format((float) $product->price_list, 2) }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $product->is_infinite ? 'badge-infinite' : 'badge-finite' }}">
                            {{ $product->is_infinite ? 'Ilimitado' : 'Finito' }}
                        </span>
                    </td>
                    <td class="text-center" style="color: #374151;">
                        {{ $product->is_infinite ? 'N/A' : $product->stock }}
                    </td>
                    <td class="text-center" style="color: #6b7280;">
                        {{ optional($product->created_at)->format('d/m/Y') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="color: #9ca3af; padding: 20px;">
                        No existen productos registrados en el catálogo.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pie de página -->
    <div class="footer">
        DominioMatic PWA — Documento generado automáticamente.
    </div>

</body>
</html>