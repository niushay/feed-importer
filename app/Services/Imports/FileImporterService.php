<?php

namespace App\Services\Imports;

use App\Services\Imports\contracts\ImporterInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileImporterService
{
    /**
     * List of supported file formats and their corresponding importer classes.
     *
     * @var array<string, class-string<ImporterInterface>>
     */
    protected array $importerServices = [
        'csv' => CsvImporter::class,
//        'json' => JsonImporter::class,
//        'xml' => XmlImporter::class,
    ];

    public function resolveFilePath(string $filePath): ?string
    {
        if (! file_exists($filePath)) {
            $resolved = Storage::disk('public')->path($filePath);
            if (file_exists($resolved)) {
                return $resolved;
            }
            Log::error("The file at path '{$filePath}' does not exist.");
            return null;
        }

        return $filePath;
    }

    public function validateFile(string $filePath, string $extension, string $model): bool
    {
        if (! file_exists($filePath) || filesize($filePath) === 0) {
            Log::error("The file at path '{$filePath}' is empty or does not exist.");
            return false;
        }

        if (! $this->isSupportedFormat($extension)) {
            $supportedFormats = implode(', ', array_keys($this->importerServices));
            Log::error("Unsupported file format: '{$extension}'. Supported formats are: {$supportedFormats}.");
            return false;
        }

        if (! $this->isValidModel($model)) {
            Log::error("Invalid or missing model class: '{$model}'.");
            return false;
        }

        return true;
    }

    public function isSupportedFormat(string $extension): bool
    {
        return array_key_exists($extension, $this->importerServices);
    }

    public function isValidModel(string $model): bool
    {
        $modelClass = 'App\\Models\\' . ucfirst($model);
        return class_exists($modelClass);
    }

    public function getImporterClass(string $extension): string
    {
        if ($this->isSupportedFormat($extension)) {
            return $this->importerServices[$extension];
        }
        return '';
    }
}
