<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSchoolOwner
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        abort_unless(
            $user
                && $user->status === 'active'
                && $user->hasRole('school_owner'),
            403
        );

        return $next($request);
    }
}
