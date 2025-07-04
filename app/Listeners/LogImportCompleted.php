<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Events\AfterImport;

class LogImportCompleted
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function __invoke(AfterImport $event): void
    {
        Log::info('Import completed for: '.get_class($event->getConcernable()));
    }
}
