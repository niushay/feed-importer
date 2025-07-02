<?php

namespace App\Console\Commands;

use App\Services\CsvImporter;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class FeedImporter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:feed {file=storage/app/public/feed.csv : Path to the feed file} {--model=Product : Type of the data (e.g., product, user)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from a file (e.g., CSV) into the database';
    /**
     * Supported file types and their corresponding importer services.
     */
    protected array $importerServices = [
        'csv' => CsvImporter::class,
        // 'json' => JsonImporter::class,
        // 'xml' => XmlImporter::class,
    ];
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $model = $this->option('model');
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        if (!$this->validate($filePath, $extension, $model)) {
            return CommandAlias::FAILURE;
        }

        $importerClass = $this->importerServices[$extension];
        $importer = app($importerClass);
        $modelClass = 'App\\Models\\' . ucfirst($model);
        $result = $importer->import($filePath, $modelClass);

        $this->info("✅  {$result['success']} row(s) imported successfully. ❌  {$result['error']} row(s) failed.");
        return CommandAlias::SUCCESS;
    }

    private function validate(string $filePath, string $extension, string $model): bool
    {
        if (!file_exists($filePath)) {
            $this->error("The file at path '{$filePath}' does not exist.");
            return false;
        }

        if (!$this->isSupportedFormat($extension)) {
            $this->error("Unsupported file format: '{$extension}'.");
            return false;
        }

        if (!$this->isValidModel($model)) {
            $this->error("Invalid or missing model class: '{$model}'.");
            return false;
        }

        return true;
    }

    private function isSupportedFormat(string $extension): bool
    {
        return array_key_exists($extension, $this->importerServices);
    }

    private function isValidModel(?string $model): bool
    {
        if (!$model) {
            return false;
        }

        $modelClass = "App\\Models\\" . ucfirst($model);

        return class_exists($modelClass);
    }

}
