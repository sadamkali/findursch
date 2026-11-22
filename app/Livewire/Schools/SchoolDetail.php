<?php

namespace App\Livewire\Schools;

use App\Models\School;
use App\Models\SchoolView;
use App\Models\SchoolPost;
use App\Models\Setting;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class SchoolDetail extends Component
{
    #[Locked]
    public School $school;

    public string $inquiryPhone = '';
    public string $inquiryStudentName = '';
    public string $inquiryStudentAge = '';
    public string $inquiryLevel = '';
    public string $inquiryIntake = '';
    public string $inquiryMessage = '';

    public string $reviewerType = 'parent';
    public int $reviewRating = 5;
    public string $reviewTitle = '';
    public string $reviewBody = '';

    public function mount(School $school): void
    {
        $this->school = School::query()
            ->publiclyVisible()
            ->whereKey($school->id)
            ->firstOrFail();

        $this->school->load([
            'type',
            'district.region',
            'county',
            'subCounty',
            'parish',
            'curricula',
            'programs' => fn ($query) => $query->where('is_active', true)->orderBy('name'),
            'programs.curriculum',
            'programs.fees' => fn ($query) => $query->where('is_active', true)->orderBy('amount'),
            'fees' => fn ($query) => $query->where('is_active', true)->orderBy('category')->orderBy('amount'),
            'facilities' => fn ($query) => $query->where('is_active', true)->orderBy('category')->orderBy('name'),
            'contacts',
            'approvedReviews' => fn ($query) => $query->latest()->with('user'),
            'media',
            'activeSubscription.plan',
        ]);

        if (auth()->check()) {
            $this->inquiryPhone = (string) (auth()->user()->phone ?? '');
        }

        SchoolView::create([
            'school_id' => $this->school->id,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'session_id' => session()->getId(),
            'referrer' => request()->header('referer'),
        ]);

        $this->school->incrementViews();
    }

    public function submitInquiry(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        if (! auth()->user()->hasVerifiedEmail()) {
            $this->redirectRoute('verification.notice');

            return;
        }

        abort_unless($this->school->canReceiveInquiries(), 404);

        $key = 'school-inquiry:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('inquiryMessage', 'You have sent several inquiries recently. Please try again later.');

            return;
        }

        $validated = $this->validate([
            'inquiryPhone' => ['required', 'string', 'max:30'],
            'inquiryStudentName' => ['nullable', 'string', 'max:255'],
            'inquiryStudentAge' => ['nullable', 'integer', 'min:1', 'max:100'],
            'inquiryLevel' => ['nullable', 'string', 'max:120'],
            'inquiryIntake' => ['nullable', 'string', 'max:120'],
            'inquiryMessage' => ['required', 'string', 'min:10', 'max:4800'],
        ]);

        RateLimiter::hit($key, 3600);

        $messageParts = [];

        if (! empty($validated['inquiryIntake'])) {
            $messageParts[] = 'Preferred intake: '.$validated['inquiryIntake'];
        }

        $messageParts[] = $validated['inquiryMessage'];

        $this->school->inquiries()->create([
            'user_id' => auth()->id(),
            'parent_name' => auth()->user()->name,
            'parent_email' => auth()->user()->email,
            'parent_phone' => $validated['inquiryPhone'] ?: auth()->user()->phone,
            'student_name' => $validated['inquiryStudentName'] ?: null,
            'student_age' => $validated['inquiryStudentAge'] ?: null,
            'intended_level' => $validated['inquiryLevel'] ?: null,
            'message' => mb_substr(implode("\n\n", $messageParts), 0, 5000),
            'source' => 'web',
        ]);

        $this->school->incrementInquiries();

        $this->reset(
            'inquiryPhone',
            'inquiryStudentName',
            'inquiryStudentAge',
            'inquiryLevel',
            'inquiryIntake',
            'inquiryMessage'
        );

        session()->flash('inquiry_sent', 'Your admission enquiry has been sent to the school.');
        $this->dispatch('inquiry-sent');
    }

    public function toggleSave()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        auth()->user()->savedSchools()->toggle($this->school->id);
    }

    public function isSaved(): bool
    {
        return auth()->check()
            && auth()->user()->savedSchools()->whereKey($this->school->id)->exists();
    }

    public function submitReview()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $validated = $this->validate([
            'reviewerType' => ['required', 'in:parent,student,alumni,staff,other'],
            'reviewRating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviewTitle' => ['required', 'string', 'max:255'],
            'reviewBody' => ['required', 'string', 'min:20', 'max:5000'],
        ]);

        if ($this->school->reviews()->where('user_id', auth()->id())->exists()) {
            $this->addError('reviewTitle', 'You have already submitted a review for this school.');

            return;
        }

        $requiresApproval = filter_var(Setting::get('reviews_require_approval', '1'), FILTER_VALIDATE_BOOLEAN);

        $this->school->reviews()->create([
            'user_id' => auth()->id(),
            'reviewer_type' => $validated['reviewerType'],
            'rating' => $validated['reviewRating'],
            'title' => $validated['reviewTitle'],
            'body' => $validated['reviewBody'],
            'status' => $requiresApproval ? 'pending' : 'approved',
            'moderated_at' => $requiresApproval ? null : now(),
        ]);

        $this->reset('reviewTitle', 'reviewBody');
        $this->reviewRating = 5;
        $this->reviewerType = 'parent';

        session()->flash('review_submitted', 'Your review has been submitted and is awaiting moderation.');
    }

    public function render()
    {
        $schoolPosts = SchoolPost::query()
            ->where('school_id', $this->school->id)
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->get();

        return view('livewire.schools.school-detail', [
            'alreadyReviewed' => auth()->check()
                && $this->school->reviews()->where('user_id', auth()->id())->exists(),
            'schoolEvents' => $schoolPosts->where('type', 'event')->sortBy('starts_at')->values(),
            'schoolNews' => $schoolPosts->where('type', 'news')->values(),
            'schoolBursaries' => $schoolPosts->where('type', 'bursary')->sortBy('deadline')->values(),
            'admissionsVisible' => in_array($this->school->activeSubscription?->plan?->slug, ['standard', 'premium'], true),
        ]);
    }
}
