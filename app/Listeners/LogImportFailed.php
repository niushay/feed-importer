<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class LogImportFailed
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
        Log::error('Import failed: ' . $event->getException()->getMessage());
    }
}
