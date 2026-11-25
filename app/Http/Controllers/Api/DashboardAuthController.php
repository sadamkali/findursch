<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\Facility;
use App\Models\Region;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\SchoolVideo;
use App\Models\Setting;
use App\Models\User;
use App\Services\SchoolMediaStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;

class DashboardAuthController extends Controller
{
    private function publishedFormConfig(): array
    {
        $value = Setting::get('school_forms_published');
        if (is_array($value)) return $value;
        if (! is_string($value) || trim($value) === '') return ['version' => 1, 'fields' => [], 'option_lists' => []];
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : ['version' => 1, 'fields' => [], 'option_lists' => []];
    }

    private function validateCustomFieldValues(Request $request, string $target): array
    {
        $values = $request->input('custom_fields', []);
        if (! is_array($values)) {
            throw ValidationException::withMessages(['custom_fields' => ['The additional school details are not in a valid format.']]);
        }

        $config = $this->publishedFormConfig();
        $lists = collect($config['option_lists'] ?? [])->keyBy('key');
        $result = [];
        $errors = [];

        foreach (($config['fields'] ?? []) as $field) {
            if (! is_array($field) || empty($field['enabled'])) continue;
            $applies = $target === 'registration'
                ? ! empty($field['registration_step'])
                : ! empty($field['profile_enabled']);
            if (! $applies) continue;

            $key = (string) ($field['key'] ?? '');
            if ($key === '') continue;
            $type = (string) ($field['type'] ?? 'text');
            $value = $values[$key] ?? null;
            if ($type === 'checkbox' && $value === null) $value = false;
            if (is_string($value)) $value = trim($value);
            $required = (bool) ($field['required'] ?? false);
            if ($type === 'checkbox' && $required && ! in_array($value, [true, 1, '1'], true)) {
                $errors['custom_fields.'.$key] = ['Please confirm '.$field['label'].'.'];
                continue;
            }
            $empty = $value === null || $value === '' || $value === [];
            if ($empty && ! $required) continue;
            if ($empty && $required) {
                $errors['custom_fields.'.$key] = ['Please complete '.$field['label'].'.'];
                continue;
            }

            $options = collect($lists->get((string) ($field['option_list'] ?? ''), [])['options'] ?? [])
                ->where('enabled', true)->pluck('value')->all();
            $minLength = max(0, (int) ($field['min_length'] ?? 0));
            $rawMaxLength = (int) ($field['max_length'] ?? 0);
            $maxLength = $rawMaxLength > 0 ? max(1, min(5000, $rawMaxLength)) : ($type === 'textarea' ? 5000 : 500);
            $minValue = is_numeric($field['min_value'] ?? null) ? $field['min_value'] : 0;
            $numberBounds = is_numeric($field['max_value'] ?? null) ? ['max:'.$field['max_value']] : [];
            $lengthRules = array_filter([
                $minLength > 0 ? 'min:'.$minLength : null,
                'max:'.$maxLength,
            ]);
            $rules = match ($type) {
                'number' => ['required', 'numeric', 'min:'.$minValue, ...$numberBounds],
                'url' => ['required', 'url', 'max:'.min(2000, $maxLength), ...($minLength > 0 ? ['min:'.$minLength] : [])],
                'email' => ['required', 'email', 'max:'.min(255, $maxLength), ...($minLength > 0 ? ['min:'.$minLength] : [])],
                'date' => ['required', 'date'],
                'checkbox' => ['required', 'boolean'],
                'select', 'radio' => ['required', 'string', Rule::in($options)],
                'checkboxes' => ['required', 'array', 'min:'.($required ? 1 : 0)],
                'phone' => ['required', 'string', ...array_values($lengthRules)],
                'textarea' => ['required', 'string', ...array_values($lengthRules)],
                default => ['required', 'string', ...array_values($lengthRules)],
            };
            $validationRules = ['value' => $rules];
            if ($type === 'checkboxes') $validationRules['value.*'] = [Rule::in($options)];
            $validator = Validator::make(['value' => $value], $validationRules);
            if ($validator->fails()) {
                $errors['custom_fields.'.$key] = ['Enter a valid value for '.$field['label'].'.'];
                continue;
            }
            $result[$key] = $validator->validated()['value'];
        }

        if ($errors) throw ValidationException::withMessages($errors);
        return $result;
    }


    private function registrationChoices(): array
    {
        $defaults = [
            'school_roles' => [
                ['id' => 'school_role_director', 'value' => 'Director / Proprietor', 'label' => 'Director / Proprietor', 'enabled' => true],
                ['id' => 'school_role_head_teacher', 'value' => 'Head teacher / Principal', 'label' => 'Head teacher / Principal', 'enabled' => true],
                ['id' => 'school_role_deputy_head_teacher', 'value' => 'Deputy head teacher', 'label' => 'Deputy head teacher', 'enabled' => true],
                ['id' => 'school_role_administrator', 'value' => 'Administrator', 'label' => 'Administrator', 'enabled' => true],
                ['id' => 'school_role_bursar', 'value' => 'Bursar', 'label' => 'Bursar', 'enabled' => true],
                ['id' => 'school_role_marketing', 'value' => 'Marketing officer', 'label' => 'Marketing officer', 'enabled' => true],
                ['id' => 'school_role_other', 'value' => 'Other', 'label' => 'Other', 'enabled' => true],
            ],
            'ownerships' => [
                ['id' => 'ownership_private', 'value' => 'Private', 'label' => 'Private', 'enabled' => true],
                ['id' => 'ownership_government', 'value' => 'Government', 'label' => 'Government', 'enabled' => true],
                ['id' => 'ownership_faith_based', 'value' => 'Faith-based', 'label' => 'Faith-based', 'enabled' => true],
                ['id' => 'ownership_ngo_community', 'value' => 'NGO / Community', 'label' => 'NGO / Community', 'enabled' => true],
            ],
            'gender_policies' => [
                ['id' => 'gender_mixed', 'value' => 'mixed', 'label' => 'Mixed (boys and girls)', 'enabled' => true],
                ['id' => 'gender_boys', 'value' => 'boys', 'label' => 'Boys only', 'enabled' => true],
                ['id' => 'gender_girls', 'value' => 'girls', 'label' => 'Girls only', 'enabled' => true],
            ],
            'boarding_types' => [
                ['id' => 'boarding_day', 'value' => 'day', 'label' => 'Day', 'enabled' => true],
                ['id' => 'boarding_full', 'value' => 'boarding', 'label' => 'Boarding', 'enabled' => true],
                ['id' => 'boarding_mixed', 'value' => 'mixed', 'label' => 'Day and boarding', 'enabled' => true],
            ],
            'religious_affiliations' => [
                ['id' => 'religion_non_denominational', 'value' => 'Non-denominational', 'label' => 'Non-denominational', 'enabled' => true],
                ['id' => 'religion_catholic', 'value' => 'Catholic', 'label' => 'Catholic', 'enabled' => true],
                ['id' => 'religion_anglican', 'value' => 'Anglican', 'label' => 'Anglican', 'enabled' => true],
                ['id' => 'religion_pentecostal', 'value' => 'Pentecostal', 'label' => 'Pentecostal', 'enabled' => true],
                ['id' => 'religion_sda', 'value' => 'Seventh-day Adventist', 'label' => 'Seventh-day Adventist', 'enabled' => true],
                ['id' => 'religion_muslim', 'value' => 'Muslim', 'label' => 'Muslim', 'enabled' => true],
                ['id' => 'religion_none', 'value' => 'None', 'label' => 'None', 'enabled' => true],
            ],
            'referral_sources' => [
                ['id' => 'referral_search', 'value' => 'Search engine', 'label' => 'Search engine', 'enabled' => true],
                ['id' => 'referral_social', 'value' => 'Social media', 'label' => 'Social media', 'enabled' => true],
                ['id' => 'referral_school', 'value' => 'Another school', 'label' => 'Another school', 'enabled' => true],
                ['id' => 'referral_word', 'value' => 'Word of mouth', 'label' => 'Word of mouth', 'enabled' => true],
                ['id' => 'referral_team', 'value' => 'Your team contacted us', 'label' => 'Your team contacted us', 'enabled' => true],
                ['id' => 'referral_other', 'value' => 'Other', 'label' => 'Other', 'enabled' => true],
            ],
        ];

        $saved = Setting::get('school_registration_choices');
        if (is_string($saved)) {
            $decoded = json_decode($saved, true);
            $saved = is_array($decoded) ? $decoded : [];
        }

        return array_replace($defaults, is_array($saved) ? $saved : []);
    }

    private function activeRegistrationChoiceValues(string $key): array
    {
        return collect($this->registrationChoices()[$key] ?? [])
            ->filter(fn ($option) => is_array($option) && (bool) ($option['enabled'] ?? true))
            ->pluck('value')
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->values()
            ->all();
    }

    public function registrationOptions(): JsonResponse
    {
        $config = $this->publishedFormConfig();
        return response()->json([
            'data' => [
                'school_types' => SchoolType::query()->where('is_active', true)->orderBy('order')->get(['id', 'name']),
                'regions' => Region::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'districts' => \App\Models\District::query()
                    ->where('is_active', true)
                    ->whereHas('region', fn ($query) => $query->where('is_active', true))
                    ->orderBy('name')
                    ->get(['id', 'name', 'region_id']),
                'counties' => DB::table('counties')->where('is_active', true)->orderBy('name')
                    ->get(['id', 'name', 'district_id']),
                'sub_counties' => DB::table('sub_counties')->where('is_active', true)->orderBy('name')
                    ->get(['id', 'name', 'district_id', 'county_id']),
                'parishes' => DB::table('parishes')->where('is_active', true)->orderBy('name')
                    ->get(['id', 'name', 'sub_county_id']),
                'towns' => DB::table('sub_counties')->where('is_active', true)->orderBy('name')
                    ->get(['id', 'name', 'district_id', 'county_id']),
                'allow_custom_town' => filter_var(Setting::get('school_locations_allow_custom_town', '1'), FILTER_VALIDATE_BOOLEAN),
                'curricula' => Curriculum::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'facilities' => Facility::query()->where('is_active', true)->orderBy('category')->orderBy('name')->get(['id', 'name', 'category']),
                'registration_choices' => $this->registrationChoices(),
                'form_fields' => collect($config['fields'] ?? [])->where('enabled', true)->whereNotNull('registration_step')->sortBy('sort_order')->values(),
                'option_lists' => $config['option_lists'] ?? [],
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

    /**
     * Register a parent or guardian without creating a school listing.
     * This endpoint assigns the parent role server-side; callers cannot choose a role.
     */
    public function registerParent(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9 ]{9,15}$/', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = Str::lower(trim($data['email']));
        $phone = isset($data['phone']) ? trim($data['phone']) : null;

        $user = DB::transaction(function () use ($data, $email, $phone) {
            $user = User::query()->create([
                'name' => trim($data['name']),
                'email' => $email,
                'phone' => $phone ?: null,
                'password' => $data['password'],
            ]);

            $user->forceFill([
                'user_type' => 'parent',
                'status' => 'active',
            ])->save();

            Role::findOrCreate('parent', 'web');
            $user->assignRole('parent');

            return $user;
        });

        return $this->issueDashboardSession($user);
    }

    public function registerSchool(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'min:1', 'max:100'],
            'last_name' => ['required', 'string', 'min:1', 'max:100'],
            'school_role' => ['required', 'string', 'max:80', Rule::in($this->activeRegistrationChoiceValues('school_roles'))],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9 ]{9,15}$/'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'custom_fields' => ['sometimes', 'array'],
            'school.name' => ['required', 'string', 'min:2', 'max:255'],
            'school.type_id' => ['required', 'integer', Rule::exists('school_types', 'id')->where('is_active', true)],
            'school.ownership' => ['required', 'string', 'max:100', Rule::in($this->activeRegistrationChoiceValues('ownerships'))],
            'school.curriculum_id' => ['nullable', 'integer', Rule::exists('curricula', 'id')->where('is_active', true)],
            'school.gender_policy' => ['nullable', 'string', 'max:50', Rule::in($this->activeRegistrationChoiceValues('gender_policies'))],
            'school.boarding_type' => ['nullable', 'string', 'max:50', Rule::in($this->activeRegistrationChoiceValues('boarding_types'))],
            'school.religious_affiliation' => ['nullable', 'string', 'max:100', Rule::in($this->activeRegistrationChoiceValues('religious_affiliations'))],
            'school.founded_year' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')],
            'school.website' => ['nullable', 'url', 'max:255'],
            'school.description' => ['required', 'string', 'min:30', 'max:1500'],
            'school.region_id' => ['required', 'integer', Rule::exists('regions', 'id')->where('is_active', true)],
            'school.county_id' => ['required', 'integer', Rule::exists('counties', 'id')->where('is_active', true)],
            'school.sub_county_id' => ['required', 'integer', Rule::exists('sub_counties', 'id')->where('is_active', true)],
            'school.parish_id' => ['nullable', 'integer', Rule::exists('parishes', 'id')->where('is_active', true)],
            'school.district_id' => [
                'required',
                'integer',
                Rule::exists('districts', 'id')->where(fn ($query) => $query
                    ->where('region_id', $request->input('school.region_id'))
                    ->where('is_active', true)),
            ],
            'school.town' => ['nullable', 'string', 'max:120'],
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
            'school.referral_source' => ['nullable', 'string', 'max:120', Rule::in($this->activeRegistrationChoiceValues('referral_sources'))],
            'school.facility_ids' => ['nullable', 'array', 'max:100'],
            'school.facility_ids.*' => ['integer', 'distinct', Rule::exists('facilities', 'id')->where('is_active', true)],
            'terms_accepted' => ['accepted'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'badge' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $customFieldValues = $this->validateCustomFieldValues($request, 'registration');
        $schoolData = $data['school'];
        $districtId = (int) $schoolData['district_id'];
        $countyId = (int) $schoolData['county_id'];
        $subCountyId = (int) $schoolData['sub_county_id'];
        $county = DB::table('counties')->where('id', $countyId)
            ->where('district_id', $districtId)->where('is_active', true)->first();
        if (! $county) {
            throw ValidationException::withMessages(['school.county_id' => ['Choose a county within the selected district.']]);
        }
        $existingTown = DB::table('sub_counties')->where('id', $subCountyId)
            ->where('district_id', $districtId)->where('county_id', $countyId)
            ->where('is_active', true)->first();
        if (! $existingTown) {
            throw ValidationException::withMessages(['school.sub_county_id' => ['Choose a subcounty within the selected county.']]);
        }
        $parishId = isset($schoolData['parish_id']) ? (int) $schoolData['parish_id'] : null;
        if ($parishId && ! DB::table('parishes')->where('id', $parishId)
            ->where('sub_county_id', $subCountyId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['school.parish_id' => ['Choose a parish within the selected subcounty.']]);
        }
        $townName = $existingTown->name;
        $schoolData['town'] = $townName;
        $allowCustomTown = false;

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

        $hasRegistrationImages = $request->hasFile('logo') || $request->hasFile('badge') || $request->hasFile('cover');
        if ($hasRegistrationImages && ! SchoolMediaStorage::isConfigured()) {
            return response()->json([
                'message' => 'School image storage is not fully configured. You can remove the selected images and submit your school details, then add the logo, badge and cover photo from your dashboard.',
            ], 503);
        }

        $email = Str::lower(trim($data['email']));
        $approvalRequired = filter_var(Setting::get('schools_require_approval', '1'), FILTER_VALIDATE_BOOLEAN);
        $user = DB::transaction(function () use ($request, $data, $schoolData, $email, $youtubeId, $approvalRequired, $customFieldValues, $existingTown, $allowCustomTown, $townName, $districtId, $countyId, $parishId) {
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
                'district_id' => $districtId,
                'county_id' => $countyId,
                'sub_county_id' => $existingTown->id,
                'parish_id' => $parishId,
                'name' => trim($schoolData['name']),
                'ownership' => $schoolData['ownership'],
                'tagline' => null,
                'description' => trim($schoolData['description']),
                'custom_fields' => $customFieldValues,
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
                'status' => $approvalRequired ? 'pending' : 'active',
                'verification_status' => $approvalRequired ? 'pending' : 'verified',
                'verified_at' => $approvalRequired ? null : now(),
                'submitted_at' => now(),
            ]);

            if (! $existingTown && $allowCustomTown) {
                $normalizedTown = mb_strtolower($townName);
                $pendingSuggestion = DB::table('location_suggestions')
                    ->where('district_id', $districtId)
                    ->where('normalized_name', $normalizedTown)
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->first();

                if ($pendingSuggestion) {
                    DB::table('location_suggestions')->where('id', $pendingSuggestion->id)->update([
                        'school_count' => DB::raw('school_count + 1'),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('location_suggestions')->insert([
                        'district_id' => $districtId,
                        'suggested_name' => $townName,
                        'normalized_name' => $normalizedTown,
                        'source_school_id' => $school->id,
                        'suggested_by' => $user->id,
                        'school_count' => 1,
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

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

            try {
                if ($request->hasFile('logo')) {
                    $school->addMediaFromRequest('logo')->toMediaCollection('logo');
                }
                if ($request->hasFile('badge')) {
                    $school->addMediaFromRequest('badge')->toMediaCollection('badge');
                }
                if ($request->hasFile('cover')) {
                    $school->addMediaFromRequest('cover')->toMediaCollection('cover');
                }
            } catch (\Throwable $exception) {
                report($exception);
                throw ValidationException::withMessages([
                    'images' => ['We could not save one of the selected school images. Your registration was not completed. Please try again or submit without the images and add them from your dashboard.'],
                ]);
            }

            return $user;
        });

        $school = $user->ownedSchools()->latest()->firstOrFail();

        return $this->issueDashboardSession($user, true, [
            'registration' => [
                'school_name' => $school->name,
                'school_status' => $school->status,
                'verification_status' => $school->verification_status,
                'requires_review' => ! ($school->status === 'active' && $school->verification_status === 'verified'),
            ],
        ]);
    }


    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::broker()->reset(
            [
                'token' => $data['token'],
                'email' => Str::lower(trim($data['email'])),
                'password' => $data['password'],
                'password_confirmation' => $request->input('password_confirmation'),
            ],
            function (User $user, string $password): void {
                $invitationPending = (bool) $user->owner_invitation_pending && $user->status === 'inactive';

                $updates = ['password' => $password];
                if ($invitationPending) {
                    $updates['status'] = 'active';
                    $updates['owner_invitation_pending'] = false;
                    $updates['email_verified_at'] = now();
                }

                $user->forceFill($updates)->save();
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            $message = match ($status) {
                Password::INVALID_TOKEN => 'This password setup link is invalid or has expired. Ask the FindUrSch administrator to send a new invitation.',
                Password::INVALID_USER => 'No account was found for that email address.',
                Password::RESET_THROTTLED => 'A password change was attempted recently. Wait before trying again.',
                default => 'The password could not be changed. Request a new setup invitation.',
            };

            return response()->json(['message' => $message], $status === Password::RESET_THROTTLED ? 429 : 422);
        }

        return response()->json(['message' => 'Your password has been set. You can now sign in to your school account.']);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'login' => ['required_without:email', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $identifier = trim((string) ($credentials['login'] ?? $credentials['email'] ?? ''));
        $normalizedPhone = preg_replace('/[\\s().-]+/', '', $identifier);
        $email = Str::lower($identifier);

        $user = User::query()
            ->where(function ($query) use ($email, $identifier, $normalizedPhone) {
                $query->whereRaw('LOWER(email) = ?', [$email])
                    ->orWhere('phone', $identifier)
                    ->orWhere('phone', $normalizedPhone);
            })
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['The supplied sign-in details are incorrect.'],
                'email' => ['The supplied sign-in details are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json(['message' => 'This account is not active. Contact FindurSch administration.'], 403);
        }

        $isAdmin = $user->hasRole('admin') || $user->getAllPermissions()->contains(fn ($permission) => str_starts_with($permission->name, 'admin.'));
        if (! $user->hasAnyRole(['school_owner', 'parent']) && ! $isAdmin) {
            return response()->json(['message' => 'This account does not have access to the FindurSch workspace.'], 403);
        }

        $countryCode = strtoupper(substr((string) ($request->header('X-Vercel-IP-Country') ?? $request->header('CF-IPCountry') ?? ''), 0, 2));
        if ($isAdmin && ! $user->hasRole('admin') && preg_match('/^[A-Z]{2}$/', $countryCode)) {
            $country = DB::table('admin_login_countries')
                ->where('user_id', $user->id)
                ->where('country_code', $countryCode)
                ->first();
            $blockNewCountries = filter_var(Setting::get('block_new_country_signins', '0'), FILTER_VALIDATE_BOOLEAN);

            if (! $country) {
                $hasApprovedCountry = DB::table('admin_login_countries')
                    ->where('user_id', $user->id)
                    ->whereNotNull('approved_at')
                    ->exists();

                DB::table('admin_login_countries')->insert([
                    'user_id' => $user->id,
                    'country_code' => $countryCode,
                    'first_seen_at' => now(),
                    'approved_at' => $hasApprovedCountry && $blockNewCountries ? null : now(),
                    'approved_by' => $hasApprovedCountry && $blockNewCountries ? null : $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($hasApprovedCountry && $blockNewCountries) {
                    $this->notifyOwnerOfNewCountry($user, $countryCode);
                    return response()->json([
                        'message' => 'Sign-in from a new country is blocked until the FindurSch owner approves this location.',
                    ], 403);
                }
            } elseif ($blockNewCountries && ! $country->approved_at) {
                $this->notifyOwnerOfNewCountry($user, $countryCode);
                return response()->json([
                    'message' => 'Sign-in from this country is awaiting approval from the FindurSch owner.',
                ], 403);
            }
        }

        if ($isAdmin && filter_var(Setting::get('require_two_step', '0'), FILTER_VALIDATE_BOOLEAN)) {
            $code = (string) random_int(100000, 999999);
            DB::table('admin_login_challenges')->where('user_id', $user->id)->whereNull('used_at')->delete();
            $challengeId = DB::table('admin_login_challenges')->insertGetId([
                'user_id' => $user->id,
                'code_hash' => Hash::make($code),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'expires_at' => now()->addMinutes(10),
                'used_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            try {
                Mail::raw(
                    "Your FindurSch administrator sign-in code is ".$code.". It expires in 10 minutes. Do not share this code.",
                    fn ($message) => $message->to($user->email)->subject('FindurSch sign-in verification code')
                );
            } catch (\Throwable $exception) {
                DB::table('admin_login_challenges')->where('id', $challengeId)->delete();
                report($exception);
                return response()->json(['message' => 'The verification code could not be emailed. Check the backend mail configuration.'], 503);
            }
            return response()->json([
                'requires_two_step' => true,
                'challenge_id' => $challengeId,
                'email' => $user->email,
            ]);
        }

        return $this->issueDashboardSession($user, (bool) ($credentials['remember'] ?? true));
    }

    private function issueDashboardSession(User $user, bool $remember = true, array $additional = []): JsonResponse
    {
        $isAdmin = $user->hasRole('admin') || $user->getAllPermissions()->contains(fn ($permission) => str_starts_with($permission->name, 'admin.'));
        $timeout = max(30, (int) Setting::get('admin_session_timeout_minutes', 120));
        $expiresAt = $isAdmin ? now()->addMinutes($timeout) : now()->addDays($remember ? 30 : 1);
        $token = $user->createToken('findursch-dashboard', ['*'], $expiresAt)->plainTextToken;

        $user->forceFill(['last_login_at' => now()])->save();

        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name')->values();

        if ($isAdmin && ! $user->hasRole('admin') && filter_var(Setting::get('admin_signin_alerts', '1'), FILTER_VALIDATE_BOOLEAN)) {
            try {
                $recipients = User::query()->role('admin')->where('status', 'active')->pluck('email')->filter()->unique()->all();
                foreach ($recipients as $recipient) {
                    Mail::raw(
                        "An administrator signed in to FindurSch.\nAccount: ".$user->email."\nTime: ".now()->toDateTimeString()."\nIP: ".(request()->ip() ?? 'Not available')."\nDevice: ".substr((string) request()->userAgent(), 0, 500),
                        fn ($message) => $message->to($recipient)->subject('FindurSch administrator sign-in alert')
                    );
                }
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        $redirectTo = $isAdmin ? '/admin' : ($user->hasRole('school_owner') ? '/owner' : '/parent');

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles,
                'permissions' => $permissions,
            ],
            'redirectTo' => $redirectTo,
            ...$additional,
        ]);
    }

    public function handoff(Request $request): JsonResponse
    {
        $user = $request->user();

        $canReturnToWebsite = $user && (
            $user->hasRole('parent') ||
            $user->hasRole('school_owner') ||
            $user->hasRole('admin') ||
            $user->getAllPermissions()->contains(fn ($permission) => str_starts_with($permission->name, 'admin.'))
        );

        if (! $canReturnToWebsite || $user->status !== 'active') {
            return response()->json(['message' => 'Your account does not have access to return to the public website.'], 403);
        }

        $token = $user->createToken(
            'findursch-public-session-handoff',
            ['public-session-handoff'],
            now()->addMinutes(2)
        )->plainTextToken;

        return response()->json(['code' => $token]);
    }

    /**
     * Create a short-lived one-time code that moves an authenticated dashboard
     * user to the correct workspace host without sharing cookies across hosts.
     */
    public function createDashboardSessionHandoff(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user && (
            $user->hasRole('admin') ||
            $user->getAllPermissions()->contains(fn ($permission) => str_starts_with($permission->name, 'admin.'))
        );
        $isSchoolOwner = $user && $user->hasRole('school_owner');

        if (! $user || $user->status !== 'active' || (! $isAdmin && ! $isSchoolOwner)) {
            return response()->json(['message' => 'Only active school-owner or administrator accounts can open this workspace.'], 403);
        }

        $code = $user->createToken(
            'findursch-dashboard-session-handoff',
            ['dashboard-session-handoff'],
            now()->addMinutes(2)
        )->plainTextToken;

        // Revoke the token that authenticated this broker request. The new
        // workspace token is issued only after the one-time code is exchanged.
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'code' => $code,
            'workspace' => $isAdmin ? 'admin' : 'owner',
        ]);
    }

    /**
     * Exchange a one-time dashboard handoff code for a normal protected
     * session token. Codes are single-use and expire after two minutes.
     */
    public function exchangeDashboardSession(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $handoff = PersonalAccessToken::findToken($data['code']);
        abort_unless(
            $handoff &&
            $handoff->can('dashboard-session-handoff') &&
            $handoff->expires_at &&
            $handoff->expires_at->isFuture(),
            403
        );

        $user = $handoff->tokenable;
        abort_unless($user instanceof User && $user->status === 'active', 403);

        $isAdmin = $user->hasRole('admin') || $user->getAllPermissions()->contains(
            fn ($permission) => str_starts_with($permission->name, 'admin.')
        );
        $isSchoolOwner = $user->hasRole('school_owner');
        abort_unless($isAdmin || $isSchoolOwner, 403);

        $handoff->delete();

        $timeout = max(30, (int) Setting::get('admin_session_timeout_minutes', 120));
        $expiresAt = $isAdmin ? now()->addMinutes($timeout) : now()->addDays(30);
        $token = $user->createToken('findursch-dashboard', ['*'], $expiresAt)->plainTextToken;

        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name')->values();

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles,
                'permissions' => $permissions,
            ],
            'redirectTo' => $isAdmin ? '/admin' : '/owner',
        ])->header('Cache-Control', 'no-store, private');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            ],
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('admin') || $user->getAllPermissions()->contains(fn ($permission) => str_starts_with($permission->name, 'admin.'))), 403, 'Administrator access is required to update these account details.');

        $request->merge(['email' => Str::lower(trim((string) $request->input('email', '')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'current_password' => ['required', 'string'],
        ]);

        abort_unless(Hash::check($data['current_password'], $user->password), 422, 'Your current password is incorrect.');
        $emailChanged = Str::lower((string) $user->email) !== $data['email'];
        $user->forceFill([
            'name' => trim($data['name']),
            'email' => $data['email'],
            'phone' => isset($data['phone']) ? trim((string) $data['phone']) : null,
            ...($emailChanged ? ['email_verified_at' => null] : []),
        ])->save();

        return response()->json([
            'message' => 'Your account details have been updated.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            ],
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('admin') || $user->getAllPermissions()->contains(fn ($permission) => str_starts_with($permission->name, 'admin.'))), 403, 'Administrator access is required to change this password.');

        $minimum = max(10, (int) Setting::get('admin_min_password_length', 10));
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:'.$minimum, 'confirmed'],
        ]);

        abort_unless(Hash::check($data['current_password'], $user->password), 422, 'Your current password is incorrect.');
        $user->forceFill(['password' => $data['new_password']])->save();

        $currentToken = $user->currentAccessToken();
        if ($currentToken && method_exists($currentToken, 'getKey')) {
            $user->tokens()->where('id', '!=', $currentToken->getKey())->delete();
        } else {
            $user->tokens()->delete();
        }

        return response()->json(['message' => 'Your password has been updated. Other active sessions have been signed out.']);
    }

    public function verifyAdminCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'challenge_id' => ['required', 'integer'],
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $challenge = DB::table('admin_login_challenges')
            ->where('id', $data['challenge_id'])
            ->whereNull('used_at')
            ->first();
        abort_unless($challenge && now()->lessThan($challenge->expires_at), 422, 'This verification code has expired. Sign in again to request a new code.');

        $user = User::query()->findOrFail($challenge->user_id);
        abort_unless(strtolower($user->email) === strtolower($data['email']), 422, 'The verification code does not match this account.');
        abort_unless($user->status === 'active' && ($user->hasRole('admin') || $user->getAllPermissions()->contains(fn ($permission) => str_starts_with($permission->name, 'admin.'))), 403, 'Administrator access is no longer active for this account.');
        abort_unless(Hash::check($data['code'], $challenge->code_hash), 422, 'The verification code is incorrect.');

        DB::table('admin_login_challenges')->where('id', $challenge->id)->update(['used_at' => now(), 'updated_at' => now()]);
        return $this->issueDashboardSession($user, (bool) ($data['remember'] ?? true));
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

    private function notifyOwnerOfNewCountry(User $user, string $countryCode): void
    {
        try {
            $owners = User::query()->role('admin')->where('status', 'active')->pluck('email')->filter()->unique()->all();
            foreach ($owners as $email) {
                Mail::raw(
                    "A FindurSch administrator attempted to sign in from a new country.\nAccount: ".$user->email."\nCountry code: ".$countryCode."\nTime: ".now()->toDateTimeString()."\nThe sign-in was blocked until you approve this country in Admin users.",
                    fn ($message) => $message->to($email)->subject('FindurSch sign-in blocked from a new country')
                );
            }
        } catch (\Throwable $exception) {
            report($exception);
        }
    }


}
