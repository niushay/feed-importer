<?php

namespace App\Services\Imports;

use App\Jobs\ImportFileJob;
use App\Services\Imports\contracts\ImporterInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class CsvImporter implements ImporterInterface
{
    /**
     * Import the data from the CSV file.
     *
     * @param string $filePath
     * @param string $model
     * @param Command $commandContext
     * @return array{success: int, error: int}
     */
    public function import(string $filePath, string $model, Command $commandContext): array
    {
        try {
            $importClass = $this->getImportClass($model);
            $hasHeader = $this->getHeaderFlag($commandContext);
            $shouldQueue = $this->getQueueFlag($commandContext);

            Log::info("Import started for {$filePath}");

            if ($shouldQueue) {
                return $this->handleQueueImport($filePath, $importClass, $hasHeader);
            }

            return $this->handleDirectImport($filePath, $importClass, $hasHeader, $commandContext);
        } catch (Exception $e) {
            Log::error("Error processing file: {$e->getMessage()}");
            return $this->handleImportError($filePath);
        }
    }

    private function getImportClass(string $model): string
    {
        return 'App\\Imports\\' . ucfirst($model) . 'Import';
    }

    private function getHeaderFlag(Command $commandContext): bool
    {
        return filter_var($commandContext->option('with-header'), FILTER_VALIDATE_BOOLEAN);
    }

    private function getQueueFlag(Command $commandContext): bool
    {
        return filter_var($commandContext->option('with-queue'), FILTER_VALIDATE_BOOLEAN);
    }

    private function handleQueueImport(string $filePath, string $importClass, bool $hasHeader): array
    {
        ImportFileJob::dispatch($filePath, $importClass, $hasHeader);
        Log::info("Queued import dispatched for {$filePath}");

        return ['success' => 0, 'error' => 0];
    }

    private function handleDirectImport(string $filePath, string $importClass, bool $hasHeader, Command $commandContext): array
    {
        $importObject = new $importClass($hasHeader);
        Excel::import($importObject->withOutput($commandContext->getOutput()), $filePath);

        return [
            'success' => $importObject->getSuccessCount(),
            'error' => $importObject->getErrorCount(),
        ];
    }

    private function handleImportError(string $filePath): array
    {
        $totalRows = ($lines = @file($filePath)) ? count($lines) - 1 : 0;
        return ['success' => 0, 'error' => $totalRows];
    }
}
