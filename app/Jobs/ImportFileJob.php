<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImportFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    protected $importClass;

    protected $hasHeader;

    public function __construct($filePath, $importClass, $hasHeader)
    {
        $this->filePath = $filePath;
        $this->importClass = $importClass;
        $this->hasHeader = $hasHeader;
    }

    public function handle()
    {
        $importObject = new $this->importClass($this->hasHeader);
        Excel::import($importObject, $this->filePath);
        Log::info("Import completed for {$this->filePath}: {$importObject->getSuccessCount()} successes, {$importObject->getErrorCount()} errors");
    }
}
