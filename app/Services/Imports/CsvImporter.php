<?php

namespace App\Services\Imports;

use App\Services\Imports\contracts\ImporterInterface;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CsvImporter implements ImporterInterface
{
    public function import(string $filePath, string $model, $commandContext): array
    {
        $importClass = 'App\\Imports\\' . ucfirst($model) . 'Import';
        try {
            $importObject = new $importClass('App\\Models\\' . ucfirst($model));
            Log::info("Import started for {$filePath}");
            Excel::import($importObject->withOutput($commandContext), $filePath);
            $successCount = $importObject->getSuccessCount();
            $errorCount = $importObject->getErrorCount();
        } catch (\Exception $e) {
            Log::error("Error processing file: {$e->getMessage()}");
            return ['success' => 0, 'error' => 1];
        }

        Log::info("Import finished: {$successCount} success, {$errorCount} failed.");
        return [
            'success' => $successCount,
            'error' => $errorCount,
        ];
    }
}
