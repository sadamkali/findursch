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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OwnerWorkspaceController extends Controller
{
    public function workspace(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');
        $schoolsQuery = $isAdmin ? School::query() : $user->ownedSchools();
        $schools = $schoolsQuery
            ->with(['type', 'district', 'activeSubscription.plan'])
            ->withCount(['inquiries', 'approvedReviews'])
            ->latest()
            ->limit($isAdmin ? 500 : 100)
            ->get()
            ->map(fn (School $school) => [
                'id' => $school->id,
                'name' => $school->name,
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

    public function profile(Request $request, int $school): JsonResponse
    {
        $record = $this->school($request, $school);

        if ($request->isMethod('put') || $request->isMethod('patch')) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'min:2', 'max:255'],
                'tagline' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:10000'],
                'address' => ['nullable', 'string', 'max:1000'],
                'phone' => ['nullable', 'string', 'max:30'],
                'email' => ['nullable', 'email', 'max:255'],
                'website' => ['nullable', 'url', 'max:255'],
                'whatsapp' => ['nullable', 'string', 'max:30'],
            ]);

            $changes = $validated;

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

        $record->load(['type', 'district', 'subCounty']);

        return response()->json(['data' => ['school' => $record]]);
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

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'min:2', 'max:180'],
                'level' => ['nullable', 'string', 'max:60'],
                'duration' => ['nullable', 'string', 'max:60'],
                'description' => ['nullable', 'string', 'max:5000'],
                'curriculum_id' => ['nullable', 'integer', 'exists:curricula,id'],
            ]);

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

        if ($request->isMethod('post')) {
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
            ]);

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
        abort_unless(in_array($collection, ['cover', 'gallery'], true), 404);
        $record = $this->school($request, $school);

        if ($request->isMethod('post')) {
            $diskName = config('media-library.disk_name', 'public');
            $diskDriver = config('filesystems.disks.'.$diskName.'.driver');

            if ($diskDriver !== 's3') {
                return response()->json([
                    'message' => 'Permanent image storage is not configured. Set MEDIA_DISK to an S3-compatible disk before uploading school photos.',
                ], 503);
            }

            $request->validate([
                'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            ]);

            $media = $record->addMediaFromRequest('image')->toMediaCollection($collection);

            return response()->json([
                'data' => ['record' => ['id' => $media->id, 'name' => $media->name, 'url' => $media->getUrl()]],
            ], 201);
        }

        if ($request->isMethod('delete')) {
            abort_unless($mediaId, 404);
            $media = $record->media()->where('collection_name', $collection)->whereKey($mediaId)->firstOrFail();
            $media->delete();

            return response()->json(['message' => 'Image removed.']);
        }

        return response()->json([
            'data' => [
                'records' => $record->getMedia($collection)->map(fn ($media) => [
                    'id' => $media->id,
                    'title' => $media->name,
                    'url' => $media->getUrl(),
                ])->values(),
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
            ]);

            $created = SchoolAdvertisement::create([
                'school_id' => $record->id,
                'created_by' => $request->user()->id,
                'title' => $validated['title'],
                'placement' => $validated['placement'],
                'destination_url' => $validated['url'] ?? null,
                'description' => $validated['description'],
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
