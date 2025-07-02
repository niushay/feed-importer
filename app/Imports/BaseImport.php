<?php

namespace App\Imports;

abstract class BaseImport
{
    protected int $successCount = 0;
    protected int $errorCount = 0;

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

    abstract public function model(array $row);
}
