<?php

namespace App\Http\Controllers;

use App\Models\School;
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

        $school->reviews()->create([
            ...$data,
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your review has been submitted and will appear after moderation.');
    }
}
