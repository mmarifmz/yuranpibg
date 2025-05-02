<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Family;

class StudentSearchController extends Controller
{
    public function showSearchForm()
    {
        return view('student_search');
    }

    public function handleSearch(Request $request)
    {
        $request->validate([
            'student_name' => 'required|string|min:2',
        ]);

        $searchTerm = strtoupper(trim($request->input('student_name')));

        $students = Family::where('student_name', 'LIKE', "%$searchTerm%")
            ->orderBy('class_name')
            ->get();

        return view('student_search', [
            'student_name' => $searchTerm,
            'students' => $students
        ]);
    }
}
