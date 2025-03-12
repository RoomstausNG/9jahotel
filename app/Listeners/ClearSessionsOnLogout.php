<?php

namespace App\Listeners;

use IlluminateAuthEventsLogout;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Session;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClearSessionsOnLogout
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
    public function handle(Logout $event)
    {
        Session::flush();
    }
}