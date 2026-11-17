<?php

namespace App\Livewire\Schools;

use App\Models\Review;
use App\Models\School;
use App\Models\SchoolView;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SchoolDetail extends Component
{
    public School $school;

    public function mount(School $school)
    {
        $this->school = School::query()
            ->active()
            ->verified()
            ->whereKey($school->id)
            ->firstOrFail();

        $this->school->load(['type', 'district.region', 'curricula', 'programs.fees', 'facilities', 'contacts', 'approvedReviews.user']);


        // Track view
        SchoolView::create([
            'school_id' => $school->id,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'session_id' => session()->getId(),
            'referrer' => request()->header('referer'),
        ]);
    }

    public function submitInquiry(array $data)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        abort_unless($this->school->canReceiveInquiries(), 404);

        $key = 'school-inquiry:'.auth()->id();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('message', 'You have sent several inquiries recently. Please try again later.');

            return;
        }

        $validated = Validator::make($data, [
            'phone' => ['nullable', 'string', 'max:30'],
            'student_name' => ['nullable', 'string', 'max:255'],
            'student_age' => ['nullable', 'integer', 'min:1', 'max:100'],
            'intended_level' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ])->validate();

        RateLimiter::hit($key, 3600);

        $this->school->inquiries()->create([
            'user_id' => auth()->id(),
            'parent_name' => auth()->user()->name,
            'parent_email' => auth()->user()->email,
            'parent_phone' => $validated['phone'] ?? null,
            'student_name' => $validated['student_name'] ?? null,
            'student_age' => $validated['student_age'] ?? null,
            'intended_level' => $validated['intended_level'] ?? null,
            'message' => $validated['message'],
            'source' => 'web',
        ]);

        $this->school->incrementInquiries();
        session()->flash('inquiry_sent', 'Your inquiry has been sent. The school will contact you soon.');
    }

    public function toggleSave()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }
        auth()->user()->savedSchools()->toggle($this->school->id);
    }

    public function isSaved(): bool
    {
        return auth()->check() && auth()->user()->savedSchools()->where('school_id', $this->school->id)->exists();
    }

    public function submitReview(array $data)
    {
        if (! auth()->check()) return redirect()->route('login');

        $validated = Validator::make($data, [
            'reviewer_type' => ['required', 'in:parent,student,alumni,staff,other'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:20', 'max:5000'],
            'academic_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'facilities_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'staff_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'environment_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ])->validate();

        if ($this->school->reviews()->where('user_id', auth()->id())->exists()) {
            $this->addError('review', 'You have already submitted a review for this school.');

            return;
        }

        $this->school->reviews()->create([
            'user_id' => auth()->id(),
            'reviewer_type' => $validated['reviewer_type'],
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'body' => $validated['body'],
            'academic_rating' => $validated['academic_rating'] ?? null,
            'facilities_rating' => $validated['facilities_rating'] ?? null,
            'staff_rating' => $validated['staff_rating'] ?? null,
            'environment_rating' => $validated['environment_rating'] ?? null,
            'status' => 'pending',
        ]);

        session()->flash('review_submitted', 'Your review has been submitted and is pending approval.');
    }

    public function render()
    {
        return view('livewire.schools.school-detail');
    }
}
