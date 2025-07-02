<?php

namespace App\Services\contracts;

interface ImporterInterface
{
    public function import(string $filePath, string $modelClass): array;
}
