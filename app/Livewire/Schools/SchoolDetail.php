<?php

namespace App\Livewire\Schools;

use App\Models\School;
use App\Models\SchoolView;
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
    public string $inquiryMessage = '';

    public string $reviewerType = 'parent';
    public int $reviewRating = 5;
    public string $reviewTitle = '';
    public string $reviewBody = '';

    public function mount(School $school): void
    {
        $this->school = School::query()
            ->active()
            ->verified()
            ->whereKey($school->id)
            ->firstOrFail();

        $this->school->load([
            'type', 'district.region', 'subCounty', 'curricula',
            'programs.curriculum', 'programs.fees', 'fees.program',
            'facilities', 'contacts', 'approvedReviews.user',
        ]);

        SchoolView::create([
            'school_id' => $this->school->id,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'session_id' => session()->getId(),
            'referrer' => request()->header('referer'),
        ]);

        $this->school->incrementViews();
    }

    public function submitInquiry()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        abort_unless($this->school->canReceiveInquiries(), 404);

        $key = 'school-inquiry:'.auth()->id();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('inquiryMessage', 'You have sent several inquiries recently. Please try again later.');

            return;
        }

        $validated = $this->validate([
            'inquiryPhone' => ['nullable', 'string', 'max:30'],
            'inquiryStudentName' => ['nullable', 'string', 'max:255'],
            'inquiryStudentAge' => ['nullable', 'integer', 'min:1', 'max:100'],
            'inquiryLevel' => ['nullable', 'string', 'max:120'],
            'inquiryMessage' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        RateLimiter::hit($key, 3600);

        $this->school->inquiries()->create([
            'user_id' => auth()->id(),
            'parent_name' => auth()->user()->name,
            'parent_email' => auth()->user()->email,
            'parent_phone' => $validated['inquiryPhone'] ?: auth()->user()->phone,
            'student_name' => $validated['inquiryStudentName'] ?: null,
            'student_age' => $validated['inquiryStudentAge'] ?: null,
            'intended_level' => $validated['inquiryLevel'] ?: null,
            'message' => $validated['inquiryMessage'],
            'source' => 'web',
        ]);

        $this->school->incrementInquiries();
        $this->reset('inquiryPhone', 'inquiryStudentName', 'inquiryStudentAge', 'inquiryLevel', 'inquiryMessage');

        session()->flash('inquiry_sent', 'Your inquiry has been sent to the school.');
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

        $this->school->reviews()->create([
            'user_id' => auth()->id(),
            'reviewer_type' => $validated['reviewerType'],
            'rating' => $validated['reviewRating'],
            'title' => $validated['reviewTitle'],
            'body' => $validated['reviewBody'],
            'status' => 'pending',
        ]);

        $this->reset('reviewTitle', 'reviewBody');
        $this->reviewRating = 5;
        $this->reviewerType = 'parent';

        session()->flash('review_submitted', 'Your review has been submitted and is awaiting moderation.');
    }

    public function render()
    {
        return view('livewire.schools.school-detail', [
            'alreadyReviewed' => auth()->check()
                && $this->school->reviews()->where('user_id', auth()->id())->exists(),
        ]);
    }
}
