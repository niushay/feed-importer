<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Events\ImportFailed;

class LogImportFailed
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function __invoke(ImportFailed $event): void
    {
        Log::error('Import failed: ' . $event->getException()->getMessage());
    }
}
