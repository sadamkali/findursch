<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function store(Request $request, School $school)
    {
        $data = $request->validate([
            'parent_name' => 'required|max:255',
            'parent_email' => 'required|email',
            'parent_phone' => 'nullable|max:30',
            'student_name' => 'nullable|max:255',
            'student_age' => 'nullable|integer',
            'intended_level' => 'nullable|max:120',
            'message' => 'required|min:10',
        ]);

        $data['user_id'] = auth()->id();
        $data['source'] = 'web';
        $school->inquiries()->create($data);
        $school->incrementInquiries();

        return back()->with('success', 'Your inquiry has been sent to the school.');
    }
}
