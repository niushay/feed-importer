<?php

namespace App\Services\Imports\contracts;

interface ImporterInterface
{
    public function import(string $filePath, string $model, $commandContext): array;
}
