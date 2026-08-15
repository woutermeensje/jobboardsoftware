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
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route($roles === ['admin'] ? 'admin.login' : 'login.choice');
        }

        abort_unless(in_array($user->role, $roles, true), 403);

        return $next($request);
    }
}
