<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteIsNotInMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! filter_var(Setting::get('maintenance_mode', '0'), FILTER_VALIDATE_BOOLEAN)) {
            return $next($request);
        }

        // Keep centralized sign-in, account recovery and dashboard redirects available.
        if (
            $request->is('login') ||
            $request->is('register') ||
            $request->is('forgot-password') ||
            $request->is('reset-password/*') ||
            $request->is('auth/dashboard-callback') ||
            $request->is('admin') ||
            $request->is('admin/*') ||
            $request->is('owner') ||
            $request->is('owner/*') ||
            $request->is('up')
        ) {
            return $next($request);
        }

        return response()->view('errors.503', [], 503);
    }
}
