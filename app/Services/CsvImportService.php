<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Closure;

class CsvImportService
{
    public function process(
        UploadedFile $file,
        array $requiredHeaders,
        Closure $rowValidatorCallback,
        Closure $rowImporterCallback
    ): array {
        $filePath = $file->getRealPath();

        // 1. Auto-detección de delimitador (coma o punto y coma)
        $sample = file_get_contents($filePath, false, null, 0, 8192) ?: '';
        $delimiter = substr_count($sample, ';') > substr_count($sample, ',') ? ';' : ',';

        $handle = @fopen($filePath, 'r');
        if (!$handle) {
            return $this->headerError('No se pudo leer el archivo subido. Verifica que sea un formato CSV válido.');
        }

        // 2. Leer cabecera
        $rawHeader = fgetcsv($handle, 0, $delimiter);
        if (!$rawHeader || !array_filter($rawHeader)) {
            fclose($handle);
            return $this->headerError('El archivo CSV está vacío o no contiene una fila de encabezados válida.');
        }

        // 3. Limpieza de BOM UTF-8
        if (isset($rawHeader[0])) {
            $rawHeader[0] = preg_replace('/^\xEF\xBB\xBF/', '', $rawHeader[0]);
            $rawHeader[0] = preg_replace('/\x{FEFF}/u', '', $rawHeader[0]);
        }

        $headers = array_map(fn ($col) => strtolower(trim((string) $col)), $rawHeader);

        // 4. Validación de la Estructura de Encabezados (Regla Bloqueante)
        $missingHeaders = array_values(array_diff($requiredHeaders, $headers));
        if (!empty($missingHeaders)) {
            fclose($handle);
            return $this->headerError(
                'El formato de las columnas no coincide con la plantilla oficial. Columnas faltantes o incorrectas: ' . implode(', ', $missingHeaders) . '.'
            );
        }

        $imported = 0;
        $failed = 0;
        $errorLogs = [];
        $line = 1; // Línea 1 = encabezados

        // 5. Procesamiento Fila por Fila (Best Effort / Importación Parcial)
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;

            // Omitir líneas completamente vacías
            if (!is_array($row) || (count($row) === 1 && trim((string) $row[0]) === '')) {
                continue;
            }

            // Nivelación de longitud de columnas
            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            } elseif (count($row) > count($headers)) {
                $row = array_slice($row, 0, count($headers));
            }

            $data = array_combine(
                $headers,
                array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row)
            );

            // Validación de contenido según reglas de la entidad
            $validationErrors = $rowValidatorCallback($data, $line);
            if (!empty($validationErrors)) {
                $failed++;
                $errorLogs[] = [
                    'line'   => $line,
                    'data'   => implode(' | ', array_filter($row, fn ($v) => $v !== '')),
                    'reason' => implode(' ', $validationErrors),
                ];
                continue;
            }

            // Intento de importación independiente
            try {
                $rowImporterCallback($data, $line);
                $imported++;
            } catch (\Throwable $e) {
                $failed++;
                $errorLogs[] = [
                    'line'   => $line,
                    'data'   => implode(' | ', array_filter($row, fn ($v) => $v !== '')),
                    'reason' => 'Excepción de base de datos: ' . $e->getMessage(),
                ];
            }
        }

        fclose($handle);

        return [
            'status'         => 'completed',
            'imported_count' => $imported,
            'failed_count'   => $failed,
            'total_rows'     => $imported + $failed,
            'error_logs'     => $errorLogs,
        ];
    }

    protected function headerError(string $message): array
    {
        return [
            'status'         => 'header_error',
            'message'        => $message,
            'imported_count' => 0,
            'failed_count'   => 0,
            'total_rows'     => 0,
            'error_logs'     => [],
        ];
    }
}