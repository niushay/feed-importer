<?php

namespace App\Console\Commands;

use App\Services\Imports\FileImporterService;
use Illuminate\Console\Command;
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

    protected FileImporterService $fileImporterService;

    public function __construct(FileImporterService $fileImporterService)
    {
        parent::__construct();
        $this->fileImporterService = $fileImporterService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');
        $resolvedFilePath = $this->fileImporterService->resolveFilePath($filePath);

        if (! $resolvedFilePath) {
            return CommandAlias::FAILURE;
        }

        $model = $this->option('model');
        $extension = pathinfo($resolvedFilePath, PATHINFO_EXTENSION);

        if (! $this->fileImporterService->validateFile($resolvedFilePath, $extension, $model)) {
            return CommandAlias::FAILURE;
        }

        $importerClass = $this->fileImporterService->getImporterClass($extension);
        $importer = app($importerClass);

        $this->output->title('Starting import...');
        $result = $importer->import($resolvedFilePath, $model, $this);

        if ($result['success'] === 0 && $result['error'] > 0) {
            $this->error('The file is not imported successfully');

            return CommandAlias::FAILURE;
        }

        $this->output->success('Import completed!');
        $this->outputImportStats($result);

        return CommandAlias::SUCCESS;
    }

    private function outputImportStats(array $result): void
    {
        if ($result['success'] === 0 && $result['error'] === 0 && $this->option('with-queue')) {
            $this->info('ℹ️  Import has been queued. Results will appear once processing completes.');
        } else {
            $this->info("✅  {$result['success']} row(s) imported successfully. ❌  {$result['error']} row(s) failed.");
        }
    }
}
