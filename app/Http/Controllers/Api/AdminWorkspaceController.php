<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Inquiry;
use App\Models\Review;
use App\Models\School;
use App\Models\SchoolPost;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminWorkspaceController extends Controller
{
    public function overview(): JsonResponse
    {
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
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        switch ($resource) {
            case 'schools':
            case 'verification':
                $query = School::query()->with([
                    'district:id,name',
                    'type:id,name',
                    'owner:id,name,email',
                ])->orderByDesc('created_at');

                if ($resource === 'verification') {
                    $query->where(function ($builder) {
                        $builder->where('verification_status', 'pending')
                            ->orWhereIn('status', ['draft', 'pending']);
                    });
                }
                if ($status) {
                    $query->where('status', $status);
                }
                if ($search !== '') {
                    $query->where(function ($builder) use ($search) {
                        $builder->where('name', 'ilike', '%'.$search.'%')
                            ->orWhere('email', 'ilike', '%'.$search.'%');
                    });
                }

                $items = $query->limit(100)->get();
                break;

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

                $items = $query->limit(100)->get(['id', 'name', 'email', 'phone', 'user_type', 'status', 'email_verified_at', 'created_at'])
                    ->map(fn (User $user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'user_type' => $user->user_type,
                        'status' => $user->status,
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

    public function update(Request $request, string $resource, int $id): JsonResponse
    {
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
                $data = $request->validate([
                    'status' => ['sometimes', 'in:active,inactive,suspended,banned'],
                    'account_type' => ['sometimes', 'in:parent,school_owner,student'],
                ]);
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
                    'id' => $user->id, 'name' => $user->name, 'email' => $user->email,
                    'user_type' => $user->user_type, 'status' => $user->status,
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

            case 'articles':
                $article = Article::query()->findOrFail($id);
                $data = $request->validate([
                    'status' => ['sometimes', 'in:draft,published,archived'],
                    'is_featured' => ['sometimes', 'boolean'],
                ]);
                if (($data['status'] ?? null) === 'published' && ! $article->published_at) {
                    $data['published_at'] = now();
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

    public function storeArticle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'body' => ['required', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:article_categories,id'],
            'cover_image' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:draft,published'],
            'is_featured' => ['sometimes', 'boolean'],
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
            'author_id' => $request->user()->id,
            'published_at' => $data['status'] === 'published' ? now() : null,
            'read_time_minutes' => max(1, (int) ceil(str_word_count(strip_tags($data['body'])) / 200)),
        ]);

        return response()->json(['data' => $article->load(['category', 'author'])], 201);
    }
}
