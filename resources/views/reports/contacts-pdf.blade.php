<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Contactos</title>
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
    <h1>Reporte de Contactos</h1>

    <div class="meta">
        <strong>Empresa:</strong> {{ $company->name ?? 'Sin empresa' }}<br>
        <strong>Generado por:</strong> {{ $user->name }}<br>
        <strong>Fecha:</strong> {{ $generatedAt->format('d/m/Y H:i') }}<br>
        <strong>Total de contactos:</strong> {{ $contacts->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Nombre completo</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Tipo</th>
                <th>Cargo</th>
                <th>Registro</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($contacts as $contact)
                <tr>
                    <td>{{ trim($contact->first_name . ' ' . $contact->last_name) }}</td>
                    <td>{{ $contact->email }}</td>
                    <td>{{ $contact->phone }}</td>
                    <td>{{ $contact->type }}</td>
                    <td>{{ $contact->position }}</td>
                    <td>{{ optional($contact->created_at)->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No existen contactos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        DominioMatic PWA - Reporte generado automáticamente.
    </div>
</body>
</html>