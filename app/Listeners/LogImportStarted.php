<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Events\BeforeImport;

class LogImportStarted
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function __invoke(BeforeImport $event): void
    {
        Log::info('Import started using reader: '.get_class($event->getReader()));
    }
}
