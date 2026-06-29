<?php

namespace App\Exports;

use App\Models\Subscription;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubscriptionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(protected $user)
    {
    }

    public function collection(): Collection
    {
        return Subscription::query()
            ->where('company_id', $this->user->company_id)
            ->with(['contact', 'product'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Contacto',
            'Producto / Servicio',
            'Precio lista',
            'Descuento',
            'Total neto',
            'Inicio',
            'Vencimiento',
            'Estado',
            'Fecha de registro',
        ];
    }

    public function map($subscription): array
    {
        $contactName = trim(
            optional($subscription->contact)->first_name . ' ' .
            optional($subscription->contact)->last_name
        );

        return [
            $contactName ?: 'Sin contacto',
            optional($subscription->product)->name ?? 'Sin producto',
            $subscription->price_list,
            $subscription->discount,
            $subscription->total_neto,
            optional($subscription->starts_at)->format('d/m/Y'),
            optional($subscription->expires_at)->format('d/m/Y'),
            $subscription->status,
            optional($subscription->created_at)->format('d/m/Y H:i'),
        ];
    }
}