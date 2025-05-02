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
            ->orderBy('family_id')
            ->get()
            ->groupBy('family_id')
            ->take(6); // Limit preview to 6 families

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
        $request->validate([
            'student_name' => 'required|string|max:255',
        ]);

        $studentName = strtoupper(trim($request->input('student_name')));
        $classFilter = $request->input('class_name');

        $query = Family::where('student_name', 'LIKE', "%{$studentName}%");

        if (!empty($classFilter)) {
            $query->where('class_name', $classFilter);
        }

        $families = $query->orderBy('family_id')->get()->groupBy('family_id');

        $classNames = Family::select('class_name')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        return view('student_search', [
            'families' => $families,
            'searched' => true,
            'student_name' => $studentName,
            'classNames' => $classNames,
            'selectedClass' => $classFilter,
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