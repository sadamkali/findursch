<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class SyncDashboardIdentity
{
    /**
     * Keep the public website header in sync with the shared dashboard session.
     * This is navigation-only identity: it does not authenticate owners or
     * administrators into parent account features.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            $plainTextToken = $request->cookie('findursch_api_token');
            $identity = null;

            if (is_string($plainTextToken) && $plainTextToken !== '') {
                $accessToken = PersonalAccessToken::findToken($plainTextToken);
                $tokenIsActive = $accessToken && (
                    ! $accessToken->expires_at || $accessToken->expires_at->isFuture()
                );

                if ($tokenIsActive) {
                    $user = $accessToken->tokenable;

                    if ($user instanceof User && $user->status === 'active') {
                        $roles = $user->getRoleNames();
                        $isAdmin = $roles->contains('admin') || $user->getAllPermissions()->contains(
                            fn ($permission) => str_starts_with($permission->name, 'admin.')
                        );

                        if ($isAdmin || $roles->contains('school_owner')) {
                            $identity = [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'role' => $isAdmin ? 'admin' : 'school_owner',
                            ];
                        }
                    }
                }
            }

            if ($identity) {
                $request->session()->put('dashboard_identity', $identity);
            } else {
                $request->session()->forget('dashboard_identity');
            }
        }

        return $next($request);
    }
}
