<?php

namespace App\Services;

use App\Services\contracts\ImporterInterface;

class CsvImporter implements ImporterInterface
{
    public function import(string $filePath, string $modelClass): array
    {
        return [
            "success" => 1,
            "error" => 2
        ];
    }
}
