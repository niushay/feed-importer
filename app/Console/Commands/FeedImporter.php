<?php

namespace App\Console\Commands;

use App\Services\Imports\contracts\ImporterInterface;
use App\Services\Imports\CsvImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;

class FeedImporter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:feed
    {file=storage/app/public/feed.csv : Path to the feed file}
    {--model=Product : Type of the data (e.g., product, user)}
    {--with-header=true : Specify if the file has a header row}
    {--with-queue=false : Whether to run the import in the queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from a file (e.g., CSV) into the database';

    /**
     * Supported file types and their corresponding importer services.
     *
     * @var array<string, class-string<ImporterInterface>>
     */
    protected array $importerServices = [
        'csv' => CsvImporter::class,
        // 'json' => JsonImporter::class,
        // 'xml' => XmlImporter::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (! Str::startsWith($filePath, '/') && ! file_exists($filePath)) {
            $resolved = Storage::disk('public')->path($filePath);
            if (file_exists($resolved)) {
                $filePath = $resolved;
            }
        }

        $model = $this->option('model');
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        if (! $this->validate($filePath, $extension, $model)) {
            return CommandAlias::FAILURE;
        }

        $importerClass = $this->importerServices[$extension];
        $importer = app($importerClass);

        $this->output->title('Starting import...');
        $result = $importer->import($filePath, $model, $this);
        if ($result['success'] === 0 && $result['success'] < $result['error']) {
            $this->error('The file is not imported successfully');
            Log::error('Import failed: the file could not be imported successfully');

            return CommandAlias::FAILURE;
        }
        $this->output->success('Import completed!');
        if ($result['success'] === 0 && $result['error'] === 0 && $this->option('with-queue')) {
            $this->info('ℹ️  Import has been queued. Results will appear once processing completes.');
        } else {
            $this->info("✅  {$result['success']} row(s) imported successfully. ❌  {$result['error']} row(s) failed.");
        }

        return CommandAlias::SUCCESS;
    }

    private function validate(string $filePath, string $extension, string $model): bool
    {
        if (! file_exists($filePath)) {
            $this->error("The file at path '{$filePath}' does not exist.");
            Log::error("The file at path '{$filePath}' does not exist.");

            //            dd("The file at path '{$filePath}' does not exist.");
            return false;
        }

        if (filesize($filePath) === 0) {
            $this->error("The file at path '{$filePath}' is empty.");
            Log::error("The file at path '{$filePath}' is empty.");

            return false;
        }

        if (! $this->isSupportedFormat($extension)) {
            $supportedFormats = implode(', ', array_keys($this->importerServices));
            $this->error("Unsupported file format: '{$extension}'. Supported formats are: {$supportedFormats}.");
            Log::error("Unsupported file format: '{$extension}'. Supported formats are: {$supportedFormats}.");
            //            dd('b');

            return false;
        }

        if (! $this->isValidModel($model)) {
            $this->error("Invalid or missing model class: '{$model}'.");
            Log::error("Invalid or missing model class: '{$model}'.");
            //            dd('c');

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
        if (! $model) {
            return false;
        }

        $modelClass = 'App\\Models\\'.ucfirst($model);

        return class_exists($modelClass);
    }
}
