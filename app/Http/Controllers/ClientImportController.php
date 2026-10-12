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
        $filePath = $file->getRealPath();

        // 1. Detección automática de delimitador (coma ',' o punto y coma ';')
        $firstLine = file_get_contents($filePath, false, null, 0, 2048) ?: '';
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return back()->withErrors(['file' => 'No se pudo abrir el archivo subido.']);
        }

        // 2. Leer la cabecera
        $header = fgetcsv($handle, 0, $delimiter);

        if (!$header || empty(array_filter($header))) {
            fclose($handle);
            return back()->withErrors(['file' => 'El archivo CSV está vacío o es inválido.']);
        }

        // 3. Limpiar marcas de agua BOM UTF-8 y normalizar cabeceras
        if (isset($header[0])) {
            $header[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/u', '', $header[0]);
            $header[0] = str_replace("\xEF\xBB\xBF", '', $header[0]);
        }
        $header = array_map(fn($col) => strtolower(trim($col)), $header);

        $userCompanyId = Auth::user()->company_id;
        $importedCount = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                // Omitir filas completamente vacías
                if (empty($row) || (count($row) === 1 && trim($row[0]) === '')) {
                    continue;
                }

                // Auto-nivelar columnas si la fila tiene menos o más elementos que la cabecera
                $headerCount = count($header);
                $rowCount = count($row);

                if ($rowCount < $headerCount) {
                    $row = array_pad($row, $headerCount, '');
                } elseif ($rowCount > $headerCount) {
                    $row = array_slice($row, 0, $headerCount);
                }

                $data = array_combine($header, $row);

                // Normalización de tipos (soporta company, empresa, person, persona, individual, particular)
                $rawType = strtolower(trim($data['type'] ?? 'company'));
                if (in_array($rawType, ['person', 'persona', 'individual', 'particular'])) {
                    $type = 'person';
                } else {
                    $type = 'company';
                }

                $firstName   = trim($data['first_name'] ?? '');
                $lastName    = trim($data['last_name'] ?? '');
                $companyName = trim($data['company_name'] ?? '');

                // Fallback inteligente para nombres en campos cruzados
                if ($type === 'company' && empty($companyName)) {
                    $companyName = trim("{$firstName} {$lastName}");
                }

                if ($type === 'person' && empty($firstName)) {
                    $firstName = $companyName ?: $lastName;
                }

                // Omitir solo si no existe absolutamente ningún nombre identificable
                if (empty($companyName) && empty($firstName) && empty($lastName)) {
                    continue;
                }

                // Validar formato de email
                $rawEmail = trim($data['email'] ?? '');
                $email = filter_var($rawEmail, FILTER_VALIDATE_EMAIL) ? $rawEmail : null;

                // 1. Crear el Cliente (Entidad de facturación)
                $client = Client::create([
                    'company_id'     => $userCompanyId,
                    'type'           => $type,
                    'company_name'   => $type === 'company' ? $companyName : trim("{$firstName} {$lastName}"),
                    'website'        => !empty($data['website']) ? trim($data['website']) : null,
                    'language'       => !empty($data['language']) ? trim($data['language']) : 'es',
                    'portal_enabled' => filter_var($data['portal_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ]);

                // 2. Crear el Contacto Principal asociado
                $client->contacts()->create([
                    'company_id' => $userCompanyId,
                    'first_name' => $firstName ?: ($type === 'company' ? $companyName : 'Contacto'),
                    'last_name'  => $lastName ?: '',
                    'email'      => $email,
                    'phone'      => !empty($data['phone']) ? trim($data['phone']) : null,
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
            'Content-Type'        => 'text/csv; charset=UTF-8',
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
            
            // BOM UTF-8 para apertura correcta en Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, $columns);

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
                'person', 
                '', 
                'Ana', 
                'García', 
                'ana.garcia@email.com', 
                '+34612345678', 
                '', 
                'es', 
                '0'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}