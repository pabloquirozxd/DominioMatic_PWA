<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(protected $user)
    {
    }

    public function collection(): Collection
    {
        return Product::query()
            ->where('company_id', $this->user->company_id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Descripción',
            'Precio de lista',
            'Tipo de recurso',
            'Stock',
            'Fecha de registro',
        ];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->description,
            $product->price_list,
            $product->is_infinite ? 'Ilimitado' : 'Finito',
            $product->is_infinite ? 'No aplica' : $product->stock,
            optional($product->created_at)->format('d/m/Y H:i'),
        ];
    }
}