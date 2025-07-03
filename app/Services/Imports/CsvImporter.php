<?php

namespace App\Services\Imports;

use App\Jobs\ImportFileJob;
use App\Services\Imports\contracts\ImporterInterface;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Console\Command;

class CsvImporter implements ImporterInterface
{
    /**
     * @param string $filePath
     * @param string $model
     * @param Command $commandContext
     * @return array{success: int, error: int}
     */
    public function import(string $filePath, string $model, Command $commandContext): array
    {
        $importClass = 'App\\Imports\\' . ucfirst($model) . 'Import';
        try {
            $hasHeader = filter_var($commandContext->option('with-header'), FILTER_VALIDATE_BOOLEAN);
            $shouldQueue = filter_var($commandContext->option('with-queue'), FILTER_VALIDATE_BOOLEAN);

            $importObject = new $importClass($hasHeader);
            Log::info("Import started for {$filePath}");

            if ($shouldQueue) {
                ImportFileJob::dispatch($filePath, $importClass, $hasHeader);
                Log::info("Queued import dispatched for {$filePath}");
                return ['success' => 0, 'error' => 0];
            } else {
                Excel::import($importObject->withOutput($commandContext->getOutput()), $filePath);
                $successCount = $importObject->getSuccessCount();
                $errorCount = $importObject->getErrorCount();
                return [
                    'success' => $successCount,
                    'error' => $errorCount,
                ];
            }
        } catch (\Exception $e) {
            Log::error("Error processing file: {$e->getMessage()}");
            $totalRows = ($lines = @file($filePath)) ? count($lines) - 1 : 0;
            return ['success' => 0, 'error' => $totalRows];
        }
    }
}
