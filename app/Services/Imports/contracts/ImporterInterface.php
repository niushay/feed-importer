<?php

namespace App\Services\Imports\contracts;

use Illuminate\Console\Command;

/**
 * Import data from a file and return success/error counts.
 *
 * @param  string  $filePath
 * @param  string  $model
 * @param  Command  $commandContext
 * @return array{success: int, error: int}
 */
interface ImporterInterface
{
    public function import(string $filePath, string $model, Command $commandContext): array;
}
