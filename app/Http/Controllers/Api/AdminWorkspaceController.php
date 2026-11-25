<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Inquiry;
use App\Models\JobListing;
use App\Models\Review;
use App\Models\School;
use App\Models\SchoolPost;
use App\Models\SubCounty;
use App\Models\User;
use App\Services\SchoolOwnerAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminWorkspaceController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.schools.view');
        $activity = DB::getSchemaBuilder()->hasTable('activity_log')
            ? DB::table('activity_log')->orderByDesc('created_at')->limit(8)->get([
                'id', 'description', 'subject_type', 'subject_id', 'created_at',
            ])
            : collect();

        return response()->json([
            'data' => [
                'schools_total' => School::query()->count(),
                'schools_active' => School::query()->where('status', 'active')->count(),
                'schools_pending' => School::query()->whereIn('status', ['draft', 'pending'])->count(),
                'schools_verified' => School::query()->where('verification_status', 'verified')->count(),
                'owners_total' => User::query()->where('user_type', 'school_owner')->count(),
                'parents_total' => User::query()->where('user_type', 'parent')->count(),
                'users_total' => User::query()->count(),
                'inquiries_new' => Inquiry::query()->where('status', 'new')->count(),
                'reviews_pending' => Review::query()->where('status', 'pending')->count(),
                'articles_published' => Article::query()->where('status', 'published')->count(),
                'contact_messages_new' => DB::table('contact_messages')->where('status', 'new')->count(),
                'recent_activity' => $activity,
                'recent_schools' => School::query()
                    ->with(['district:id,name', 'owner:id,name'])
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get(['id', 'name', 'status', 'verification_status', 'district_id', 'owner_id', 'created_at']),
            ],
        ]);
    }

    public function index(Request $request, string $resource): JsonResponse
    {
        $permission = match ($resource) {
            'owners', 'parents' => 'admin.users.view',
            'enquiries', 'contacts' => 'admin.enquiries.view',
            'reviews' => 'admin.reviews.moderate',
            'articles', 'article-categories', 'jobs' => 'admin.content.publish',
            'events' => 'admin.content.events',
            'scholarships' => 'admin.content.scholarships',
            'school-news' => 'admin.content.approve',
            'advertisements' => 'admin.ads.manage',
            'settings' => 'admin.system.edit_site_settings',
            'plans' => 'admin.billing.edit_plans',
            'payments', 'subscriptions' => 'admin.billing.view_payments',
            'activity' => 'admin.system.view_audit_log',
            default => 'admin.schools.view',
        };
        $this->authorizeWorkspacePermission($request, $permission);
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        switch ($resource) {
            case 'schools':
            case 'verification':
                $filters = $request->validate([
                    'q' => ['sometimes', 'nullable', 'string', 'max:150'],
                    'status' => ['sometimes', 'nullable', 'in:active,pending,draft,suspended,rejected'],
                    'verification_status' => ['sometimes', 'nullable', 'in:unverified,pending,verified'],
                    'page' => ['sometimes', 'integer', 'min:1'],
                    'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
                    'sort' => ['sometimes', 'in:name,created_at,status,id'],
                    'direction' => ['sometimes', 'in:asc,desc'],
                ]);

                $query = School::query()->with([
                    'district:id,name',
                    'county:id,name',
                    'subCounty:id,name',
                    'parish:id,name',
                    'type:id,name',
                    'owner:id,name,email,phone,status,owner_invitation_pending',
                    'media' => fn ($query) => $query->whereIn('collection_name', ['logo', 'badge']),
                ]);

                if ($resource === 'verification') {
                    $query->where(function ($builder) {
                        $builder->where('verification_status', 'pending')
                            ->orWhereIn('status', ['draft', 'pending']);
                    });
                }

                if (! empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }
                if (! empty($filters['verification_status'])) {
                    $query->where('verification_status', $filters['verification_status']);
                }
                if ($search !== '') {
                    $query->where(function ($builder) use ($search) {
                        $builder->where('name', 'ilike', '%'.$search.'%')
                            ->orWhere('email', 'ilike', '%'.$search.'%')
                            ->orWhere('phone', 'ilike', '%'.$search.'%');
                    });
                }

                $sortColumns = [
                    'name' => 'name',
                    'created_at' => 'created_at',
                    'status' => 'status',
                    'id' => 'id',
                ];
                $sort = $sortColumns[$filters['sort'] ?? 'created_at'];
                $direction = $filters['direction'] ?? 'desc';
                $perPage = (int) ($filters['per_page'] ?? 25);

                $paginator = $query
                    ->orderBy($sort, $direction)
                    ->paginate($perPage)
                    ->appends($request->query());

                $paginator->getCollection()->each(function (School $school) {
                    $school->setAttribute('logo_url', $school->getFirstMedia('logo')?->getUrl());
                    $school->setAttribute('badge_url', $school->getFirstMedia('badge')?->getUrl());
                });

                $summary = [
                    'total' => School::query()->count(),
                    'active' => School::query()->where('status', 'active')->count(),
                    'pending' => School::query()
                        ->where(fn ($builder) => $builder
                            ->whereIn('status', ['draft', 'pending'])
                            ->orWhere('verification_status', 'pending'))
                        ->count(),
                    'verified' => School::query()->where('verification_status', 'verified')->count(),
                    'needs_attention' => School::query()->whereIn('status', ['suspended', 'rejected'])->count(),
                ];

                return response()->json([
                    'data' => $paginator->items(),
                    'meta' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                        'from' => $paginator->firstItem(),
                        'to' => $paginator->lastItem(),
                    ],
                    'summary' => $summary,
                ]);

            case 'owners':
            case 'parents':
                $accountType = $resource === 'owners' ? 'school_owner' : 'parent';
                $query = User::query()->with('roles:id,name')
                    ->where('user_type', $accountType)
                    ->orderByDesc('created_at');

                if ($status) {
                    $query->where('status', $status);
                }
                if ($search !== '') {
                    $query->where(function ($builder) use ($search) {
                        $builder->where('name', 'ilike', '%'.$search.'%')
                            ->orWhere('email', 'ilike', '%'.$search.'%')
                            ->orWhere('phone', 'ilike', '%'.$search.'%');
                    });
                }

                $items = $query->limit(100)->get(['id', 'name', 'email', 'phone', 'school_role', 'user_type', 'status', 'owner_invitation_pending', 'email_verified_at', 'created_at'])
                    ->map(fn (User $user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'school_role' => $user->school_role,
                        'user_type' => $user->user_type,
                        'status' => $user->status,
                        'owner_invitation_pending' => (bool) $user->owner_invitation_pending,
                        'roles' => $user->getRoleNames(),
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                    ]);
                break;

            case 'enquiries':
                $query = Inquiry::query()->with('school:id,name')->orderByDesc('created_at');
                if ($status) $query->where('status', $status);
                if ($search !== '') {
                    $query->where(function ($builder) use ($search) {
                        $builder->where('parent_name', 'ilike', '%'.$search.'%')
                            ->orWhere('parent_email', 'ilike', '%'.$search.'%')
                            ->orWhereHas('school', fn ($school) => $school->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%']));
                    });
                }
                $items = $query->limit(100)->get();
                break;

            case 'reviews':
                $query = Review::query()->with(['school:id,name', 'user:id,name,email'])->orderByDesc('created_at');
                if ($status) $query->where('status', $status);
                $items = $query->limit(100)->get();
                break;

            case 'jobs':
                $jobQuery = JobListing::query()->orderByDesc('created_at');
                if ($status) {
                    $jobQuery->where('status', $status);
                }
                if ($search !== '') {
                    $jobQuery->where(function ($builder) use ($search) {
                        $builder->where('title', 'ilike', '%'.$search.'%')
                            ->orWhere('employer', 'ilike', '%'.$search.'%')
                            ->orWhere('location', 'ilike', '%'.$search.'%');
                    });
                }
                $items = $jobQuery->limit(100)->get();
                break;

            case 'articles':
                $items = Article::query()->with(['category:id,name', 'author:id,name'])
                    ->orderByDesc('created_at')->limit(100)->get();
                break;

            case 'article-categories':
                $items = ArticleCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']);
                break;

            case 'events':
            case 'scholarships':
            case 'school-news':
                $postType = match ($resource) {
                    'events' => 'event',
                    'scholarships' => 'bursary',
                    default => 'news',
                };
                $postQuery = SchoolPost::query()
                    ->where('type', $postType)
                    ->with('school:id,name')
                    ->orderByDesc('created_at');

                if ($status) {
                    $postQuery->where('status', $status);
                }
                if ($search !== '') {
                    $postQuery->where(function ($builder) use ($search) {
                        $builder->where('title', 'ilike', '%'.$search.'%')
                            ->orWhere('description', 'ilike', '%'.$search.'%')
                            ->orWhereHas('school', fn ($school) => $school->where('name', 'ilike', '%'.$search.'%'));
                    });
                }

                $items = $postQuery->limit(100)->get();
                break;

            case 'advertisements':
                $adQuery = DB::table('school_advertisements')
                    ->leftJoin('schools', 'schools.id', '=', 'school_advertisements.school_id')
                    ->leftJoin('users', 'users.id', '=', 'school_advertisements.created_by')
                    ->orderByDesc('school_advertisements.created_at');
                if ($status) $adQuery->where('school_advertisements.status', $status);
                if ($search !== '') {
                    $adQuery->where(function ($builder) use ($search) {
                        $builder->where('school_advertisements.title', 'ilike', '%'.$search.'%')
                            ->orWhere('schools.name', 'ilike', '%'.$search.'%');
                    });
                }
                $items = $adQuery->limit(100)->get([
                    'school_advertisements.id', 'school_advertisements.school_id',
                    'school_advertisements.is_platform_ad', 'school_advertisements.title',
                    'school_advertisements.placement', 'school_advertisements.destination_url',
                    'school_advertisements.description', 'school_advertisements.status',
                    'school_advertisements.starts_at', 'school_advertisements.ends_at',
                    'school_advertisements.image_url', 'school_advertisements.image_alt',
                    'school_advertisements.cta_label', 'school_advertisements.created_at',
                    'schools.name as school_name', 'schools.slug as school_slug', 'users.name as owner_name',
                ]);
                break;

            case 'settings':
                $items = DB::table('settings')->orderBy('group')->orderBy('key')
                    ->limit(300)->get(['id', 'key', 'value', 'type', 'group', 'label']);
                break;

            case 'plans':
                $items = DB::table('plans')->orderBy('order')->orderBy('price')
                    ->get(['id', 'name', 'slug', 'tagline', 'description', 'price', 'currency', 'duration_days', 'billing_cycle', 'features', 'is_active', 'includes_featured_slot', 'featured_slot_count', 'order']);
                break;

            case 'subscriptions':
                $items = DB::table('subscriptions')
                    ->leftJoin('schools', 'schools.id', '=', 'subscriptions.school_id')
                    ->leftJoin('plans', 'plans.id', '=', 'subscriptions.plan_id')
                    ->orderByDesc('subscriptions.created_at')
                    ->limit(100)
                    ->get([
                        'subscriptions.id', 'subscriptions.start_date', 'subscriptions.end_date',
                        'subscriptions.status', 'subscriptions.auto_renew', 'subscriptions.created_at',
                        'schools.name as school_name', 'plans.name as plan_name', 'plans.price as plan_price',
                    ]);
                break;

            case 'payments':
                $items = DB::table('payments')
                    ->leftJoin('schools', 'schools.id', '=', 'payments.school_id')
                    ->orderByDesc('payments.created_at')->limit(100)
                    ->get([
                        'payments.id', 'payments.amount', 'payments.currency', 'payments.payment_method',
                        'payments.transaction_id', 'payments.status', 'payments.gateway', 'payments.paid_at',
                        'payments.created_at', 'schools.name as school_name',
                    ]);
                break;

            case 'activity':
                $items = DB::getSchemaBuilder()->hasTable('activity_log')
                    ? DB::table('activity_log')->orderByDesc('created_at')->limit(100)
                        ->get(['id', 'description', 'subject_type', 'subject_id', 'causer_type', 'causer_id', 'created_at'])
                    : collect();
                break;

            case 'contacts':
                $items = DB::table('contact_messages')->orderByDesc('created_at')->limit(100)
                    ->get(['id', 'name', 'email', 'subject', 'message', 'status', 'created_at']);
                break;

            case 'locations':
                $items = DB::table('districts')
                    ->leftJoin('regions', 'regions.id', '=', 'districts.region_id')
                    ->orderBy('regions.name')->orderBy('districts.name')
                    ->get([
                        'districts.id', 'districts.name', 'districts.slug', 'districts.school_count',
                        'districts.is_featured', 'regions.name as region_name',
                    ]);
                break;

            default:
                return response()->json(['message' => 'Unknown administration section.'], 404);
        }

        return response()->json(['data' => $items]);
    }


    public function schoolEditor(Request $request, int $id): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.schools.edit_any');

        $school = School::query()
            ->with([
                'type:id,name',
                'district:id,name,region_id',
                'district.region:id,name',
                'county:id,name,district_id',
                'subCounty:id,name,district_id,county_id',
                'parish:id,name,sub_county_id',
                'owner:id,name,email,phone,school_role,status,owner_invitation_pending',
                'curricula:id,name,slug,abbreviation,is_active',
                'facilities:id,name,category',
                'programs:id,school_id,curriculum_id,name,level,duration,description,is_active',
                'programs.curriculum:id,name,abbreviation',
                'fees:id,school_id,program_id,category,label,amount,currency,period,academic_year,notes,is_active',
                'fees.program:id,name',
                'contacts:id,school_id,type,value,label,is_primary',
            ])
            ->findOrFail($id);

        $school->setAttribute('media_assets', collect(['logo', 'badge', 'cover', 'gallery'])
            ->flatMap(fn (string $collection) => $school->getMedia($collection)->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->name,
                'collection' => $collection,
                'url' => $media->getUrl(),
            ]))
            ->values());

        return response()->json([
            'data' => [
                'school' => $school,
                'options' => [
                    'school_types' => \App\Models\SchoolType::query()->where('is_active', true)->orderBy('order')->get(['id', 'name']),
                    'regions' => \App\Models\Region::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
                    'districts' => \App\Models\District::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'region_id']),
                    'counties' => DB::table('counties')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'district_id']),
                'sub_counties' => SubCounty::query()->orderBy('name')->get(['id', 'name', 'district_id', 'county_id']),
                'parishes' => DB::table('parishes')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sub_county_id']),
                    'curricula' => \App\Models\Curriculum::query()->orderBy('name')->get(['id', 'name', 'abbreviation', 'is_active']),
                    'facilities' => \App\Models\Facility::query()->where('is_active', true)->orderBy('category')->orderBy('name')->get(['id', 'name', 'category']),
                ],
            ],
        ]);
    }

    public function schoolEditorOptions(Request $request): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.schools.edit_any');

        return response()->json([
            'data' => [
                'school_types' => \App\Models\SchoolType::query()->where('is_active', true)->orderBy('order')->get(['id', 'name']),
                'regions' => \App\Models\Region::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'districts' => \App\Models\District::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'region_id']),
                'counties' => DB::table('counties')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'district_id']),
                'sub_counties' => SubCounty::query()->orderBy('name')->get(['id', 'name', 'district_id', 'county_id']),
                'parishes' => DB::table('parishes')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sub_county_id']),
                'curricula' => \App\Models\Curriculum::query()->orderBy('name')->get(['id', 'name', 'abbreviation', 'is_active']),
                'facilities' => \App\Models\Facility::query()->where('is_active', true)->orderBy('category')->orderBy('name')->get(['id', 'name', 'category']),
            ],
        ]);
    }

    public function updateSchoolEditor(Request $request, int $id): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.schools.edit_any');
        $school = School::query()->findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'type_id' => ['required', 'integer', 'exists:school_types,id'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'county_id' => ['nullable', 'integer', 'exists:counties,id'],
            'sub_county_id' => ['nullable', 'integer', 'exists:sub_counties,id'],
            'parish_id' => ['nullable', 'integer', 'exists:parishes,id'],
            'town' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:1000'],
            'po_box' => ['nullable', 'string', 'max:120'],
            'nearest_landmark' => ['nullable', 'string', 'max:255'],
            'maps_url' => ['nullable', 'url', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'registration_number' => ['nullable', 'string', 'max:60'],
            'founded_year' => ['nullable', 'integer', 'min:1800', 'max:'.now()->year],
            'ownership' => ['nullable', 'string', 'max:100'],
            'religious_affiliation' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'facebook_url' => ['nullable', 'url', 'max:500'],
            'instagram_url' => ['nullable', 'url', 'max:500'],
            'boarding_type' => ['required', 'in:boarding,day,mixed'],
            'gender_policy' => ['required', 'in:boys,girls,mixed'],
            'student_count_approx' => ['nullable', 'integer', 'min:0'],
            'fees_from' => ['nullable', 'numeric', 'min:0'],
            'fees_to' => ['nullable', 'numeric', 'min:0'],
            'activities' => ['nullable', 'string', 'max:5000'],
            'referral_source' => ['nullable', 'string', 'max:120'],
            'admissions_open' => ['required', 'boolean'],
            'admission_classes' => ['nullable', 'string', 'max:500'],
            'admission_deadline' => ['nullable', 'date'],
            'application_fee' => ['nullable', 'string', 'max:120'],
            'admissions_contact' => ['nullable', 'string', 'max:180'],
            'admission_requirements' => ['nullable', 'string', 'max:10000'],
            'how_to_apply' => ['nullable', 'string', 'max:10000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'custom_fields' => ['sometimes', 'array'],
            'curriculum_ids' => ['sometimes', 'array', 'max:20'],
            'curriculum_ids.*' => ['integer', 'distinct', 'exists:curricula,id'],
            'facility_ids' => ['sometimes', 'array', 'max:100'],
            'facility_ids.*' => ['integer', 'distinct', 'exists:facilities,id'],
            'status' => ['required', 'in:draft,pending,active,suspended,rejected'],
            'verification_status' => ['required', 'in:unverified,pending,verified'],
            'is_featured' => ['required', 'boolean'],
            'featured_until' => ['nullable', 'date'],
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $districtMatchesRegion = \App\Models\District::query()
            ->whereKey($data['district_id'])
            ->where('region_id', $data['region_id'])
            ->exists();
        if (! $districtMatchesRegion) {
            throw ValidationException::withMessages([
                'district_id' => ['Choose a district within the selected region.'],
            ]);
        }

        if (filled($data['county_id'] ?? null) && ! DB::table('counties')
            ->where('id', $data['county_id'])->where('district_id', $data['district_id'])->exists()) {
            throw ValidationException::withMessages(['county_id' => ['Choose a county within the selected district.']]);
        }
        if (filled($data['sub_county_id'] ?? null)) {
            $subCountyQuery = DB::table('sub_counties')->where('id', $data['sub_county_id'])
                ->where('district_id', $data['district_id']);
            if (filled($data['county_id'] ?? null)) $subCountyQuery->where('county_id', $data['county_id']);
            if (! $subCountyQuery->exists()) {
                throw ValidationException::withMessages(['sub_county_id' => ['Choose a subcounty within the selected county.']]);
            }
        }
        if (filled($data['parish_id'] ?? null) && ! DB::table('parishes')
            ->where('id', $data['parish_id'])->where('sub_county_id', $data['sub_county_id'] ?? null)->exists()) {
            throw ValidationException::withMessages(['parish_id' => ['Choose a parish within the selected subcounty.']]);
        }

        $permissions = ['admin.schools.edit_any'];
        $statusChanged = $data['status'] !== $school->status;
        $verificationChanged = $data['verification_status'] !== $school->verification_status;
        if ($statusChanged) {
            $permissions[] = $data['status'] === 'suspended'
                ? 'admin.schools.suspend'
                : 'admin.schools.approve';
        }
        if ($verificationChanged) {
            $permissions[] = 'admin.schools.approve';
        }
        if ((bool) $data['is_featured'] !== (bool) $school->is_featured ||
            ($data['featured_until'] ?? null) !== optional($school->featured_until)->format('Y-m-d')) {
            $permissions[] = 'admin.schools.feature';
        }
        foreach (array_unique($permissions) as $permission) {
            $this->authorizeWorkspacePermission($request, $permission);
        }

        DB::transaction(function () use ($school, $data, $request) {
            $curriculumIds = $data['curriculum_ids'] ?? null;
            $facilityIds = $data['facility_ids'] ?? null;
            unset($data['curriculum_ids'], $data['facility_ids']);

            if (($data['verification_status'] ?? null) === 'verified') {
                $data['verified_at'] = $school->verified_at ?? now();
                $data['verified_by'] = $school->verified_by ?? $request->user()->id;
                $data['rejection_reason'] = null;
            } elseif (($data['verification_status'] ?? null) !== 'verified') {
                $data['verified_at'] = null;
                $data['verified_by'] = null;
            }
            if ($data['status'] === 'rejected') {
                $data['verification_status'] = 'unverified';
                $data['verified_at'] = null;
                $data['verified_by'] = null;
            }

            $school->forceFill($data)->save();

            if ($curriculumIds !== null) {
                $school->curricula()->sync($curriculumIds);
            }
            if ($facilityIds !== null) {
                $school->facilities()->sync($facilityIds);
            }
        });

        return response()->json([
            'message' => 'The complete school profile has been saved.',
            'data' => $school->fresh([
                'type:id,name',
                'district:id,name,region_id',
                'district.region:id,name',
                'subCounty:id,name,district_id',
                'owner:id,name,email,phone,school_role,status,owner_invitation_pending',
                'curricula:id,name,slug,abbreviation,is_active',
                'facilities:id,name,category',
            ]),
        ]);
    }

    public function updateSchoolOwnerProfile(Request $request, int $id): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.schools.edit_any');
        $school = School::query()->with('owner')->findOrFail($id);
        $owner = $school->owner;

        abort_unless($owner && $owner->user_type === 'school_owner', 422, 'This school does not have a valid school-owner account.');

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$owner->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'school_role' => ['nullable', 'string', 'max:80'],
        ]);

        $owner->forceFill($data)->save();

        return response()->json([
            'message' => 'School-owner account details updated.',
            'data' => [
                'id' => $owner->id,
                'name' => $owner->name,
                'email' => $owner->email,
                'phone' => $owner->phone,
                'school_role' => $owner->school_role,
                'status' => $owner->status,
                'owner_invitation_pending' => (bool) $owner->owner_invitation_pending,
            ],
        ]);
    }

    public function setSchoolOwnerPassword(Request $request, int $id): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.schools.edit_any');
        $school = School::query()->with('owner')->findOrFail($id);
        $owner = $school->owner;

        abort_unless($owner && $owner->user_type === 'school_owner', 422, 'This school does not have a valid school-owner account.');

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed', 'max:128'],
        ]);

        $owner->forceFill([
            'password' => Hash::make($data['password']),
            'owner_invitation_pending' => false,
            'status' => $owner->status === 'inactive' && $owner->owner_invitation_pending ? 'active' : $owner->status,
        ])->save();

        // A manually reset password signs out any previous owner sessions.
        $owner->tokens()->delete();

        return response()->json([
            'message' => 'The school-owner password has been updated and existing sessions have been signed out.',
        ]);
    }

    public function createSchool(Request $request, SchoolOwnerAccountService $ownerAccounts): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.schools.edit_any');

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'type_id' => ['required', 'integer', 'exists:school_types,id'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'county_id' => ['required', 'integer', 'exists:counties,id'],
            'sub_county_id' => ['required', 'integer', 'exists:sub_counties,id'],
            'parish_id' => ['nullable', 'integer', 'exists:parishes,id'],
            'town' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:1000'],
            'po_box' => ['nullable', 'string', 'max:120'],
            'nearest_landmark' => ['nullable', 'string', 'max:255'],
            'maps_url' => ['nullable', 'url', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'registration_number' => ['nullable', 'string', 'max:60'],
            'founded_year' => ['nullable', 'integer', 'min:1800', 'max:'.now()->year],
            'ownership' => ['nullable', 'string', 'max:100'],
            'religious_affiliation' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'facebook_url' => ['nullable', 'url', 'max:500'],
            'instagram_url' => ['nullable', 'url', 'max:500'],
            'boarding_type' => ['required', 'in:boarding,day,mixed'],
            'gender_policy' => ['required', 'in:boys,girls,mixed'],
            'student_count_approx' => ['nullable', 'integer', 'min:0'],
            'fees_from' => ['nullable', 'numeric', 'min:0'],
            'fees_to' => ['nullable', 'numeric', 'min:0'],
            'activities' => ['nullable', 'string', 'max:5000'],
            'referral_source' => ['nullable', 'string', 'max:120'],
            'admissions_open' => ['required', 'boolean'],
            'admission_classes' => ['nullable', 'string', 'max:500'],
            'admission_deadline' => ['nullable', 'date'],
            'application_fee' => ['nullable', 'string', 'max:120'],
            'admissions_contact' => ['nullable', 'string', 'max:180'],
            'admission_requirements' => ['nullable', 'string', 'max:10000'],
            'how_to_apply' => ['nullable', 'string', 'max:10000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'custom_fields' => ['sometimes', 'array'],
            'curriculum_ids' => ['sometimes', 'array', 'max:20'],
            'curriculum_ids.*' => ['integer', 'distinct', 'exists:curricula,id'],
            'facility_ids' => ['sometimes', 'array', 'max:100'],
            'facility_ids.*' => ['integer', 'distinct', 'exists:facilities,id'],
            'status' => ['sometimes', 'in:draft,pending,active,suspended,rejected'],
            'verification_status' => ['sometimes', 'in:unverified,pending,verified'],
            'is_featured' => ['sometimes', 'boolean'],
            'featured_until' => ['nullable', 'date'],
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
            'owner_mode' => ['required', 'in:existing,new'],
            'owner_user_id' => ['required_if:owner_mode,existing', 'nullable', 'integer', 'exists:users,id'],
            'owner_name' => ['required_if:owner_mode,new', 'nullable', 'string', 'min:2', 'max:255'],
            'owner_email' => ['required_if:owner_mode,new', 'nullable', 'email', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'owner_school_role' => ['nullable', 'string', 'max:80'],
            'owner_password' => ['nullable', 'string', 'min:8', 'confirmed', 'max:128'],
        ]);

        $districtMatchesRegion = \App\Models\District::query()
            ->whereKey($data['district_id'])
            ->where('region_id', $data['region_id'])
            ->exists();
        if (! $districtMatchesRegion) {
            throw ValidationException::withMessages([
                'district_id' => ['Choose a district within the selected region.'],
            ]);
        }

        $countyMatchesDistrict = DB::table('counties')->where('id', $data['county_id'])
            ->where('district_id', $data['district_id'])->exists();
        if (! $countyMatchesDistrict) {
            throw ValidationException::withMessages(['county_id' => ['Choose a county within the selected district.']]);
        }
        $subCountyMatchesCounty = DB::table('sub_counties')->where('id', $data['sub_county_id'])
            ->where('district_id', $data['district_id'])->where('county_id', $data['county_id'])->exists();
        if (! $subCountyMatchesCounty) {
            throw ValidationException::withMessages(['sub_county_id' => ['Choose a subcounty within the selected county.']]);
        }
        if (filled($data['parish_id'] ?? null) && ! DB::table('parishes')
            ->where('id', $data['parish_id'])->where('sub_county_id', $data['sub_county_id'])->exists()) {
            throw ValidationException::withMessages(['parish_id' => ['Choose a parish within the selected subcounty.']]);
        }

        if (filled($data['sub_county_id'] ?? null)) {
            $subCountyMatchesDistrict = SubCounty::query()
                ->whereKey($data['sub_county_id'])
                ->where('district_id', $data['district_id'])
                ->exists();
            if (! $subCountyMatchesDistrict) {
                throw ValidationException::withMessages([
                    'sub_county_id' => ['Choose a sub-county within the selected district.'],
                ]);
            }
        }

        $data['status'] = $data['status'] ?? 'pending';
        $data['verification_status'] = $data['verification_status'] ?? 'unverified';
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);

        $permissions = ['admin.schools.edit_any'];
        if ($data['status'] === 'suspended') {
            $permissions[] = 'admin.schools.suspend';
        } elseif (in_array($data['status'], ['active', 'rejected'], true) ||
            $data['verification_status'] !== 'unverified') {
            $permissions[] = 'admin.schools.approve';
        }
        if ($data['is_featured'] || filled($data['featured_until'] ?? null)) {
            $permissions[] = 'admin.schools.feature';
        }
        foreach (array_unique($permissions) as $permission) {
            $this->authorizeWorkspacePermission($request, $permission);
        }

        $result = DB::transaction(function () use ($data, $ownerAccounts, $request) {
            $owner = $ownerAccounts->resolveOrCreate([
                'user_id' => $data['owner_mode'] === 'existing' ? ($data['owner_user_id'] ?? null) : null,
                'name' => $data['owner_name'] ?? null,
                'email' => $data['owner_email'] ?? null,
                'phone' => $data['owner_phone'] ?? null,
            ], (int) $data['district_id']);

            if (filled($data['owner_school_role'] ?? null)) {
                $owner['user']->forceFill(['school_role' => trim($data['owner_school_role'])])->save();
            }

            $hasManualPassword = filled($data['owner_password'] ?? null);
            if ($hasManualPassword) {
                $owner['user']->forceFill([
                    'password' => Hash::make($data['owner_password']),
                    'owner_invitation_pending' => false,
                    'status' => $owner['user']->status === 'inactive' && $owner['user']->owner_invitation_pending
                        ? 'active'
                        : $owner['user']->status,
                ])->save();
            }

            $status = $data['status'];
            $verificationStatus = $data['verification_status'];
            if ($status === 'rejected') {
                $verificationStatus = 'unverified';
            }

            $school = School::query()->create([
                'owner_id' => $owner['user']->id,
                'name' => trim($data['name']),
                'tagline' => $data['tagline'] ?? null,
                'description' => $data['description'] ?? null,
                'type_id' => (int) $data['type_id'],
                'district_id' => (int) $data['district_id'],
                'county_id' => (int) $data['county_id'],
                'sub_county_id' => (int) $data['sub_county_id'],
                'parish_id' => $data['parish_id'] ?? null,
                'town' => $data['town'] ?? null,
                'address' => $data['address'] ?? null,
                'po_box' => $data['po_box'] ?? null,
                'nearest_landmark' => $data['nearest_landmark'] ?? null,
                'maps_url' => $data['maps_url'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'registration_number' => $data['registration_number'] ?? null,
                'founded_year' => $data['founded_year'] ?? null,
                'ownership' => $data['ownership'] ?? null,
                'religious_affiliation' => $data['religious_affiliation'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => isset($data['email']) ? Str::lower(trim($data['email'])) : null,
                'website' => $data['website'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'facebook_url' => $data['facebook_url'] ?? null,
                'instagram_url' => $data['instagram_url'] ?? null,
                'boarding_type' => $data['boarding_type'],
                'gender_policy' => $data['gender_policy'],
                'student_count_approx' => $data['student_count_approx'] ?? null,
                'fees_from' => $data['fees_from'] ?? null,
                'fees_to' => $data['fees_to'] ?? null,
                'activities' => $data['activities'] ?? null,
                'referral_source' => $data['referral_source'] ?? null,
                'admissions_open' => (bool) $data['admissions_open'],
                'admission_classes' => $data['admission_classes'] ?? null,
                'admission_deadline' => $data['admission_deadline'] ?? null,
                'application_fee' => $data['application_fee'] ?? null,
                'admissions_contact' => $data['admissions_contact'] ?? null,
                'admission_requirements' => $data['admission_requirements'] ?? null,
                'how_to_apply' => $data['how_to_apply'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'custom_fields' => $data['custom_fields'] ?? [],
                'status' => $status,
                'verification_status' => $verificationStatus,
                'verified_at' => $verificationStatus === 'verified' ? now() : null,
                'verified_by' => $verificationStatus === 'verified' ? $request->user()->id : null,
                'is_featured' => $data['is_featured'],
                'featured_until' => $data['featured_until'] ?? null,
                'rejection_reason' => $data['rejection_reason'] ?? null,
                'submitted_at' => now(),
            ]);

            $school->curricula()->sync($data['curriculum_ids'] ?? []);
            $school->facilities()->sync($data['facility_ids'] ?? []);

            return [
                'school' => $school,
                'owner' => $owner['user'],
                'invite' => $owner['invitation_pending'] && ! $hasManualPassword,
                'manual_password' => $hasManualPassword,
            ];
        });

        if ($result['manual_password']) {
            // The password may belong to an owner shared by multiple schools.
            $result['owner']->tokens()->delete();
        }

        $invitationStatus = $result['invite']
            ? $ownerAccounts->sendInvitation($result['owner'])
            : null;
        $invitationSent = $invitationStatus === Password::RESET_LINK_SENT;

        $school = $result['school']->fresh([
            'type:id,name',
            'district:id,name,region_id',
            'district.region:id,name',
            'county:id,name,district_id',
            'subCounty:id,name,district_id,county_id',
            'parish:id,name,sub_county_id',
            'owner:id,name,email,phone,school_role,status,owner_invitation_pending',
            'curricula:id,name,slug,abbreviation,is_active',
            'facilities:id,name,category',
        ]);

        return response()->json([
            'message' => $result['invite']
                ? ($invitationSent
                    ? 'School created and its owner account was assigned. A password setup invitation has been sent.'
                    : 'School created and its owner account was assigned, but the invitation could not be sent. Resend it from the school account panel.')
                : ($result['manual_password']
                    ? 'School created and its owner account was assigned with the password you set.'
                    : 'School created and linked to the selected school-owner account.'),
            'data' => $school,
            'owner_invitation' => $invitationStatus,
        ], 201);
    }

    public function schoolOwnerOptions(Request $request): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.schools.view');
        $search = trim((string) $request->query('q', ''));

        $query = User::query()
            ->where('user_type', 'school_owner')
            ->where(function ($builder) {
                $builder->where('status', 'active')
                    ->orWhere(function ($pending) {
                        $pending->where('status', 'inactive')
                            ->where('owner_invitation_pending', true);
                    });
            })
            ->withCount('ownedSchools')
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'ilike', '%'.$search.'%')
                    ->orWhere('email', 'ilike', '%'.$search.'%')
                    ->orWhere('phone', 'ilike', '%'.$search.'%');
            });
        }

        return response()->json([
            'data' => $query->limit(100)->get([
                'id', 'name', 'email', 'phone', 'status', 'owner_invitation_pending',
            ]),
        ]);
    }

    public function updateSchoolOwner(Request $request, int $id, SchoolOwnerAccountService $ownerAccounts): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.schools.edit_any');
        $school = School::query()->findOrFail($id);

        $data = $request->validate([
            'owner_user_id' => ['required_without:owner_email', 'nullable', 'integer', 'exists:users,id'],
            'owner_name' => ['required_without:owner_user_id', 'nullable', 'string', 'min:2', 'max:255'],
            'owner_email' => ['required_without:owner_user_id', 'nullable', 'email', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $result = DB::transaction(function () use ($data, $school, $ownerAccounts) {
            $owner = $ownerAccounts->resolveOrCreate([
                'user_id' => $data['owner_user_id'] ?? null,
                'name' => $data['owner_name'] ?? null,
                'email' => $data['owner_email'] ?? null,
                'phone' => $data['owner_phone'] ?? null,
            ], (int) $school->district_id);

            $school->forceFill(['owner_id' => $owner['user']->id])->save();

            return [
                'owner' => $owner['user'],
                'invite' => $owner['invitation_pending'],
            ];
        });

        $invitationStatus = $result['invite']
            ? $ownerAccounts->sendInvitation($result['owner'])
            : null;
        $invitationSent = $invitationStatus === Password::RESET_LINK_SENT;

        return response()->json([
            'message' => $result['invite']
                ? ($invitationSent
                    ? 'School owner account assigned. A password setup invitation has been sent.'
                    : 'School owner account assigned, but the invitation could not be sent. Use Resend invitation to try again.')
                : 'School owner account assigned successfully.',
            'data' => $school->fresh(['district', 'type', 'owner']),
            'owner_invitation' => $invitationStatus,
        ]);
    }

    public function resendSchoolOwnerInvitation(Request $request, int $id, SchoolOwnerAccountService $ownerAccounts): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.schools.edit_any');
        $school = School::query()->with('owner')->findOrFail($id);
        $owner = $school->owner;

        abort_unless($owner && $owner->user_type === 'school_owner', 422, 'This school does not have a valid school-owner account.');
        abort_unless((bool) $owner->owner_invitation_pending, 422, 'This owner account has already completed password setup.');

        $status = $ownerAccounts->sendInvitation($owner);

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => $status === Password::RESET_THROTTLED
                    ? 'An invitation was sent recently. Wait one minute before sending another.'
                    : 'The invitation could not be sent. Check the email delivery settings and try again.',
            ], $status === Password::RESET_THROTTLED ? 429 : 422);
        }

        return response()->json(['message' => 'A new password setup invitation has been sent to '.$owner->email.'.']);
    }

    public function setOwnerPassword(Request $request, int $id): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.users.manage');
        $owner = User::query()->findOrFail($id);
        abort_unless($owner->user_type === 'school_owner', 404, 'School-owner account not found.');

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed', 'max:128'],
        ]);

        $owner->forceFill([
            'password' => Hash::make($data['password']),
            'owner_invitation_pending' => false,
            'status' => $owner->status === 'inactive' && $owner->owner_invitation_pending ? 'active' : $owner->status,
        ])->save();

        // Force the owner to sign in again with the new credentials on every device.
        $owner->tokens()->delete();

        return response()->json([
            'message' => 'The school-owner password has been updated. Existing sessions have been signed out.',
        ]);
    }

    public function resendOwnerInvitation(Request $request, int $id, SchoolOwnerAccountService $ownerAccounts): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.users.manage');
        $owner = User::query()->findOrFail($id);
        abort_unless($owner->user_type === 'school_owner', 404, 'School-owner account not found.');
        abort_unless((bool) $owner->owner_invitation_pending, 422, 'This owner has already completed password setup.');

        $status = $ownerAccounts->sendInvitation($owner);
        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => $status === Password::RESET_THROTTLED
                    ? 'An invitation was sent recently. Wait one minute before sending another.'
                    : 'The invitation could not be sent. Check email delivery settings and try again.',
            ], $status === Password::RESET_THROTTLED ? 429 : 422);
        }

        return response()->json(['message' => 'A new password setup invitation has been sent to '.$owner->email.'.']);
    }

    public function update(Request $request, string $resource, int $id): JsonResponse
    {
        if (in_array($resource, ['schools', 'verification'], true)) {
            $fields = array_keys($request->all());
            $permissions = [];

            $editableFields = [
                'name', 'tagline', 'description', 'address', 'phone', 'email', 'website',
                'whatsapp', 'boarding_type', 'gender_policy', 'type_id', 'district_id',
                'sub_county_id', 'student_count_approx', 'founded_year', 'registration_number',
                'ownership', 'religious_affiliation', 'town', 'po_box', 'nearest_landmark',
                'maps_url', 'facebook_url', 'instagram_url', 'fees_from', 'fees_to', 'activities',
                'custom_fields', 'admissions_open', 'admission_classes', 'admission_deadline',
                'application_fee', 'admissions_contact', 'admission_requirements', 'how_to_apply',
                'rejection_reason',
            ];
            if (array_intersect($fields, $editableFields) !== []) {
                $permissions[] = 'admin.schools.edit_any';
            }
            if (array_intersect($fields, ['verification_status']) !== [] ||
                in_array($request->input('status'), ['pending', 'active', 'rejected'], true)) {
                $permissions[] = 'admin.schools.approve';
            }
            if ($request->input('status') === 'suspended') {
                $permissions[] = 'admin.schools.suspend';
            }
            if (array_intersect($fields, ['is_featured', 'featured_until']) !== []) {
                $permissions[] = 'admin.schools.feature';
            }
            if ($permissions === []) {
                $permissions[] = 'admin.schools.edit_any';
            }
            foreach (array_unique($permissions) as $permission) {
                $this->authorizeWorkspacePermission($request, $permission);
            }
        } else {
            $permission = match ($resource) {
                'owners', 'parents' => 'admin.users.manage',
                'enquiries' => 'admin.enquiries.reply',
                'reviews' => 'admin.reviews.moderate',
                'articles', 'jobs' => 'admin.content.publish',
                'events' => 'admin.content.events',
                'scholarships' => 'admin.content.scholarships',
                'school-news' => 'admin.content.approve',
                'advertisements' => 'admin.ads.manage',
                'settings' => 'admin.system.edit_site_settings',
                'plans' => 'admin.billing.edit_plans',
                'contacts' => 'admin.enquiries.reply',
                default => 'admin.schools.view',
            };
            $this->authorizeWorkspacePermission($request, $permission);
        }
        switch ($resource) {
            case 'schools':
            case 'verification':
                $school = School::query()->findOrFail($id);
                $data = $request->validate([
                    'name' => ['sometimes', 'string', 'max:255'],
                    'tagline' => ['sometimes', 'nullable', 'string', 'max:255'],
                    'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
                    'address' => ['sometimes', 'nullable', 'string', 'max:255'],
                    'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
                    'email' => ['sometimes', 'nullable', 'email', 'max:255'],
                    'website' => ['sometimes', 'nullable', 'url', 'max:255'],
                    'whatsapp' => ['sometimes', 'nullable', 'string', 'max:30'],
                    'registration_number' => ['sometimes', 'nullable', 'string', 'max:60'],
                    'ownership' => ['sometimes', 'nullable', 'string', 'max:100'],
                    'religious_affiliation' => ['sometimes', 'nullable', 'string', 'max:100'],
                    'town' => ['sometimes', 'nullable', 'string', 'max:120'],
                    'po_box' => ['sometimes', 'nullable', 'string', 'max:120'],
                    'nearest_landmark' => ['sometimes', 'nullable', 'string', 'max:255'],
                    'maps_url' => ['sometimes', 'nullable', 'url', 'max:500'],
                    'facebook_url' => ['sometimes', 'nullable', 'url', 'max:500'],
                    'instagram_url' => ['sometimes', 'nullable', 'url', 'max:500'],
                    'fees_from' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                    'fees_to' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                    'activities' => ['sometimes', 'nullable', 'string', 'max:5000'],
                    'custom_fields' => ['sometimes', 'nullable', 'array'],
                    'admissions_open' => ['sometimes', 'boolean'],
                    'admission_classes' => ['sometimes', 'nullable', 'string', 'max:1000'],
                    'admission_deadline' => ['sometimes', 'nullable', 'date'],
                    'application_fee' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                    'admissions_contact' => ['sometimes', 'nullable', 'string', 'max:255'],
                    'admission_requirements' => ['sometimes', 'nullable', 'string', 'max:10000'],
                    'how_to_apply' => ['sometimes', 'nullable', 'string', 'max:10000'],
                    'boarding_type' => ['sometimes', 'in:boarding,day,mixed'],
                    'gender_policy' => ['sometimes', 'in:boys,girls,mixed'],
                    'type_id' => ['sometimes', 'integer', 'exists:school_types,id'],
                    'district_id' => ['sometimes', 'integer', 'exists:districts,id'],
                    'sub_county_id' => ['sometimes', 'nullable', 'integer', 'exists:sub_counties,id'],
                    'student_count_approx' => ['sometimes', 'nullable', 'integer', 'min:0'],
                    'founded_year' => ['sometimes', 'nullable', 'integer', 'min:1800', 'max:'.date('Y')],
                    'status' => ['sometimes', 'in:draft,pending,active,suspended,rejected'],
                    'verification_status' => ['sometimes', 'in:unverified,pending,verified'],
                    'is_featured' => ['sometimes', 'boolean'],
                    'featured_until' => ['sometimes', 'nullable', 'date'],
                    'rejection_reason' => ['sometimes', 'nullable', 'string', 'max:2000'],
                ]);

                if (($data['verification_status'] ?? null) === 'verified') {
                    $data['verified_at'] = now();
                    $data['verified_by'] = $request->user()->id;
                    $data['rejection_reason'] = null;
                }
                if (($data['status'] ?? null) === 'rejected') {
                    $data['verification_status'] = 'unverified';
                    $data['verified_at'] = null;
                    $data['verified_by'] = null;
                }
                $school->forceFill($data)->save();
                return response()->json(['data' => $school->fresh(['district', 'type', 'owner'])]);

            case 'owners':
            case 'parents':
                $user = User::query()->findOrFail($id);
                $isOwnerResource = $resource === 'owners';
                $rules = [
                    'status' => ['sometimes', 'in:active,inactive,suspended,banned'],
                    'account_type' => ['sometimes', 'in:parent,school_owner,student'],
                ];
                if ($isOwnerResource) {
                    $rules += [
                        'name' => ['sometimes', 'string', 'min:2', 'max:255'],
                        'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,'.$user->id],
                        'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
                        'school_role' => ['sometimes', 'nullable', 'string', 'max:80'],
                    ];
                }
                $data = $request->validate($rules);
                abort_unless($data !== [], 422);

                if ($user->is($request->user()) && (($data['status'] ?? 'active') !== 'active' || isset($data['account_type']))) {
                    throw ValidationException::withMessages(['status' => ['You cannot disable or downgrade your own administrator account.']]);
                }
                if ($user->hasRole('admin') && ($data['status'] ?? 'active') !== 'active') {
                    $activeAdmins = User::query()->role('admin')->where('status', 'active')->count();
                    if ($activeAdmins <= 1) {
                        throw ValidationException::withMessages(['status' => ['At least one active administrator must remain.']]);
                    }
                }
                if (isset($data['account_type'])) {
                    if ($user->hasRole('admin')) {
                        throw ValidationException::withMessages(['account_type' => ['Administrator accounts cannot be changed here.']]);
                    }
                    Role::findOrCreate($data['account_type'], 'web');
                    $user->forceFill(['user_type' => $data['account_type']])->save();
                    $user->syncRoles([$data['account_type']]);
                    unset($data['account_type']);
                }
                if ($data !== []) {
                    $user->forceFill($data)->save();
                }
                return response()->json(['data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'school_role' => $user->school_role,
                    'user_type' => $user->user_type,
                    'status' => $user->status,
                    'owner_invitation_pending' => (bool) $user->owner_invitation_pending,
                ]]);

            case 'enquiries':
                $inquiry = Inquiry::query()->findOrFail($id);
                $data = $request->validate([
                    'status' => ['sometimes', 'in:new,read,responded,archived'],
                    'owner_response' => ['sometimes', 'nullable', 'string', 'max:5000'],
                ]);
                if (array_key_exists('owner_response', $data) && filled($data['owner_response'])) {
                    $data['status'] = 'responded';
                    $data['responded_at'] = now();
                    $data['responded_by'] = $request->user()->id;
                }
                $inquiry->forceFill($data)->save();
                return response()->json(['data' => $inquiry->fresh(['school'])]);

            case 'reviews':
                $review = Review::query()->findOrFail($id);
                $data = $request->validate([
                    'status' => ['required', 'in:pending,approved,rejected,flagged'],
                ]);
                $review->forceFill([
                    'status' => $data['status'],
                    'moderated_by' => $request->user()->id,
                    'moderated_at' => now(),
                ])->save();
                $review->school?->updateRatingCache();
                return response()->json(['data' => $review->fresh(['school', 'user'])]);

            case 'jobs':
                $job = JobListing::query()->findOrFail($id);
                $data = $request->validate([
                    'title' => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
                    'employer' => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
                    'category' => ['sometimes', 'required', 'in:teaching,administration,higher,ngo,support,other'],
                    'location' => ['sometimes', 'required', 'string', 'min:2', 'max:180'],
                    'employment_type' => ['sometimes', 'required', 'in:full_time,part_time,temporary,contract,internship,volunteer'],
                    'excerpt' => ['sometimes', 'nullable', 'string', 'max:600'],
                    'description' => ['sometimes', 'required', 'string', 'max:50000'],
                    'salary' => ['sometimes', 'nullable', 'string', 'max:180'],
                    'deadline' => ['sometimes', 'nullable', 'date'],
                    'application_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
                    'application_email' => ['sometimes', 'nullable', 'email', 'max:255'],
                    'application_instructions' => ['sometimes', 'nullable', 'string', 'max:10000'],
                    'status' => ['sometimes', 'required', 'in:draft,published,closed,archived'],
                    'is_featured' => ['sometimes', 'boolean'],
                ]);
                abort_unless($data !== [], 422);

                if (isset($data['title']) && $data['title'] !== $job->title) {
                    $data['slug'] = $this->uniqueJobSlug($data['title'], $job->id);
                }
                if (($data['status'] ?? null) === 'published' && ! $job->published_at) {
                    $data['published_at'] = now();
                } elseif (($data['status'] ?? null) === 'draft') {
                    $data['published_at'] = null;
                }

                $job->forceFill($data)->save();

                return response()->json(['data' => $job->fresh()]);

            case 'articles':
                $article = Article::query()->findOrFail($id);
                $data = $request->validate([
                    'title' => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
                    'content_type' => ['sometimes', 'in:news,blog,article'],
                    'excerpt' => ['sometimes', 'nullable', 'string', 'max:2000'],
                    'body' => ['sometimes', 'required', 'string', 'max:500000'],
                    'category_id' => ['sometimes', 'nullable', 'integer', 'exists:article_categories,id'],
                    'cover_image' => ['sometimes', 'nullable', 'url', 'max:2048'],
                    'status' => ['sometimes', 'in:draft,published,archived'],
                    'published_at' => ['sometimes', 'nullable', 'date'],
                    'is_featured' => ['sometimes', 'boolean'],
                    'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
                    'meta_description' => ['sometimes', 'nullable', 'string', 'max:2000'],
                ]);
                abort_unless($data !== [], 422);
                if (($data['status'] ?? null) === 'draft') {
                    $data['published_at'] = null;
                } elseif (($data['status'] ?? null) === 'published'
                    && (! array_key_exists('published_at', $data) || ! $data['published_at'])) {
                    $data['published_at'] = now();
                }
                if (array_key_exists('body', $data)) {
                    $data['read_time_minutes'] = max(1, (int) ceil(str_word_count(strip_tags($data['body'])) / 200));
                }
                $article->forceFill($data)->save();
                return response()->json(['data' => $article->fresh(['category', 'author'])]);

            case 'events':
            case 'scholarships':
            case 'school-news':
                $postType = match ($resource) {
                    'events' => 'event',
                    'scholarships' => 'bursary',
                    default => 'news',
                };
                $post = SchoolPost::query()->where('type', $postType)->findOrFail($id);
                $data = $request->validate([
                    'status' => ['required', 'in:draft,pending,approved,published,rejected'],
                ]);

                if (in_array($data['status'], ['approved', 'published'], true) && ! $post->published_at) {
                    $data['published_at'] = now();
                }

                $post->forceFill($data)->save();
                return response()->json(['data' => $post->fresh(['school'])]);

            case 'advertisements':
                $advertisement = DB::table('school_advertisements')->where('id', $id)->first();
                abort_unless($advertisement, 404);
                $data = $request->validate([
                    'status' => ['sometimes', 'in:draft,pending,active,rejected,expired'],
                    'placement' => ['sometimes', 'in:school_spotlight,homepage_banner,search_feature'],
                    'destination_url' => ['sometimes', 'nullable', 'url', 'max:500'],
                    'description' => ['sometimes', 'string', 'max:5000'],
                    'image_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
                    'image_alt' => ['sometimes', 'nullable', 'string', 'max:255'],
                    'cta_label' => ['sometimes', 'nullable', 'string', 'max:80'],
                    'starts_at' => ['sometimes', 'nullable', 'date'],
                    'ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
                ]);
                abort_unless($data !== [], 422);
                if (($data['status'] ?? null) === 'active' && empty($data['starts_at']) && empty($advertisement->starts_at)) {
                    $data['starts_at'] = now();
                }
                DB::table('school_advertisements')->where('id', $id)->update($data + ['updated_at' => now()]);
                return response()->json(['message' => 'Advertisement updated.']);

            case 'settings':
                $data = $request->validate(['value' => ['present', 'nullable', 'string', 'max:20000']]);
                $updated = DB::table('settings')->where('id', $id)->update([
                    'value' => $data['value'],
                    'updated_at' => now(),
                ]);
                abort_unless($updated > 0, 404);
                return response()->json(['message' => 'Setting saved.']);

            case 'plans':
                $data = $request->validate([
                    'price' => ['sometimes', 'numeric', 'min:0'],
                    'is_active' => ['sometimes', 'boolean'],
                    'order' => ['sometimes', 'integer', 'min:0'],
                    'tagline' => ['sometimes', 'nullable', 'string', 'max:255'],
                    'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
                ]);
                abort_unless($data !== [], 422);
                $data['updated_at'] = now();
                DB::table('plans')->where('id', $id)->update($data);
                return response()->json(['message' => 'Plan updated.']);

            case 'contacts':
                $data = $request->validate(['status' => ['required', 'in:new,read,responded,archived']]);
                $data['handled_by'] = $request->user()->id;
                $data['handled_at'] = now();
                DB::table('contact_messages')->where('id', $id)->update($data + ['updated_at' => now()]);
                return response()->json(['message' => 'Contact message updated.']);

            default:
                return response()->json(['message' => 'This section is read-only or unavailable.'], 422);
        }
    }

    public function storeAdvertisement(Request $request): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.ads.manage');
        $data = $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:180'],
            'placement' => ['required', 'in:school_spotlight,homepage_banner,search_feature'],
            'destination_url' => ['nullable', 'url', 'max:500'],
            'description' => ['required', 'string', 'max:5000'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $id = DB::table('school_advertisements')->insertGetId([
            ...$data,
            'school_id' => null,
            'created_by' => $request->user()->id,
            'is_platform_ad' => true,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'data' => DB::table('school_advertisements')->where('id', $id)->first(),
            'message' => 'Website advertisement saved as a draft.',
        ], 201);
    }

    public function storeArticle(Request $request): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.content.publish');
        $data = $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'body' => ['required', 'string', 'max:500000'],
            'category_id' => ['nullable', 'integer', 'exists:article_categories,id'],
            'cover_image' => ['nullable', 'url', 'max:2048'],
            'content_type' => ['sometimes', 'in:news,blog,article'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['sometimes', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:2000'],
        ]);

        $slugBase = Str::slug($data['title']);
        $slug = $slugBase;
        $suffix = 2;
        while (Article::query()->where('slug', $slug)->exists()) {
            $slug = $slugBase.'-'.$suffix++;
        }

        $article = Article::query()->create([
            ...$data,
            'slug' => $slug,
            'content_type' => $data['content_type'] ?? 'blog',
            'author_id' => $request->user()->id,
            'published_at' => $data['status'] === 'draft'
                ? null
                : (($data['published_at'] ?? null) ?: now()),
            'read_time_minutes' => max(1, (int) ceil(str_word_count(strip_tags($data['body'])) / 200)),
        ]);

        return response()->json(['data' => $article->load(['category', 'author'])], 201);
    }

    public function storeJob(Request $request): JsonResponse
    {
        $this->authorizeWorkspacePermission($request, 'admin.content.publish');

        $data = $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'employer' => ['required', 'string', 'min:2', 'max:255'],
            'category' => ['required', 'in:teaching,administration,higher,ngo,support,other'],
            'location' => ['required', 'string', 'min:2', 'max:180'],
            'employment_type' => ['required', 'in:full_time,part_time,temporary,contract,internship,volunteer'],
            'excerpt' => ['nullable', 'string', 'max:600'],
            'description' => ['required', 'string', 'max:50000'],
            'salary' => ['nullable', 'string', 'max:180'],
            'deadline' => ['nullable', 'date'],
            'application_url' => ['nullable', 'url', 'max:2048'],
            'application_email' => ['nullable', 'email', 'max:255'],
            'application_instructions' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', 'in:draft,published'],
            'is_featured' => ['sometimes', 'boolean'],
        ]);

        if (blank($data['application_url'] ?? null)
            && blank($data['application_email'] ?? null)
            && blank($data['application_instructions'] ?? null)) {
            throw ValidationException::withMessages([
                'application_url' => ['Add an application website, email address or application instructions before saving this job.'],
            ]);
        }

        $job = JobListing::query()->create([
            ...$data,
            'slug' => $this->uniqueJobSlug($data['title']),
            'created_by' => $request->user()->id,
            'published_at' => $data['status'] === 'published' ? now() : null,
            'is_featured' => (bool) ($data['is_featured'] ?? false),
        ]);

        return response()->json([
            'data' => $job,
            'message' => $data['status'] === 'published'
                ? 'Job published successfully.'
                : 'Job saved as a draft.',
        ], 201);
    }

    private function uniqueJobSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'education-job';
        $slug = $base;
        $suffix = 2;

        while (JobListing::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function authorizeWorkspacePermission(Request $request, string $permission): void
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('admin') || $user->can($permission)), 403, 'Your administrator role does not have permission to view or change this section.');
    }

}
