<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActiveRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (! $request->user()?->estado) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Esta cuenta está desactivada. Consulta con administración.']);
        }
        abort_unless(! $roles || in_array($request->user()->rol, $roles), 403);

        return $next($request);
    }
}
