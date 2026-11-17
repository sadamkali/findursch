<?php

namespace App\Livewire\Schools;

use App\Models\Review;
use App\Models\School;
use App\Models\SchoolView;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SchoolDetail extends Component
{
    public School $school;

    public function mount(School $school)
    {
        $this->school = $school;
        $this->school->load(['type', 'district.region', 'curricula', 'programs.fees', 'facilities', 'contacts', 'approvedReviews.user', 'activeSubscription.plan']);

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

        $this->school->inquiries()->create([
            'user_id' => auth()->id(),
            'parent_name' => auth()->user()->name,
            'parent_email' => auth()->user()->email,
            'parent_phone' => $data['phone'] ?? null,
            'student_name' => $data['student_name'] ?? null,
            'student_age' => $data['student_age'] ?? null,
            'intended_level' => $data['intended_level'] ?? null,
            'message' => $data['message'],
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

        Review::create([
            'school_id' => $this->school->id,
            'user_id' => auth()->id(),
            'reviewer_type' => $data['reviewer_type'],
            'rating' => $data['rating'],
            'title' => $data['title'],
            'body' => $data['body'],
            'academic_rating' => $data['academic_rating'] ?? null,
            'facilities_rating' => $data['facilities_rating'] ?? null,
            'staff_rating' => $data['staff_rating'] ?? null,
            'environment_rating' => $data['environment_rating'] ?? null,
            'status' => 'pending',
        ]);

        session()->flash('review_submitted', 'Your review has been submitted and is pending approval.');
    }

    public function render()
    {
        return view('livewire.schools.school-detail');
    }
}
