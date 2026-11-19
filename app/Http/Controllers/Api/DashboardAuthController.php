<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\Facility;
use App\Models\Region;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\SchoolVideo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class DashboardAuthController extends Controller
{
    public function registrationOptions(): JsonResponse
    {
        return response()->json([
            'data' => [
                'school_types' => SchoolType::query()->where('is_active', true)->orderBy('order')->get(['id', 'name']),
                'regions' => Region::query()->orderBy('name')->get(['id', 'name']),
                'districts' => \App\Models\District::query()->orderBy('name')->get(['id', 'name', 'region_id']),
                'curricula' => Curriculum::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'facilities' => Facility::query()->where('is_active', true)->orderBy('category')->orderBy('name')->get(['id', 'name', 'category']),
            ],
        ]);
    }

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

    public function registerSchool(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'min:1', 'max:100'],
            'last_name' => ['required', 'string', 'min:1', 'max:100'],
            'school_role' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9 ]{9,15}$/'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'school.name' => ['required', 'string', 'min:2', 'max:255'],
            'school.type_id' => ['required', 'integer', Rule::exists('school_types', 'id')->where('is_active', true)],
            'school.ownership' => ['required', Rule::in(['Private', 'Government', 'Faith-based', 'NGO / Community'])],
            'school.curriculum_id' => ['nullable', 'integer', Rule::exists('curricula', 'id')->where('is_active', true)],
            'school.gender_policy' => ['nullable', Rule::in(['mixed', 'boys', 'girls'])],
            'school.boarding_type' => ['nullable', Rule::in(['day', 'boarding', 'mixed'])],
            'school.religious_affiliation' => ['nullable', 'string', 'max:100'],
            'school.founded_year' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')],
            'school.website' => ['nullable', 'url', 'max:255'],
            'school.description' => ['required', 'string', 'min:30', 'max:1500'],
            'school.region_id' => ['required', 'integer', 'exists:regions,id'],
            'school.district_id' => [
                'required',
                'integer',
                Rule::exists('districts', 'id')->where(fn ($query) => $query->where('region_id', $request->input('school.region_id'))),
            ],
            'school.town' => ['required', 'string', 'max:120'],
            'school.po_box' => ['nullable', 'string', 'max:120'],
            'school.address' => ['required', 'string', 'max:1000'],
            'school.nearest_landmark' => ['nullable', 'string', 'max:255'],
            'school.maps_url' => ['nullable', 'url', 'max:500'],
            'school.phone' => ['nullable', 'string', 'max:30'],
            'school.email' => ['nullable', 'email', 'max:255'],
            'school.fees_from' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'school.fees_to' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'school.activities' => ['nullable', 'string', 'max:1500'],
            'school.youtube_url' => ['nullable', 'url', 'max:500'],
            'school.facebook_url' => ['nullable', 'url', 'max:500'],
            'school.instagram_url' => ['nullable', 'url', 'max:500'],
            'school.referral_source' => ['nullable', 'string', 'max:120'],
            'school.facility_ids' => ['nullable', 'array', 'max:100'],
            'school.facility_ids.*' => ['integer', 'distinct', Rule::exists('facilities', 'id')->where('is_active', true)],
            'terms_accepted' => ['accepted'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $schoolData = $data['school'];
        if (isset($schoolData['fees_from'], $schoolData['fees_to']) && (float) $schoolData['fees_to'] < (float) $schoolData['fees_from']) {
            throw ValidationException::withMessages([
                'school.fees_to' => ['Fees to must be equal to or greater than fees from.'],
            ]);
        }

        $youtubeId = null;
        if (! empty($schoolData['youtube_url'])) {
            $youtubeId = $this->youtubeId($schoolData['youtube_url']);
            if (! $youtubeId) {
                throw ValidationException::withMessages([
                    'school.youtube_url' => ['Enter a valid public YouTube video URL.'],
                ]);
            }
        }

        if (($request->hasFile('logo') || $request->hasFile('cover')) &&
            config('filesystems.disks.'.config('media-library.disk_name', 'public').'.driver') !== 's3') {
            return response()->json([
                'message' => 'Image uploads are not available yet. Your image storage must be configured before adding a logo or cover photo. Remove the selected images to submit your registration.',
            ], 503);
        }

        $email = Str::lower(trim($data['email']));
        $user = DB::transaction(function () use ($data, $schoolData, $email, $youtubeId) {
            $user = User::query()->create([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $email,
                'password' => $data['password'],
                'phone' => trim($data['phone']),
                'school_role' => trim($data['school_role']),
                'district_id' => (int) $schoolData['district_id'],
            ]);

            $user->forceFill(['user_type' => 'school_owner', 'status' => 'active'])->save();
            Role::findOrCreate('school_owner', 'web');
            $user->assignRole('school_owner');

            $school = School::query()->create([
                'owner_id' => $user->id,
                'type_id' => (int) $schoolData['type_id'],
                'district_id' => (int) $schoolData['district_id'],
                'name' => trim($schoolData['name']),
                'ownership' => $schoolData['ownership'],
                'tagline' => null,
                'description' => trim($schoolData['description']),
                'founded_year' => $schoolData['founded_year'] ?? null,
                'address' => trim($schoolData['address']),
                'town' => trim($schoolData['town']),
                'po_box' => $schoolData['po_box'] ?? null,
                'nearest_landmark' => $schoolData['nearest_landmark'] ?? null,
                'maps_url' => $schoolData['maps_url'] ?? null,
                'phone' => $schoolData['phone'] ?? null,
                'email' => isset($schoolData['email']) ? Str::lower(trim($schoolData['email'])) : null,
                'website' => $schoolData['website'] ?? null,
                'facebook_url' => $schoolData['facebook_url'] ?? null,
                'instagram_url' => $schoolData['instagram_url'] ?? null,
                'religious_affiliation' => $schoolData['religious_affiliation'] ?? null,
                'boarding_type' => $schoolData['boarding_type'] ?: 'day',
                'gender_policy' => $schoolData['gender_policy'] ?: 'mixed',
                'fees_from' => $schoolData['fees_from'] ?? null,
                'fees_to' => $schoolData['fees_to'] ?? null,
                'activities' => $schoolData['activities'] ?? null,
                'referral_source' => $schoolData['referral_source'] ?? null,
                'status' => 'pending',
                'verification_status' => 'pending',
                'submitted_at' => now(),
            ]);

            if (! empty($schoolData['curriculum_id'])) {
                $school->curricula()->sync([(int) $schoolData['curriculum_id']]);
            }
            if (! empty($schoolData['facility_ids'])) {
                $school->facilities()->sync(array_map('intval', $schoolData['facility_ids']));
            }
            if ($youtubeId) {
                SchoolVideo::query()->create([
                    'school_id' => $school->id,
                    'created_by' => $user->id,
                    'title' => 'School introduction',
                    'youtube_url' => $schoolData['youtube_url'],
                    'youtube_video_id' => $youtubeId,
                    'status' => 'pending',
                ]);
            }

            return $user;
        });

        $school = $user->ownedSchools()->latest()->firstOrFail();
        if ($request->hasFile('logo')) {
            $school->addMediaFromRequest('logo')->toMediaCollection('logo');
        }
        if ($request->hasFile('cover')) {
            $school->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

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

    private function youtubeId(string $url): ?string
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        $candidate = null;

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $candidate = trim($path, '/');
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            parse_str($parts['query'] ?? '', $query);
            $candidate = $query['v'] ?? null;

            if (! $candidate && preg_match('#/(?:embed|shorts)/([^/?]+)#', $path, $matches)) {
                $candidate = $matches[1];
            }
        }

        return is_string($candidate) && preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate)
            ? $candidate
            : null;
    }
}
