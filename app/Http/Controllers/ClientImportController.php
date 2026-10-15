<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ImportLog;
use App\Services\CsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientImportController extends Controller
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
        // Fallback robusto para company_id si $user->company_id es null
        $companyId = $user->company_id ?? $user->companies()->first()?->id ?? 1;

        $requiredHeaders = [
            'type',
            'company_name',
            'first_name',
            'last_name',
            'email',
            'phone',
            'website',
            'language',
            'portal_enabled',
        ];

        // Validaciones por fila
        $validator = function (array $data) {
            $errors = [];
            $type = strtolower($data['type'] ?? 'company');
            $isPerson = in_array($type, ['person', 'persona', 'individual', 'particular'], true);

            if (!$isPerson && empty($data['company_name'])) {
                $errors[] = "El campo 'company_name' es obligatorio para tipo empresa.";
            }

            if ($isPerson && empty($data['first_name'])) {
                $errors[] = "El campo 'first_name' es obligatorio para clientes individuales.";
            }

            if (empty($data['email'])) {
                $errors[] = "El campo 'email' es obligatorio.";
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El correo '{$data['email']}' no tiene un formato válido.";
            }

            return $errors;
        };

        // Callback de persistencia
        $importer = function (array $data) use ($companyId) {
            $rawType = strtolower($data['type'] ?? 'company');
            $isPerson = in_array($rawType, ['person', 'persona', 'individual', 'particular'], true);
            $type = $isPerson ? 'person' : 'company';

            $client = Client::create([
                'company_id'     => $companyId,
                'type'           => $type,
                'company_name'   => $type === 'company'
                    ? $data['company_name']
                    : trim("{$data['first_name']} {$data['last_name']}"),
                'website'        => $data['website'] ?: null,
                'language'       => $data['language'] ?: 'es',
                'portal_enabled' => filter_var($data['portal_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]);

            $client->contacts()->create([
                'company_id' => $companyId,
                'first_name' => $data['first_name'] ?: ($type === 'company' ? $data['company_name'] : 'Contacto'),
                'last_name'  => $data['last_name'] ?: '',
                'email'      => $data['email'],
                'phone'      => $data['phone'] ?: null,
                'is_primary' => true,
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
                'entity_type'    => 'client',
                'imported_count' => $result['imported_count'],
                'failed_count'   => $result['failed_count'],
                'errors'         => $result['error_logs'],
            ]);
            $logId = $log->id;
        }

        $message = "Se importaron {$result['imported_count']} clientes correctamente.";
        if ($result['failed_count'] > 0) {
            $message .= " {$result['failed_count']} filas contenían errores y fueron omitidas.";
        }

        return redirect()->route('clients.index')->with([
            'success'           => $message,
            'has_import_errors' => $result['failed_count'] > 0,
            'import_log_id'     => $logId,
        ]);
    }

    public function downloadTemplate(): StreamedResponse
    {
        $columns = ['type', 'company_name', 'first_name', 'last_name', 'email', 'phone', 'website', 'language', 'portal_enabled'];

        return response()->streamDownload(function () use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns);
            fputcsv($file, ['company', 'Acme Corporation', 'Carlos', 'Mendoza', 'carlos@acme.com', '+525512345678', 'https://acme.com', 'es', '1']);
            fputcsv($file, ['individual', '', 'Ana', 'García', 'ana.garcia@email.com', '+34612345678', '', 'es', '0']);
            fclose($file);
        }, 'plantilla_clientes.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}