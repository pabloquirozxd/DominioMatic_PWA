<?php

namespace App\Exports;

use App\Models\Subscription;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubscriptionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(protected Authenticatable $user)
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

        return Subscription::query()
            ->where('company_id', $companyId)
            ->with(['client', 'product'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Cliente / Empresa',
            'Producto / Servicio',
            'Precio Lista',
            'Descuento',
            'Total Neto',
            'Fecha Inicio',
            'Fecha Vencimiento',
            'Estado',
            'Fecha de Registro',
        ];
    }

    public function map($subscription): array
    {
        // Resolver nombre del cliente (Empresa o Persona)
        $clientName = $subscription->client?->company_name 
            ?? trim(($subscription->client?->first_name ?? '') . ' ' . ($subscription->client?->last_name ?? ''));

        return [
            $clientName ?: 'Sin cliente',
            optional($subscription->product)->name ?? 'Sin producto',
            (float) $subscription->price_list,
            (float) $subscription->discount,
            (float) $subscription->total_neto,
            optional($subscription->starts_at)->format('d/m/Y'),
            optional($subscription->expires_at)->format('d/m/Y'),
            ucfirst($subscription->status ?? 'N/A'),
            optional($subscription->created_at)->format('d/m/Y H:i'),
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