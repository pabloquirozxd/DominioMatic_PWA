<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(protected Authenticatable $user)
    {
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

    public function collection(): Collection
    {
        $companyId = $this->getCompanyId();

        return Product::query()
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tipo de Ítem',
            'Nombre',
            'Descripción',
            'Precio de Lista',
            'Moneda',
            'Tipo de Stock',
            'Stock Disponible',
            'Fecha de Registro',
        ];
    }

    public function map($product): array
    {
        // Identificar el tipo de ítem
        $itemType = ($product->type === 'service' || $product->type === 'Servicio') ? 'Servicio' : 'Producto';

        // Determinar el manejo de stock
        $isInfinite = (bool) $product->is_infinite;
        $stockType = $isInfinite ? 'Ilimitado' : 'Finito';
        $stockValue = ($itemType === 'Servicio' || $isInfinite) ? 'N/A' : (int) ($product->stock ?? 0);

        return [
            $itemType,
            $product->name,
            strip_tags($product->description ?? '—'),
            number_format((float) $product->price_list, 2, '.', ''),
            $product->currency ?? 'USD',
            $stockType,
            $stockValue,
            optional($product->created_at)->format('d/m/Y H:i'),
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