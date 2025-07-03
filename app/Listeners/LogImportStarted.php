<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class LogImportStarted
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
        Log::info('Import started using reader: ' . get_class($event->getReader()));
    }
}
