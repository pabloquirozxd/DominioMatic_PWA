<?php

namespace App\Http\Controllers;

use App\Models\ImportLog;
use App\Models\Product;
use App\Services\CsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductImportController extends Controller
{
    public function __construct(protected CsvImportService $importService)
    {
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $user = Auth::user();
        $companyId = session('active_company_id') ?? $user->company_id ?? $user->companies()->first()?->id ?? 1;

        $requiredHeaders = [
            'type',
            'name',
            'description',
            'price_list',
            'currency',
            'is_infinite',
            'stock',
        ];

        // Validaciones por fila
        $validator = function (array $data) {
            $errors = [];

            if (empty($data['name'])) {
                $errors[] = "El campo 'name' es obligatorio.";
            }

            $type = strtolower($data['type'] ?? 'product');
            if (!in_array($type, ['product', 'service', 'producto', 'servicio'], true)) {
                $errors[] = "El tipo '{$data['type']}' no es válido (usar 'product' o 'service').";
            }

            if (!isset($data['price_list']) || !is_numeric($data['price_list']) || floatval($data['price_list']) < 0) {
                $errors[] = "El campo 'price_list' debe ser un número mayor o igual a 0.";
            }

            $currency = strtoupper($data['currency'] ?? 'BOB');
            if (!in_array($currency, ['USD', 'BOB'], true)) {
                $errors[] = "La moneda '{$data['currency']}' debe ser USD o BOB.";
            }

            return $errors;
        };

        // Callback de persistencia
        $importer = function (array $data) use ($companyId) {
            $rawType = strtolower($data['type'] ?? 'product');
            $type = in_array($rawType, ['service', 'servicio'], true) ? 'service' : 'product';

            $isService = $type === 'service';
            $isInfinite = $isService ? true : filter_var($data['is_infinite'] ?? false, FILTER_VALIDATE_BOOLEAN);

            Product::create([
                'company_id'  => $companyId,
                'type'        => $type,
                'name'        => $data['name'],
                'description' => $data['description'] ?: null,
                'price_list'  => (float) $data['price_list'],
                'currency'    => strtoupper($data['currency'] ?: 'BOB'),
                'is_infinite' => $isInfinite,
                'stock'       => $isInfinite ? 0 : (int) ($data['stock'] ?? 0),
            ]);
        };

        $result = $this->importService->process($request->file('file'), $requiredHeaders, $validator, $importer);

        if ($result['status'] === 'header_error') {
            return back()->withErrors(['file' => $result['message']]);
        }

        $logId = null;
        if ($result['failed_count'] > 0) {
            $log = ImportLog::create([
                'user_id'        => $user->id,
                'company_id'     => $companyId,
                'entity_type'    => 'product',
                'imported_count' => $result['imported_count'],
                'failed_count'   => $result['failed_count'],
                'errors'         => $result['error_logs'],
            ]);
            $logId = $log->id;
        }

        $message = "Se importaron {$result['imported_count']} productos/servicios correctamente.";
        if ($result['failed_count'] > 0) {
            $message .= " {$result['failed_count']} filas contenían errores y fueron omitidas.";
        }

        return redirect()->route('products.index')->with([
            'success'           => $message,
            'has_import_errors' => $result['failed_count'] > 0,
            'import_log_id'     => $logId,
        ]);
    }

    public function downloadTemplate(): StreamedResponse
    {
        $columns = ['type', 'name', 'description', 'price_list', 'currency', 'is_infinite', 'stock'];

        return response()->streamDownload(function () use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns);
            fputcsv($file, ['product', 'Hosting Anual SSD', 'Plan de hosting rápido con cPanel', '120.00', 'USD', '1', '0']);
            fputcsv($file, ['service', 'Consultoría Técnica IT', 'Servicio de soporte especializado por hora', '250.00', 'BOB', '1', '0']);
            fputcsv($file, ['product', 'Servidor Cloud VPS', 'VPS de 4 vCPU y 8GB RAM', '80.00', 'USD', '0', '15']);
            fclose($file);
        }, 'plantilla_productos.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}