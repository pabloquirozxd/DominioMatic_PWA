<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Contact;
use App\Models\ImportLog;
use App\Services\CsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactImportController extends Controller
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
            'first_name',
            'last_name',
            'email',
            'phone',
            'position',
            'client_id',
        ];

        // Validaciones por fila
        $validator = function (array $data) {
            $errors = [];

            if (empty($data['first_name'])) {
                $errors[] = "El campo 'first_name' es obligatorio.";
            }

            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El correo '{$data['email']}' no tiene un formato válido.";
            }

            return $errors;
        };

        // Callback de persistencia
        $importer = function (array $data) use ($companyId) {
            DB::transaction(function () use ($data, $companyId) {
                $contactData = [
                    'first_name' => $data['first_name'],
                    'last_name'  => $data['last_name'] ?: null,
                    'phone'      => $data['phone'] ?: null,
                    'position'   => $data['position'] ?: null,
                ];

                if (!empty($data['email'])) {
                    $contact = Contact::updateOrCreate(
                        ['company_id' => $companyId, 'email' => $data['email']],
                        $contactData
                    );
                } else {
                    $contact = Contact::create(array_merge(['company_id' => $companyId], $contactData));
                }

                if (!empty($data['client_id'])) {
                    $client = Client::where('company_id', $companyId)->find($data['client_id']);
                    if ($client) {
                        DB::table('client_contact')->updateOrInsert(
                            [
                                'client_id'  => $client->id,
                                'contact_id' => $contact->id,
                            ],
                            [
                                'position'   => $data['position'] ?: null,
                                'is_primary' => false,
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            });
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
                'entity_type'    => 'contact',
                'imported_count' => $result['imported_count'],
                'failed_count'   => $result['failed_count'],
                'errors'         => $result['error_logs'],
            ]);
            $logId = $log->id;
        }

        $message = "Se importaron {$result['imported_count']} contactos correctamente.";
        if ($result['failed_count'] > 0) {
            $message .= " {$result['failed_count']} filas contenían errores y fueron omitidas.";
        }

        return redirect()->route('contacts.index')->with([
            'success'           => $message,
            'has_import_errors' => $result['failed_count'] > 0,
            'import_log_id'     => $logId,
        ]);
    }

    public function downloadTemplate(): StreamedResponse
    {
        $columns = ['first_name', 'last_name', 'email', 'phone', 'position', 'client_id'];

        return response()->streamDownload(function () use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns);
            fputcsv($file, ['Carlos', 'Mendoza', 'carlos@ejemplo.com', '+525512345678', 'Gerente de Ventas', '1']);
            fputcsv($file, ['Ana', 'García', 'ana@ejemplo.com', '+34612345678', 'Directora Operaciones', '']);
            fclose($file);
        }, 'plantilla_contactos.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}