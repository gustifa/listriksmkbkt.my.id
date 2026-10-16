<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
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

    // public function handle(Login $event): void
    // {
    //     $user = $event->user;

    //     if (function_exists('activity')) {
    //         activity('Autentikasi')
    //             ->performedOn($user)
    //             ->causedBy($user)
    //             ->withProperties([
    //                 'ip_address' => $this->request->ip(),
    //                 'user_agent' => $this->request->userAgent(),
    //             ])
    //             ->log("Pengguna {$user->name} berhasil masuk (login) ke dalam sistem");
    //     }
    // }

    public function handle(Login $event): void
    {
        $user = $event->user;

        if (function_exists('activity')) {
            activity('Autentikasi')
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties([
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log("Pengguna {$user->name} berhasil masuk (login) ke dalam sistem");
        }
    }
}
