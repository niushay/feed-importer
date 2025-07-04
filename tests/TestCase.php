<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    function createCsvFile(string $fileName, string $content): void
    {
        Storage::disk('public')->put($fileName, $content);
    }

}
