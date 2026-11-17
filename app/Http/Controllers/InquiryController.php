<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function store(Request $request, School $school)
    {
        abort_unless($school->canReceiveInquiries(), 404);

        $data = $request->validate([
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'student_name' => ['nullable', 'string', 'max:255'],
            'student_age' => ['nullable', 'integer', 'min:1', 'max:100'],
            'intended_level' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $school->inquiries()->create([
            ...$data,
            'user_id' => $request->user()->id,
            'parent_name' => $request->user()->name,
            'parent_email' => $request->user()->email,
            'source' => 'web',
        ]);

        $school->incrementInquiries();

        return back()->with('success', 'Your inquiry has been sent to the school.');
    }
}
