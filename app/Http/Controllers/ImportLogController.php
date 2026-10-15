<?php

namespace App\Http\Controllers;

use App\Models\ImportLog;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportLogController extends Controller
{
    public function downloadErrorLog(string $uuid): StreamedResponse
    {
        $user = Auth::user();

        // Validar que el log exista y pertenezca al usuario autenticado
        $log = ImportLog::where('id', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->streamDownload(function () use ($log) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 para Excel

            fputcsv($file, ['Linea_CSV', 'Datos_Originales', 'Motivo_Excepcion']);

            foreach ($log->errors as $error) {
                fputcsv($file, [
                    $error['line'] ?? '',
                    is_array($error['data'] ?? null) ? implode(' | ', $error['data']) : ($error['data'] ?? ''),
                    $error['reason'] ?? '',
                ]);
            }

            fclose($file);
        }, "informe_errores_{$log->entity_type}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}