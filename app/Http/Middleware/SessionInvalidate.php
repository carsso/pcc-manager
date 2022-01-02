<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SessionInvalidate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if(Cache::has('session_kill_'.session()->getId()))
        {
            $guards = empty($guards) ? [null] : $guards;

            foreach ($guards as $guard) {
                Auth::guard($guard)->logout();
            }
            return redirect()->route('logout');
        }

        return $next($request);
    }
}
