<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Family;

class StudentController extends Controller
{
    public function showForm()
    {
        $classNames = Family::select('class_name')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        // Lazy preview families with at least one pending status
        $families = Family::where('payment_status', 'pending')
            ->inRandomOrder()
            ->get()
            ->groupBy('family_id')
            ->take(9);

        return view('student_search', [
            'families' => $families ?? collect(),
            'searched' => false,
            'student_name' => '',
            'classNames' => $classNames,
            'selectedClass' => '', // ← Add this
        ]);
    }


    public function handleSearch(Request $request)
    {
        $studentName = $request->input('student_name');
        $className = $request->input('class_name');

        // Get all students that match the search
        $matchedStudents = Family::when($className, fn($q) => $q->where('class_name', $className))
            ->where('student_name', 'like', "%$studentName%")
            ->get();

        // Get unique family IDs from matches
        $matchedFamilyIds = $matchedStudents->pluck('family_id')->unique();

        // Load full family groups for each matched family_id
        $families = Family::whereIn('family_id', $matchedFamilyIds)
            ->orderBy('class_name')
            ->get()
            ->groupBy('family_id');

        return view('student_search', [
            'families' => $families,
            'searched' => true,
            'student_name' => $studentName,
            'classNames' => Family::select('class_name')->distinct()->orderBy('class_name')->pluck('class_name'),
            'selectedClass' => $className,
        ]);
    }

    public function loadMorePreview()
    {
        $classNames = Family::select('class_name')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        $families = Family::where('payment_status', 'pending')
            ->orderBy('family_id')
            ->get()
            ->groupBy('family_id');

        return view('student_search', [
            'classNames' => $classNames,
            'families' => $families,
            'searched' => false,
            'lazyPreview' => false,
        ]);
    }
}