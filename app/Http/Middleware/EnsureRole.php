<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (! $user || $user->status === 'inactive') {
            abort(403);
        }

        if (! in_array($user->role, $roles, true)) {
            if ($request->expectsJson() || $request->ajax()) {
                abort(403, 'Unauthorized.');
            }
            return redirect()->route('home');
        }

        return $next($request);
    }
}
