<?php

namespace App\Exports;

use App\Models\Client;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientsExport implements FromCollection, WithHeadings, WithMapping
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return Client::query()
            ->where('company_id', $this->user->company_id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tipo',
            'Nombre / Empresa',
            'Nombre de Pila',
            'Apellido',
            'Correo Electrónico',
            'Teléfono',
            'Sitio Web',
            'Idioma',
            'Portal Activo',
            'Fecha de Registro',
        ];
    }

    /**
     * @param Client $client
     */
    public function map($client): array
    {
        return [
            $client->id,
            $client->type === 'company' ? 'Empresa' : 'Persona',
            $client->company_name ?? '—',
            $client->first_name ?? '—',
            $client->last_name ?? '—',
            $client->email ?? '—',
            $client->phone ?? '—',
            $client->website ?? '—',
            strtoupper($client->language ?? 'es'),
            $client->portal_enabled ? 'Sí' : 'No',
            $client->created_at ? $client->created_at->format('Y-m-d H:i') : '—',
        ];
    }
}