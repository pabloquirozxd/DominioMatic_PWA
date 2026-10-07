<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Clientes - {{ $company->name ?? 'DominioMatic' }}</title>
    <style>
        @page {
            margin: 25px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
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
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 8px;
            text-align: left;
            border: 1px solid #005f8c;
        }
        table.data-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            vertical-align: middle;
        }

        /* Filas diferenciadas */
        .client-row td {
            background-color: #f8fafc;
            border-top: 2px solid #cbd5e1;
            font-size: 11px;
        }
        .contact-row td {
            background-color: #ffffff;
            font-size: 10px;
            color: #4b5563;
        }

        /* Badges & Textos */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: bold;
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .text-subtle { color: #9ca3af; }
        .sub-label { color: #0072A8; font-weight: bold; font-size: 10px; }
        .sub-label-sec { color: #6b7280; font-size: 10px; }
        .arrow-icon { font-weight: normal; margin-right: 2px; }

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
                <h1 class="header-title">{{ $company->name ?? 'DominioMatic' }}</h1>
                <div class="header-subtitle">Reporte General de Clientes y Contactos</div>
            </td>
            <td style="width: 45%; vertical-align: bottom;" class="meta-info">
                <div>Generado por: <strong>{{ $user->name }}</strong></div>
                <div>Fecha: <strong>{{ $generatedAt->format('d/m/Y H:i:s') }}</strong></div>
                <div>Total Clientes: <strong>{{ $clients->count() }}</strong></div>
            </td>
        </tr>
    </table>

    <!-- TABLA DE DATOS -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20px; text-align: center;">#</th>
                <th style="width: 120px;">Tipo / Relación</th>
                <th style="width: 140px;">Nombre / Empresa</th>
                <th>Correo Electrónico</th>
                <th style="width: 85px;">Teléfono</th>
                <th>Sitio Web</th>
                <th style="width: 55px; text-align: center;">Idioma</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $index => $client)
                <!-- FILA PRINCIPAL: CLIENTE / EMPRESA -->
                <tr class="client-row">
                    <td style="text-align: center;"><strong>{{ $index + 1 }}</strong></td>
                    <td><strong>{{ $client->type === 'company' ? 'Empresa' : 'Individual' }}</strong></td>
                    <td><strong>{{ $client->company_name ?? trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? '')) }}</strong></td>
                    <td colspan="2" class="text-subtle" style="text-align: center;">
                        <em>(Ver contactos vinculados)</em>
                    </td>
                    <td>
                        @if($client->website)
                            <a href="{{ $client->website }}" style="color: #0072A8; text-decoration: none;">
                                {{ str_replace(['https://', 'http://', 'www.'], '', $client->website) }}
                            </a>
                        @else
                            —
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <span class="badge">{{ mb_strtoupper($client->language ?? 'Español', 'UTF-8') }}</span>
                    </td>
                </tr>

                <!-- CASO A: CONTACTO PRINCIPAL DIRECTO EN CLIENT -->
                @if($client->first_name || $client->last_name || $client->email || $client->phone)
                <tr class="contact-row">
                    <td></td>
                    <td style="text-align: right; padding-right: 8px;">
                        <span class="sub-label"><span class="arrow-icon">↳</span> Contacto Principal</span>
                    </td>
                    <td>{{ trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? '')) ?: '—' }}</td>
                    <td style="color: #0284c7;">{{ $client->email ?? '—' }}</td>
                    <td>{{ $client->phone ?? '—' }}</td>
                    <td class="text-subtle">—</td>
                    <td class="text-subtle" style="text-align: center;">—</td>
                </tr>
                @endif

                <!-- CASO B: CONTACTOS DE LA RELACIÓN ($client->contacts) -->
                @if($client->relationLoaded('contacts') && $client->contacts->isNotEmpty())
                    @foreach($client->contacts as $cIndex => $contact)
                        @php
                            $isPrimary = $contact->is_primary ?? ($cIndex === 0 && !($client->first_name || $client->email));
                            
                            if ($isPrimary) {
                                $label = 'Contacto Principal';
                            } elseif (!empty($contact->position)) {
                                $label = ucfirst($contact->position);
                            } else {
                                $label = 'Contacto Secundario';
                            }
                        @endphp
                        <tr class="contact-row">
                            <td></td>
                            <td style="text-align: right; padding-right: 8px;">
                                <span class="{{ $isPrimary ? 'sub-label' : 'sub-label-sec' }}">
                                    <span class="arrow-icon">↳</span> {{ $label }}
                                </span>
                            </td>
                            <td>{{ trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? '')) }}</td>
                            <td style="color: #0284c7;">{{ $contact->email ?? '—' }}</td>
                            <td>{{ $contact->phone ?? '—' }}</td>
                            <td class="text-subtle">—</td>
                            <td class="text-subtle" style="text-align: center;">—</td>
                        </tr>
                    @endforeach
                @endif

            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #9ca3af; padding: 20px;">
                        No hay clientes registrados en el sistema.
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