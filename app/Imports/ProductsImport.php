<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $companyId;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    public function model(array $row)
    {
        $type = strtolower(trim($row['tipo'] ?? $row['type'] ?? 'product'));
        $isService = in_array($type, ['servicio', 'service']);
        $isInfinite = $isService || filter_var($row['ilimitado'] ?? $row['is_infinite'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return new Product([
            'company_id'  => $this->companyId,
            'name'        => $row['nombre'] ?? $row['name'],
            'description' => $row['descripcion'] ?? $row['description'] ?? null,
            'price_list'  => (float) ($row['precio'] ?? $row['precio_lista'] ?? $row['price_list'] ?? 0),
            'currency'    => strtoupper($row['moneda'] ?? $row['currency'] ?? 'USD'),
            'type'        => $isService ? 'service' : 'product',
            'is_infinite' => $isInfinite,
            'stock'       => $isInfinite ? 0 : (int) ($row['stock'] ?? 0),
        ]);
    }

    public function rules(): array
    {
        return [
            '*.nombre' => ['nullable', 'string'],
            '*.name'   => ['nullable', 'string'],
        ];
    }
}