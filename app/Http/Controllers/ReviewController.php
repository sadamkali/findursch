<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Setting;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, School $school)
    {
        abort_unless($school->canReceiveInquiries(), 404);

        $data = $request->validate([
            'reviewer_type' => ['required', 'in:parent,student,alumni,staff,other'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:20', 'max:5000'],
        ]);

        if ($school->reviews()->where('user_id', $request->user()->id)->exists()) {
            return back()->withErrors([
                'review' => 'You have already submitted a review for this school.',
            ])->withInput();
        }

        $requiresApproval = filter_var(Setting::get('reviews_require_approval', '1'), FILTER_VALIDATE_BOOLEAN);

        $school->reviews()->create([
            ...$data,
            'user_id' => $request->user()->id,
            'status' => $requiresApproval ? 'pending' : 'approved',
            'moderated_at' => $requiresApproval ? null : now(),
        ]);

        return back()->with('success', 'Your review has been submitted and will appear after moderation.');
    }
}
