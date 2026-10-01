<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Clientes - {{ $company->name ?? 'Empresa' }}</title>
<style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px; /* Subido de 10px a 12px */
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #007AFF;
            padding-bottom: 10px;
        }
        .header table { width: 100%; }
        .header h1 {
            margin: 0;
            font-size: 18px; /* Subido de 16px a 18px */
            color: #111827;
        }
        .meta-info {
            font-size: 10px; /* Subido de 9px a 10px */
            color: #6b7280;
            text-align: right;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #e5e7eb;
            padding: 7px 9px; /* Más espacio interior */
            text-align: left;
            vertical-align: middle;
        }
        table.data-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 10px; /* Subido de 9px a 10px */
            text-transform: uppercase;
            color: #374151;
        }
        .client-row td {
            background-color: #f8fafc;
            border-top: 2px solid #cbd5e1;
            font-size: 12px; /* Subido a 12px */
        }
        .contact-row td {
            background-color: #ffffff;
            font-size: 11px; /* Subido a 11px */
            color: #4b5563;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 9.5px;
            font-weight: bold;
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .text-subtle { color: #9ca3af; }
        .sub-label { color: #007AFF; font-weight: bold; font-size: 11px; }
        .sub-label-sec { color: #6b7280; font-size: 11px; }
        .arrow-icon { font-family: 'DejaVu Sans', sans-serif; font-weight: normal; margin-right: 2px; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <h1>{{ $company->name ?? 'DominioMatic' }}</h1>
                    <p style="margin: 2px 0 0 0; color: #6b7280;">Reporte General de Clientes y Contactos</p>
                </td>
                <td class="meta-info">
                    <p style="margin: 0;">Generado por: {{ $user->name }}</p>
                    <p style="margin: 2px 0 0 0;">Fecha: {{ $generatedAt->format('d/m/Y H:i:s') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20px; text-align: center;">#</th>
                <th style="width: 130px;">Tipo / Relación</th>
                <th style="width: 150px;">Nombre / Empresa</th>
                <th>Correo Electrónico</th>
                <th style="width: 85px;">Teléfono</th>
                <th>Sitio Web</th>
                <th style="width: 60px; text-align: center;">Idioma</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $index => $client)
                <!-- FILA PRINCIPAL: CLIENTE / EMPRESA -->
                <tr class="client-row">
                    <td style="text-align: center;"><strong>{{ $index + 1 }}</strong></td>
                    <td><strong>{{ $client->type === 'company' ? 'Empresa' : 'Individual' }}</strong></td>
                    <td><strong>{{ $client->company_name ?? ($client->first_name . ' ' . $client->last_name) }}</strong></td>
                    <td colspan="2" class="text-subtle" style="text-align: center;">
                        <em>(Ver contactos abajo)</em>
                    </td>
                    <td>
                        @if($client->website)
                            <a href="{{ $client->website }}" style="color: #007AFF; text-decoration: none;">
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

                <!-- CASO A: CONTACTO PRINCIPAL DIRECTO (Campos first_name/email en la tabla clients) -->
                @if($client->first_name || $client->last_name || $client->email || $client->phone)
                <tr class="contact-row">
                    <td></td>
                    <td style="text-align: right; padding-right: 8px;">
                        <span class="sub-label"><span class="arrow-icon">↳</span> Contacto Principal</span>
                    </td>
                    <td>{{ trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? '')) ?: '—' }}</td>
                    <td>{{ $client->email ?? '—' }}</td>
                    <td>{{ $client->phone ?? '—' }}</td>
                    <td class="text-subtle">—</td>
                    <td class="text-subtle" style="text-align: center;">—</td>
                </tr>
                @endif

                <!-- CASO B: CONTACTOS DE LA RELACIÓN ($client->contacts) -->
                @if($client->relationLoaded('contacts') && $client->contacts->isNotEmpty())
                    @foreach($client->contacts as $cIndex => $contact)
                        @php
                            // Determinar si este contacto en la relación es el principal
                            $isPrimary = $contact->is_primary ?? ($cIndex === 0 && !($client->first_name || $client->email));
                            
                            // Determinar la etiqueta a mostrar
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
                            <td>{{ $contact->email ?? '—' }}</td>
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
</body>
</html>