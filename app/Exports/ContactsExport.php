<?php

namespace App\Exports;

use App\Models\Contact;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ContactsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(protected $user)
    {
    }

    public function collection(): Collection
    {
        return Contact::query()
            ->where('company_id', $this->user->company_id)
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
            'Tipo',
            'Cargo / Posición',
            'Fecha de registro',
        ];
    }

    public function map($contact): array
    {
        return [
            $contact->first_name,
            $contact->last_name,
            $contact->email,
            $contact->phone,
            $contact->type,
            $contact->position,
            optional($contact->created_at)->format('d/m/Y H:i'),
        ];
    }
}