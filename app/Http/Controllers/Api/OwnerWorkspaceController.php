<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountDeletionRequest;
use App\Models\Facility;
use App\Models\Inquiry;
use App\Models\Plan;
use App\Models\School;
use App\Models\SchoolAdvertisement;
use App\Models\SchoolFee;
use App\Models\SchoolMessageCampaign;
use App\Models\SchoolPost;
use App\Models\SchoolProgram;
use App\Models\SchoolVideo;
use App\Models\Setting;
use App\Services\SchoolMediaStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OwnerWorkspaceController extends Controller
{
    public function workspace(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');
        $schoolsQuery = $isAdmin ? School::query() : $user->ownedSchools();
        $schools = $schoolsQuery
            ->with([
                'type',
                'district',
                'activeSubscription.plan',
                'media' => fn ($query) => $query->whereIn('collection_name', ['logo', 'badge']),
            ])
            ->withCount(['inquiries', 'approvedReviews'])
            ->latest()
            ->limit($isAdmin ? 500 : 100)
            ->get()
            ->map(fn (School $school) => [
                'id' => $school->id,
                'name' => $school->name,
                'logo_url' => $school->getFirstMedia('logo')?->getUrl(),
                'badge_url' => $school->getFirstMedia('badge')?->getUrl(),
                'status' => $school->status,
                'verification_status' => $school->verification_status,
                'type' => $school->type ? ['name' => $school->type->name] : null,
                'district' => $school->district ? ['name' => $school->district->name] : null,
                'inquiries_count' => $school->inquiries_count,
                'reviews_count' => $school->approved_reviews_count,
                'views_count' => $school->views_count,
                'plan' => $school->activeSubscription?->plan?->name,
            ])->values();

        return response()->json([
            'data' => [
                'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
                'schools' => $schools,
                'plan' => $schools->first()['plan'] ?? null,
                'is_admin' => $isAdmin,
            ],
        ]);
    }

    private function publishedFormConfig(): array
    {
        $value = Setting::get('school_forms_published');
        if (is_array($value)) return $value;
        if (! is_string($value) || trim($value) === '') return ['version' => 1, 'fields' => [], 'option_lists' => []];
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : ['version' => 1, 'fields' => [], 'option_lists' => []];
    }

    private function profileCustomFieldDefinitions(): array
    {
        $config = $this->publishedFormConfig();
        $lists = collect($config['option_lists'] ?? [])->keyBy('key');
        return collect($config['fields'] ?? [])
            ->filter(fn ($field) => is_array($field) && ! empty($field['enabled']) && ! empty($field['profile_enabled']))
            ->sortBy('sort_order')
            ->map(function ($field) use ($lists) {
                $list = $lists->get((string) ($field['option_list'] ?? ''), []);
                $rawMaxLength = (int) ($field['max_length'] ?? 0);
                $maxLength = $rawMaxLength > 0
                    ? max(1, min(5000, $rawMaxLength))
                    : (($field['type'] ?? '') === 'textarea' ? 5000 : 500);
                return [
                    'key' => $field['key'],
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'help' => $field['help'] ?? '',
                    'placeholder' => $field['placeholder'] ?? '',
                    'required' => (bool) ($field['required'] ?? false),
                    'options' => collect($list['options'] ?? [])->where('enabled', true)->pluck('value')->values()->all(),
                    'min_length' => (int) ($field['min_length'] ?? 0),
                    'max_length' => $maxLength,
                    'min_value' => $field['min_value'] ?? null,
                    'max_value' => $field['max_value'] ?? null,
                    'rows' => (int) ($field['rows'] ?? 3),
                ];
            })->values()->all();
    }

    private function saveProfileCustomFields(Request $request, School $school): array
    {
        $existing = is_array($school->custom_fields) ? $school->custom_fields : [];
        $incoming = $request->input('custom_fields', []);
        if (! is_array($incoming)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['custom_fields' => ['Additional school details are not valid.']]);
        }

        $definitions = collect($this->profileCustomFieldDefinitions())->keyBy('key');
        $errors = [];
        foreach ($definitions as $key => $field) {
            if (! array_key_exists($key, $incoming)) continue;
            $value = $incoming[$key];
            if (is_string($value)) $value = trim($value);
            $empty = $value === null || $value === '' || $value === [];
            if ($field['type'] === 'checkbox' && ! empty($field['required']) && ! in_array($value, [true, 1, '1'], true)) {
                $errors['custom_fields.'.$key] = ['Please confirm '.$field['label'].'.'];
                continue;
            }
            if ($empty && ! empty($field['required'])) {
                $errors['custom_fields.'.$key] = ['Please complete '.$field['label'].'.'];
                continue;
            }
            if ($empty) {
                $existing[$key] = null;
                continue;
            }
            $minLength = max(0, (int) ($field['min_length'] ?? 0));
            $maxLength = max(1, (int) ($field['max_length'] ?? ($field['type'] === 'textarea' ? 5000 : 500)));
            $lengthRules = array_values(array_filter([
                $minLength > 0 ? 'min:'.$minLength : null,
                'max:'.$maxLength,
            ]));
            $minValue = is_numeric($field['min_value'] ?? null) ? $field['min_value'] : 0;
            $numberBounds = is_numeric($field['max_value'] ?? null) ? ['max:'.$field['max_value']] : [];
            $rules = match ($field['type']) {
                'number' => ['required', 'numeric', 'min:'.$minValue, ...$numberBounds],
                'url' => ['required', 'url', 'max:'.min(2000, $maxLength), ...($minLength > 0 ? ['min:'.$minLength] : [])],
                'email' => ['required', 'email', 'max:'.min(255, $maxLength), ...($minLength > 0 ? ['min:'.$minLength] : [])],
                'date' => ['required', 'date'],
                'checkbox' => ['required', 'boolean'],
                'select', 'radio' => ['required', 'string', \Illuminate\Validation\Rule::in($field['options'])],
                'checkboxes' => ['required', 'array', 'min:1'],
                'phone', 'textarea' => ['required', 'string', ...$lengthRules],
                default => ['required', 'string', ...$lengthRules],
            };
            $validationRules = ['value' => $rules];
            if ($field['type'] === 'checkboxes') $validationRules['value.*'] = [Rule::in($field['options'])];
            $validator = Validator::make(['value' => $value], $validationRules);
            if ($validator->fails()) {
                $errors['custom_fields.'.$key] = ['Enter a valid value for '.$field['label'].'.'];
                continue;
            }
            $existing[$key] = $validator->validated()['value'];
        }
        if ($errors) throw \Illuminate\Validation\ValidationException::withMessages($errors);
        return $existing;
    }

    public function profile(Request $request, int $school): JsonResponse
    {
        $record = $this->school($request, $school);

        if ($request->isMethod('put') || $request->isMethod('patch')) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'min:2', 'max:255'],
                'tagline' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:10000'],
                'address' => ['nullable', 'string', 'max:1000'],
                'district_id' => ['sometimes', 'required', 'integer', 'exists:districts,id'],
                'county_id' => ['sometimes', 'required', 'integer', 'exists:counties,id'],
                'sub_county_id' => ['sometimes', 'required', 'integer', 'exists:sub_counties,id'],
                'parish_id' => ['sometimes', 'nullable', 'integer', 'exists:parishes,id'],
                'phone' => ['nullable', 'string', 'max:30'],
                'email' => ['nullable', 'email', 'max:255'],
                'website' => ['nullable', 'url', 'max:255'],
                'whatsapp' => ['nullable', 'string', 'max:30'],
                'custom_fields' => ['sometimes', 'array'],
            ]);

            $locationWasSubmitted = array_intersect(['district_id', 'county_id', 'sub_county_id', 'parish_id'], array_keys($validated)) !== [];
            if ($locationWasSubmitted) {
                $districtId = (int) ($validated['district_id'] ?? $record->district_id);
                $countyId = (int) ($validated['county_id'] ?? $record->county_id);
                $subCountyId = (int) ($validated['sub_county_id'] ?? $record->sub_county_id);
                $parishId = filled($validated['parish_id'] ?? null) ? (int) $validated['parish_id'] : null;

                if (! DB::table('districts')->where('id', $districtId)->where('is_active', true)->exists()) {
                    throw ValidationException::withMessages(['district_id' => ['Choose an active district.']]);
                }
                if (! DB::table('counties')->where('id', $countyId)->where('district_id', $districtId)->where('is_active', true)->exists()) {
                    throw ValidationException::withMessages(['county_id' => ['Choose a county within the selected district.']]);
                }
                $subCounty = DB::table('sub_counties')->where('id', $subCountyId)
                    ->where('district_id', $districtId)->where('county_id', $countyId)
                    ->where('is_active', true)->first();
                if (! $subCounty) {
                    throw ValidationException::withMessages(['sub_county_id' => ['Choose a subcounty within the selected county.']]);
                }
                if ($parishId && ! DB::table('parishes')->where('id', $parishId)
                    ->where('sub_county_id', $subCountyId)->where('is_active', true)->exists()) {
                    throw ValidationException::withMessages(['parish_id' => ['Choose a parish within the selected subcounty.']]);
                }

                $validated['district_id'] = $districtId;
                $validated['county_id'] = $countyId;
                $validated['sub_county_id'] = $subCountyId;
                $validated['parish_id'] = $parishId;
                $validated['town'] = $subCounty->name;
            }

            unset($validated['custom_fields']);
            $changes = $validated;
            if ($request->exists('custom_fields')) {
                $changes['custom_fields'] = $this->saveProfileCustomFields($request, $record);
            }

            // Owners must re-submit material profile edits for review. Administrators
            // can correct public data without silently resetting verification status.
            if (! $request->user()->hasRole('admin')) {
                $changes = [
                    ...$changes,
                    'status' => 'draft',
                    'verification_status' => 'unverified',
                    'verified_at' => null,
                    'verified_by' => null,
                    'submitted_at' => null,
                    'rejection_reason' => null,
                ];
            }

            $record->update($changes);
        }

        $record->load(['type', 'district.region', 'county', 'subCounty', 'parish', 'activeSubscription.plan']);
        $record->setAttribute('logo_url', $record->getFirstMedia('logo')?->getUrl());
        $record->setAttribute('badge_url', $record->getFirstMedia('badge')?->getUrl());
        $plan = $record->activeSubscription?->plan;
        $galleryCount = $record->getMedia('gallery')->count();
        $done = [
            'info' => filled($record->description) && (filled($record->phone) || filled($record->email)),
            'logo' => $record->getMedia('logo')->isNotEmpty(),
            'badge' => $record->getMedia('badge')->isNotEmpty(),
            'photos' => $galleryCount > 0,
            'cover' => $record->getMedia('cover')->isNotEmpty(),
            'programmes' => $record->programs()->exists(),
            'facilities' => $record->facilities()->exists(),
            'admissions' => filled($record->admission_classes) && filled($record->admissions_contact),
            'location' => ($record->latitude !== null && $record->longitude !== null) || (filled($record->address) && (filled($record->maps_url) || filled($record->town))),
        ];
        $planSlug = $plan?->slug ?? 'free';
        $planName = $plan?->name ?? 'Free';
        $planLabel = $planSlug === 'free' ? 'Basic' : $planName;

        return response()->json(['data' => [
            'school' => $record,
            'location_options' => [
                'districts' => DB::table('districts')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'region_id']),
                'counties' => DB::table('counties')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'district_id']),
                'sub_counties' => DB::table('sub_counties')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'district_id', 'county_id']),
                'parishes' => DB::table('parishes')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sub_county_id']),
            ],
            'custom_profile_fields' => $this->profileCustomFieldDefinitions(),
            'completion' => $done,
            'plan' => [
                'slug' => $planSlug,
                'name' => $planLabel,
                'features' => $plan?->features ?? ['Basic listing', 'Contact info display', '1 photo upload'],
            ],
            'gallery_count' => $galleryCount,
            'public_visible' => $record->status === 'active' && $record->verification_status === 'verified',
            'public_url' => rtrim((string) config('app.url'), '/').'/schools/'.$record->slug,
        ]]);
    }

    public function admissions(Request $request, int $school): JsonResponse
    {
        $record = $this->school($request, $school);

        if ($request->isMethod('put') || $request->isMethod('patch')) {
            $data = $request->validate([
                'admissions_open' => ['required', 'boolean'],
                'admission_classes' => ['nullable', 'string', 'max:500'],
                'admission_deadline' => ['nullable', 'date'],
                'application_fee' => ['nullable', 'string', 'max:120'],
                'admissions_contact' => ['nullable', 'string', 'max:180'],
                'admission_requirements' => ['nullable', 'string', 'max:10000'],
                'how_to_apply' => ['nullable', 'string', 'max:10000'],
            ]);

            if ($data['admissions_open'] && (! filled($data['admission_classes'] ?? null) || ! filled($data['admissions_contact'] ?? null))) {
                return response()->json([
                    'message' => 'While admissions are open, add the classes you are admitting and an admissions contact.',
                ], 422);
            }

            // Admissions are separate from core school identity. Saving them does not
            // reset verification or public listing status.
            $record->forceFill($data)->save();
        }

        $record->load('activeSubscription.plan');
        $plan = $record->activeSubscription?->plan;
        $slug = $plan?->slug ?? 'free';

        return response()->json(['data' => [
            'school' => $record,
            'public_visible' => in_array($slug, ['standard', 'premium'], true),
            'plan' => ['slug' => $slug, 'name' => $plan?->name ?? 'Free'],
        ]]);
    }

    public function facilities(Request $request, int $school): JsonResponse
    {
        $record = $this->school($request, $school);

        if ($request->isMethod('put')) {
            $validated = $request->validate([
                'facilities' => ['present', 'array', 'max:100'],
                'facilities.*' => ['required', 'string', 'max:120'],
            ]);

            $names = collect($validated['facilities'])->unique()->values();
            $facilityIds = Facility::query()->where('is_active', true)->whereIn('name', $names)->pluck('id');

            if ($facilityIds->count() !== $names->count()) {
                return response()->json(['message' => 'One or more selected facilities are not available.'], 422);
            }

            $record->facilities()->sync($facilityIds);
        }

        return response()->json([
            'data' => [
                'facilities' => $record->facilities()->pluck('facilities.name')->all(),
                'available_facilities' => Facility::query()->where('is_active', true)->orderBy('category')->orderBy('name')->get(['id', 'name', 'category']),
            ],
        ]);
    }

    public function programmes(Request $request, int $school, ?int $programme = null): JsonResponse
    {
        $record = $this->school($request, $school);

        if ($request->isMethod('post') || $request->isMethod('patch')) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'min:2', 'max:180'],
                'level' => ['nullable', 'string', 'max:60'],
                'duration' => ['nullable', 'string', 'max:60'],
                'description' => ['nullable', 'string', 'max:5000'],
                'curriculum_id' => ['nullable', 'integer', 'exists:curricula,id'],
                'is_active' => ['sometimes', 'boolean'],
            ]);

            if ($request->isMethod('patch')) {
                abort_unless($programme, 404);
                $updated = $record->programs()->whereKey($programme)->firstOrFail();
                $updated->forceFill([...$validated, 'is_active' => $validated['is_active'] ?? true])->save();

                return response()->json(['data' => ['record' => $updated->fresh('curriculum')]]);
            }

            $created = $record->programs()->create([...$validated, 'is_active' => true]);

            return response()->json(['data' => ['record' => $created]], 201);
        }

        if ($request->isMethod('delete')) {
            abort_unless($programme, 404);
            $record->programs()->whereKey($programme)->firstOrFail()->delete();

            return response()->json(['message' => 'Programme removed.']);
        }

        return response()->json([
            'data' => ['records' => $record->programs()->with('curriculum')->latest()->get()],
        ]);
    }

    public function fees(Request $request, int $school, ?int $fee = null): JsonResponse
    {
        $record = $this->school($request, $school);

        if ($request->isMethod('post') || $request->isMethod('patch')) {
            $validated = $request->validate([
                'program_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('school_programs', 'id')->where(fn ($query) => $query->where('school_id', $record->id)),
                ],
                'category' => ['required', Rule::in(['tuition', 'boarding', 'uniform', 'meals', 'activities', 'other'])],
                'label' => ['required', 'string', 'max:180'],
                'amount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
                'period' => ['required', Rule::in(['per_term', 'per_year', 'once_off', 'per_month'])],
                'academic_year' => ['nullable', 'string', 'max:20'],
                'notes' => ['nullable', 'string', 'max:2000'],
                'is_active' => ['sometimes', 'boolean'],
            ]);

            if ($request->isMethod('patch')) {
                abort_unless($fee, 404);
                $updated = $record->fees()->whereKey($fee)->firstOrFail();
                $updated->forceFill([...$validated, 'currency' => 'UGX', 'is_active' => $validated['is_active'] ?? true])->save();

                return response()->json(['data' => ['record' => $updated->fresh('program')]]);
            }

            $created = $record->fees()->create([...$validated, 'currency' => 'UGX', 'is_active' => true]);

            return response()->json(['data' => ['record' => $created]], 201);
        }

        if ($request->isMethod('delete')) {
            abort_unless($fee, 404);
            $record->fees()->whereKey($fee)->firstOrFail()->delete();

            return response()->json(['message' => 'Fee removed.']);
        }

        return response()->json(['data' => ['records' => $record->fees()->with('program')->latest()->get()]]);
    }

    public function media(Request $request, int $school, string $collection, ?int $mediaId = null): JsonResponse
    {
        abort_unless(in_array($collection, ['cover', 'gallery', 'logo', 'badge'], true), 404);
        $record = $this->school($request, $school);

        if ($request->isMethod('post')) {
            if (! SchoolMediaStorage::isConfigured()) {
                return response()->json([
                    'message' => 'School image storage is not fully configured. Please check the Cloudflare R2 storage settings before uploading a school logo, badge or photo.',
                ], 503);
            }

            $request->validate([
                'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.(in_array($collection, ['logo', 'badge'], true) ? 2048 : 10240)],
            ]);

            try {
                // The logo and badge collections are single-file collections.
                // Saving a replacement therefore updates the school's current image.
                $media = $record->addMediaFromRequest('image')->toMediaCollection($collection);
            } catch (\Throwable $exception) {
                report($exception);

                return response()->json([
                    'message' => 'The school image could not be saved to permanent storage. Please try again.',
                ], 502);
            }

            return response()->json([
                'data' => [
                    'record' => [
                        'id' => $media->id,
                        'name' => $media->name,
                        'collection' => $collection,
                        'url' => $media->getUrl(),
                    ],
                    'logo_url' => $record->getFirstMedia('logo')?->getUrl(),
                    'badge_url' => $record->getFirstMedia('badge')?->getUrl(),
                    'identity_url' => $record->getFirstMedia('badge')?->getUrl()
                        ?: $record->getFirstMedia('logo')?->getUrl(),
                ],
            ], 201);
        }

        if ($request->isMethod('delete')) {
            abort_unless($mediaId, 404);
            $media = $record->media()
                ->where('collection_name', $collection)
                ->whereKey($mediaId)
                ->firstOrFail();
            $media->delete();

            return response()->json([
                'message' => 'Image removed.',
                'data' => [
                    'logo_url' => $record->getFirstMedia('logo')?->getUrl(),
                    'badge_url' => $record->getFirstMedia('badge')?->getUrl(),
                    'identity_url' => $record->getFirstMedia('badge')?->getUrl()
                        ?: $record->getFirstMedia('logo')?->getUrl(),
                ],
            ]);
        }

        return response()->json([
            'data' => [
                'records' => $record->getMedia($collection)->map(fn ($media) => [
                    'id' => $media->id,
                    'title' => $media->name,
                    'collection' => $collection,
                    'url' => $media->getUrl(),
                ])->values(),
                'logo_url' => $record->getFirstMedia('logo')?->getUrl(),
                'badge_url' => $record->getFirstMedia('badge')?->getUrl(),
                'identity_url' => $record->getFirstMedia('badge')?->getUrl()
                    ?: $record->getFirstMedia('logo')?->getUrl(),
            ],
        ]);
    }

    public function videos(Request $request, int $school, ?int $video = null): JsonResponse
    {
        $record = $this->school($request, $school);

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'title' => ['required', 'string', 'min:2', 'max:180'],
                'url' => ['required', 'url', 'max:500'],
            ]);

            $videoId = $this->youtubeId($validated['url']);
            if (! $videoId) {
                return response()->json(['message' => 'Enter a valid public YouTube video URL.'], 422);
            }

            $created = SchoolVideo::create([
                'school_id' => $record->id,
                'created_by' => $request->user()->id,
                'title' => $validated['title'],
                'youtube_url' => $validated['url'],
                'youtube_video_id' => $videoId,
                'status' => 'pending',
            ]);

            return response()->json(['data' => ['record' => $created]], 201);
        }

        if ($request->isMethod('delete')) {
            abort_unless($video, 404);
            SchoolVideo::query()->where('school_id', $record->id)->whereKey($video)->firstOrFail()->delete();

            return response()->json(['message' => 'Video removed.']);
        }

        return response()->json([
            'data' => ['records' => SchoolVideo::query()->where('school_id', $record->id)->latest()->get()],
        ]);
    }

    public function content(Request $request, int $school, string $type, ?int $post = null): JsonResponse
    {
        $record = $this->school($request, $school);
        $types = ['events' => 'event', 'news' => 'news', 'bursaries' => 'bursary'];
        abort_unless(isset($types[$type]), 404);
        $postType = $types[$type];

        if ($request->isMethod('post')) {
            $rules = [
                'title' => ['required', 'string', 'min:2', 'max:255'],
                'description' => ['required', 'string', 'max:10000'],
                'image_url' => ['nullable', 'url', 'max:2048'],
                'image_alt' => ['nullable', 'string', 'max:255'],
            ];

            if ($postType === 'event') {
                $rules['starts_at'] = ['required', 'date'];
                $rules['ends_at'] = ['nullable', 'date', 'after_or_equal:starts_at'];
                $rules['venue'] = ['nullable', 'string', 'max:255'];
            }

            if ($postType === 'bursary') {
                $rules['deadline'] = ['nullable', 'date'];
                $rules['award'] = ['nullable', 'string', 'max:180'];
            }

            $validated = $request->validate($rules);
            $created = SchoolPost::create([
                ...$validated,
                'school_id' => $record->id,
                'created_by' => $request->user()->id,
                'type' => $postType,
                'status' => 'draft',
            ]);

            return response()->json(['data' => ['record' => $created]], 201);
        }

        if ($request->isMethod('delete')) {
            abort_unless($post, 404);
            SchoolPost::query()
                ->where('school_id', $record->id)
                ->where('type', $postType)
                ->whereKey($post)
                ->firstOrFail()
                ->delete();

            return response()->json(['message' => 'Draft removed.']);
        }

        return response()->json([
            'data' => [
                'records' => SchoolPost::query()->where('school_id', $record->id)->where('type', $postType)->latest()->get(),
            ],
        ]);
    }

    public function advertisements(Request $request, int $school, ?int $advertisement = null): JsonResponse
    {
        $record = $this->school($request, $school);

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'title' => ['required', 'string', 'min:2', 'max:180'],
                'placement' => ['required', Rule::in(['school_spotlight', 'homepage_banner', 'search_feature'])],
                'url' => ['nullable', 'url', 'max:500'],
                'description' => ['required', 'string', 'max:5000'],
                'image_url' => ['nullable', 'url', 'max:2048'],
                'image_alt' => ['nullable', 'string', 'max:255'],
                'cta_label' => ['nullable', 'string', 'max:80'],
            ]);

            $created = SchoolAdvertisement::create([
                'school_id' => $record->id,
                'created_by' => $request->user()->id,
                'title' => $validated['title'],
                'placement' => $validated['placement'],
                'destination_url' => $validated['url'] ?? null,
                'description' => $validated['description'],
                'image_url' => $validated['image_url'] ?? null,
                'image_alt' => $validated['image_alt'] ?? null,
                'cta_label' => $validated['cta_label'] ?? null,
                'status' => 'draft',
            ]);

            return response()->json(['data' => ['record' => $created]], 201);
        }

        if ($request->isMethod('delete')) {
            abort_unless($advertisement, 404);
            SchoolAdvertisement::query()->where('school_id', $record->id)->whereKey($advertisement)->firstOrFail()->delete();

            return response()->json(['message' => 'Advertisement draft removed.']);
        }

        return response()->json([
            'data' => ['records' => SchoolAdvertisement::query()->where('school_id', $record->id)->latest()->get()],
        ]);
    }

    public function messageCampaigns(Request $request, int $school, ?int $campaign = null): JsonResponse
    {
        $record = $this->school($request, $school);

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name' => ['nullable', 'string', 'max:180'],
                'channel' => ['required', Rule::in(['whatsapp', 'sms', 'email'])],
                'audience' => ['nullable', Rule::in(['enquiries', 'uploaded', 'manual'])],
                'recipients' => ['nullable', 'string', 'max:50000'],
                'message' => ['required', 'string', 'min:1', 'max:10000'],
            ]);

            if (($validated['audience'] ?? 'manual') === 'enquiries') {
                $contactColumn = $validated['channel'] === 'email' ? 'parent_email' : 'parent_phone';
                $recipients = Inquiry::query()
                    ->where('school_id', $record->id)
                    ->whereNotNull($contactColumn)
                    ->where($contactColumn, '!=', '')
                    ->distinct()
                    ->pluck($contactColumn)
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            } else {
                $recipients = $this->parseRecipients($validated['recipients'] ?? '', $validated['channel']);
            }
            if (count($recipients) > 5000) {
                return response()->json(['message' => 'A message campaign can contain up to 5,000 recipients.'], 422);
            }
            if ($recipients === []) {
                return response()->json(['message' => 'Choose parents who enquired or add at least one valid contact.'], 422);
            }

            $created = SchoolMessageCampaign::create([
                'school_id' => $record->id,
                'created_by' => $request->user()->id,
                'name' => $validated['name'] ?? null,
                'channel' => $validated['channel'],
                'audience' => $validated['audience'] ?? 'manual',
                'recipients' => $recipients,
                'message' => $validated['message'],
                'status' => 'draft',
            ]);

            return response()->json(['data' => ['record' => $created]], 201);
        }

        if ($request->isMethod('delete')) {
            abort_unless($campaign, 404);
            SchoolMessageCampaign::query()->where('school_id', $record->id)->whereKey($campaign)->firstOrFail()->delete();

            return response()->json(['message' => 'Message draft removed.']);
        }

        $enquiryPhoneCount = $record->inquiries()
            ->whereNotNull('parent_phone')->where('parent_phone', '!=', '')
            ->distinct()->count('parent_phone');
        $enquiryEmailCount = $record->inquiries()
            ->whereNotNull('parent_email')->where('parent_email', '!=', '')
            ->distinct()->count('parent_email');

        return response()->json([
            'data' => [
                'records' => SchoolMessageCampaign::query()->where('school_id', $record->id)->latest()->get(),
                'enquiries_phone_count' => $enquiryPhoneCount,
                'enquiries_email_count' => $enquiryEmailCount,
            ],
        ]);
    }

    public function subscription(Request $request, int $school): JsonResponse
    {
        $record = $this->school($request, $school)->load(['activeSubscription.plan', 'activeSubscription.payments']);
        $subscription = $record->activeSubscription;

        return response()->json([
            'data' => [
                'plan_name' => $subscription?->plan?->name ?? 'Free listing',
                'subscription' => $subscription,
                'plans' => Plan::query()->active()->orderBy('order')->get(),
                'payments' => $subscription?->payments ?? collect(),
            ],
        ]);
    }

    public function account(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($request->isMethod('put')) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'min:2', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            ]);

            $user->update($validated);
        }

        $deletionRequest = AccountDeletionRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return response()->json([
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'phone', 'status', 'created_at']),
                'deletion_request' => $deletionRequest,
            ],
        ]);
    }

    public function requestDeletion(Request $request): JsonResponse
    {
        $user = $request->user();
        $existing = AccountDeletionRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You already have an account deletion request awaiting review.',
                'data' => ['request' => $existing],
            ], 409);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $deletionRequest = AccountDeletionRequest::create([
            'user_id' => $user->id,
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        return response()->json([
            'message' => 'Your account deletion request has been submitted for review.',
            'data' => ['request' => $deletionRequest],
        ], 201);
    }

    public function password(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $request->user()->password)) {
            return response()->json(['message' => 'Your current password is incorrect.'], 422);
        }

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['message' => 'Password updated.']);
    }

    private function parseRecipients(string $raw, string $channel): array
    {
        $recipients = [];

        foreach (preg_split('/\\r\\n|\\r|\\n/', $raw) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $cells = str_contains($line, ',') ? str_getcsv($line) : [$line];
            foreach ($cells as $cell) {
                $value = trim((string) $cell);
                if ($channel === 'email') {
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $recipients[] = strtolower($value);
                    }
                } elseif (preg_match('/^\\+?[0-9][0-9\\s()\\-]{6,}$/', $value)) {
                    $recipients[] = preg_replace('/[\\s()\\-]+/', '', $value);
                }
            }
        }

        return array_values(array_unique($recipients));
    }

    private function school(Request $request, int $id): School
    {
        $query = School::query()->whereKey($id);

        if (! $request->user()->hasRole('admin')) {
            $query->where('owner_id', $request->user()->id);
        }

        return $query->firstOrFail();
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
