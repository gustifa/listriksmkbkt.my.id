<?php

namespace App\Http\Middleware;

use Closure; // <--- Pastikan ada kata "use" di depan Closure
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            User::where('id', Auth::id())->update([
                'last_seen' => now()
            ]);
        }

        return $next($request);
    }
}