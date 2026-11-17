<?php

namespace App\Http\Controllers;

use App\Models\School;

class SavedSchoolController extends Controller
{
    public function toggle(School $school)
    {
        auth()->user()->savedSchools()->toggle($school->id);
        return back();
    }
}
