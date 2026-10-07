<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Contactos - {{ $company->name ?? 'DominioMatic' }}</title>
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
            margin-bottom: 18px;
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
            padding: 7px 8px;
            text-align: left;
            border: 1px solid #005f8c;
        }
        table.data-table td {
            border: 1px solid #e5e7eb;
            padding: 7px 8px;
            vertical-align: middle;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        /* Utilidades */
        .text-center { text-align: center; }
        .font-semibold { font-weight: bold; }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-primary {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-secondary {
            background-color: #f3f4f6;
            color: #4b5563;
        }

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
                <h1 class="header-title">Reporte de Contactos</h1>
                <div class="header-subtitle">{{ $company->name ?? 'Empresa no especificada' }}</div>
            </td>
            <td style="width: 45%; vertical-align: bottom;" class="meta-info">
                <div>Generado por: <strong>{{ $user->name }}</strong></div>
                <div>Fecha: <strong>{{ $generatedAt->format('d/m/Y H:i') }}</strong></div>
                <div>Total contactos: <strong>{{ $contacts->count() }}</strong></div>
            </td>
        </tr>
    </table>

    <!-- TABLA DE DATOS -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Nombre Completo</th>
                <th style="width: 25%;">Correo Electrónico</th>
                <th style="width: 15%;">Teléfono</th>
                <th style="width: 12%;" class="text-center">Tipo / Rol</th>
                <th style="width: 13%;">Cargo / Posición</th>
                <th style="width: 10%;" class="text-center">Registro</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($contacts as $contact)
                @php
                    $fullName = trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? ''));
                    $isPrimary = $contact->is_primary || ($contact->type ?? '') === 'primary';
                @endphp
                <tr>
                    <td class="font-semibold" style="color: #111827;">
                        {{ $fullName ?: '—' }}
                    </td>
                    <td style="color: #0284c7;">
                        {{ $contact->email ?? '—' }}
                    </td>
                    <td style="color: #374151;">
                        {{ $contact->phone ?? '—' }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $isPrimary ? 'badge-primary' : 'badge-secondary' }}">
                            {{ $isPrimary ? 'Principal' : ($contact->type ?? 'Secundario') }}
                        </span>
                    </td>
                    <td style="color: #4b5563;">
                        {{ $contact->position ?? '—' }}
                    </td>
                    <td class="text-center" style="color: #6b7280;">
                        {{ optional($contact->created_at)->format('d/m/Y') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="color: #9ca3af; padding: 20px;">
                        No existen contactos registrados en esta empresa.
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