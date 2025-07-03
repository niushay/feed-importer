<?php

namespace App\Imports;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;

abstract class BaseImport implements
    ToModel,
    WithHeadingRow,
    WithProgressBar,
    WithChunkReading,
    WithBatchInserts
{
    use Importable, SkipsErrors;
    protected int $successCount = 0;
    protected int $errorCount = 0;

    /**
     * @param array<string, mixed> $row
     * @return Model|null
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
}
