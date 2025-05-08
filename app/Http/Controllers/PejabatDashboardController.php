<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Family;

class PejabatDashboardController extends Controller
{
    public function index()
    {
        $families = Family::where('family_id', '!=', 'F999')
            ->select('family_id', 'payment_status', 'amount_paid')
            ->distinct('family_id')
            ->get();

        $familyCount = $families->count();
        $paidCount = $families->where('payment_status', 'paid')->count();
        $pendingCount = $families->where('payment_status', 'pending')->count();
        $totalCollected = $families->sum('amount_paid');
        $targetAmount = $familyCount * 100;

        $classBreakdown = Family::where('family_id', '!=', 'F999')
        ->selectRaw('class_name, COUNT(DISTINCT family_id) as total')
        ->selectRaw("COUNT(DISTINCT IF(payment_status = 'paid', family_id, NULL)) as paid")
        ->selectRaw("COUNT(DISTINCT IF(payment_status = 'pending', family_id, NULL)) as pending")
        ->groupBy('class_name')
        ->orderByRaw("CAST(SUBSTRING_INDEX(class_name, ' ', 1) AS UNSIGNED) DESC")
        ->orderBy('class_name')
        ->get();

    return view('pejabat.dashboard', compact(
        'familyCount',
        'paidCount',
        'pendingCount',
        'targetAmount',
        'totalCollected',
        'classBreakdown'
    ));
    }
}