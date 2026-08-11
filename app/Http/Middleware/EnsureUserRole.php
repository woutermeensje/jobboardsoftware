<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route(match ($role) {
                'admin' => 'admin.login',
                'werkgever' => 'login.werkgever',
                default => 'login.werkzoekende',
            });
        }

        abort_unless($user->role === $role, 403);

        return $next($request);
    }
}
