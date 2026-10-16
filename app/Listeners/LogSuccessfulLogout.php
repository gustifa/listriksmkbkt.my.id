<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
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
    // public function handle(object $event): void
    // {
    //     //
    // }

    public function handle(Logout $event): void
    {
        $user = $event->user;

        if ($user && function_exists('activity')) {
            activity('Autentikasi')
                ->performedOn($user)
                ->causedBy($user)
                ->log("Pengguna {$user->name} telah keluar (logout) dari sistem");
        }
    }
}
