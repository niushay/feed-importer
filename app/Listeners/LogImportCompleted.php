<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class LogImportCompleted
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        Log::info('Import completed for: ' . get_class($event->getConcernable()));
    }
}
