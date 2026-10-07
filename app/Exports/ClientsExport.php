<?php

namespace App\Exports;

use App\Models\Client;
use Illuminate\Contracts\Auth\Authenticatable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected Authenticatable $user;

    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }

    /**
     * Obtener el ID de la empresa activa.
     */
    private function getCompanyId(): ?int
    {
        if (session()->has('active_company_id')) {
            return (int) session('active_company_id');
        }

        return $this->user->companies()->first()?->id;
    }

    public function collection()
    {
        $companyId = $this->getCompanyId();

        return Client::query()
            ->where('company_id', $companyId)
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
            $client->created_at ? $client->created_at->format('d/m/Y H:i') : '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => '0072A8'],
                ],
            ],
        ];
    }
}