<?php

namespace App\Exports;

use App\Models\Contact;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContactsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(protected $user)
    {
    }

    /**
     * Obtener el ID de la empresa activa del usuario.
     */
    private function getCompanyId(): ?int
    {
        if (session()->has('active_company_id')) {
            return (int) session('active_company_id');
        }

        return $this->user->companies()->first()?->id;
    }

    public function collection(): Collection
    {
        $companyId = $this->getCompanyId();

        return Contact::query()
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Apellido',
            'Correo',
            'Teléfono',
            'Tipo / Rol',
            'Cargo / Posición',
            'Fecha de registro',
        ];
    }

    public function map($contact): array
    {
        return [
            $contact->first_name,
            $contact->last_name ?? '',
            $contact->email,
            $contact->phone ?? '—',
            $contact->is_primary ? 'Principal' : 'Secundario',
            $contact->position ?? '—',
            optional($contact->created_at)->format('d/m/Y H:i'),
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