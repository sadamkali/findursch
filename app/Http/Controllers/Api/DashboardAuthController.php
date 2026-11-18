<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class DashboardAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'account_type' => ['required', 'in:parent,school_owner'],
        ]);

        $email = Str::lower(trim($data['email']));

        $user = DB::transaction(function () use ($data, $email) {
            $user = User::query()->create([
                'name' => trim($data['name']),
                'email' => $email,
                'password' => $data['password'],
            ]);

            $user->forceFill([
                'user_type' => $data['account_type'],
                'status' => 'active',
            ])->save();

            Role::findOrCreate($data['account_type'], 'web');
            $user->assignRole($data['account_type']);

            return $user;
        });

        return $this->issueDashboardSession($user);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', Str::lower(trim($credentials['email'])))->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The supplied sign-in details are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json(['message' => 'This account is not active. Contact FindurSch administration.'], 403);
        }

        if (! $user->hasAnyRole(['school_owner', 'admin', 'parent'])) {
            return response()->json(['message' => 'This account does not have access to the FindurSch workspace.'], 403);
        }

        return $this->issueDashboardSession($user);
    }

    private function issueDashboardSession(User $user): JsonResponse
    {
        $token = $user->createToken(
            'findursch-dashboard',
            ['*'],
            now()->addDays(30)
        )->plainTextToken;

        $user->forceFill(['last_login_at' => now()])->save();

        $roles = $user->getRoleNames();

        $redirectTo = $user->hasRole('admin')
            ? '/admin'
            : ($user->hasRole('school_owner') ? '/owner' : '/parent');

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles,
            ],
            'redirectTo' => $redirectTo,
        ]);
    }

    public function handoff(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('parent')) {
            return response()->json(['message' => 'Only parent accounts can return to the public school website.'], 403);
        }

        $token = $user->createToken(
            'findursch-public-session-handoff',
            ['public-session-handoff'],
            now()->addMinutes(2)
        )->plainTextToken;

        return response()->json(['code' => $token]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Signed out.']);
    }
}
