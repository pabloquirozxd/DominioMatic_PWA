<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientImportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'], // Máx 5MB
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Leer la cabecera
        $header = fgetcsv($handle, 1000, ',');
        
        if (!$header) {
            return back()->withErrors(['file' => 'El archivo CSV está vacío o es inválido.']);
        }

        // Normalizar nombres de columnas (minúsculas y sin espacios)
        $header = array_map(fn($col) => strtolower(trim($col)), $header);

        $userCompanyId = Auth::user()->company_id;
        $importedCount = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($row) < count($header)) continue;

                $data = array_combine($header, $row);

                $type = strtolower(trim($data['type'] ?? 'company'));
                $type = in_array($type, ['company', 'person']) ? $type : 'company';

                $firstName = trim($data['first_name'] ?? '');
                $lastName = trim($data['last_name'] ?? '');
                $companyName = trim($data['company_name'] ?? '');

                // Validación de datos mínimos requeridos por fila
                if ($type === 'company' && empty($companyName)) continue;
                if ($type === 'person' && empty($firstName)) continue;

                // 1. Crear el Cliente (Entidad de facturación)
                $client = Client::create([
                    'company_id' => $userCompanyId,
                    'type' => $type,
                    'company_name' => $type === 'company' 
                        ? $companyName 
                        : trim("{$firstName} {$lastName}"),
                    'website' => !empty($data['website']) ? trim($data['website']) : null,
                    'language' => !empty($data['language']) ? trim($data['language']) : 'Español',
                    'portal_enabled' => filter_var($data['portal_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ]);

                // 2. Crear el Contacto Principal asociado
                $client->contacts()->create([
                    'company_id' => $userCompanyId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => !empty($data['email']) ? trim($data['email']) : null,
                    'phone' => !empty($data['phone']) ? trim($data['phone']) : null,
                    'is_primary' => true,
                ]);

                $importedCount++;
            }

            DB::commit();
            fclose($handle);

            return redirect()
                ->route('clients.index')
                ->with('success', "Se importaron exitosamente {$importedCount} clientes.");
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);

            return back()->withErrors(['file' => 'Error al procesar el archivo CSV: ' . $e->getMessage()]);
        }
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_clientes.csv"',
        ];

        $columns = [
            'type',
            'company_name',
            'first_name',
            'last_name',
            'email',
            'phone',
            'website',
            'language',
            'portal_enabled'
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            
            // BOM UTF-8 para que Excel abra el CSV correctamente con tildes y caracteres especiales
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Cabeceras
            fputcsv($file, $columns);

            // Filas de ejemplo
            fputcsv($file, [
                'company', 
                'Acme Corporation', 
                'Carlos', 
                'Mendoza', 
                'carlos@acme.com', 
                '+525512345678', 
                'https://acme.com', 
                'es', 
                '1'
            ]);

            fputcsv($file, [
                'individual', 
                '', 
                'Ana', 
                'García', 
                'ana.garcia@email.com', 
                '+34612345678', 
                '', 
                'en', 
                '0'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}