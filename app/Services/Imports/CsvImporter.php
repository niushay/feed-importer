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
            $importObject = new $importClass();
            Log::info("Import started for {$filePath}");
            Excel::import($importObject->withOutput($commandContext->getOutput()), $filePath); //without queue
//            Excel::queueImport(new $importObject, $filePath); //With queue
            $successCount = $importObject->getSuccessCount();
            $errorCount = $importObject->getErrorCount();
        } catch (\Exception $e) {
            Log::error("Error processing file: {$e->getMessage()}");
            $totalRows = count(file($filePath)) - 1;
            return ['success' => 0, 'error' => $totalRows];
        }

        Log::info("Import finished: {$successCount} success, {$errorCount} failed.");
        return [
            'success' => $successCount,
            'error' => $errorCount,
        ];
    }
}
