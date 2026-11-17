<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\School;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, School $school)
    {
        $data = $request->validate([
            'reviewer_type' => 'required|in:parent,student,alumni,staff,other',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|max:255',
            'body' => 'required|min:20',
        ]);

        $data['school_id'] = $school->id;
        $data['user_id'] = auth()->id();
        $data['status'] = 'pending';

        Review::create($data);

        return back()->with('success', 'Review submitted. It will appear after moderation.');
    }
}
