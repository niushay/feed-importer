<?php

namespace App\Imports;

use App\Listeners\LogImportCompleted;
use App\Listeners\LogImportFailed;
use App\Listeners\LogImportStarted;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;

abstract class BaseImport implements ToModel, WithBatchInserts, WithChunkReading, WithEvents, WithHeadingRow, WithProgressBar
{
    use Importable, SkipsErrors;

    protected int $successCount = 0;
    protected int $errorCount = 0;

    protected bool $hasHeader;

    public function __construct(bool $hasHeader)
    {
        $this->hasHeader = $hasHeader;
    }

    /**
     * Specify the row number for the heading row.
     */
    public function headingRow(): int
    {
        return $this->hasHeader ? 1 : 0;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    abstract public function model(array $row): ?Model;

    protected function incrementSuccessCount(): void
    {
        $this->successCount++;
    }

    protected function incrementErrorCount(): void
    {
        $this->errorCount++;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Register the events for the import.
     *
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            BeforeImport::class => new LogImportStarted,
            AfterImport::class => new LogImportCompleted,
            ImportFailed::class => new LogImportFailed,
        ];
    }

    protected function getField(array $row, string $key, int $index)
    {
        return $row[$key] ?? $row[$index] ?? null;
    }
}
