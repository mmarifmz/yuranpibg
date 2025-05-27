<?php

// app/Http/Controllers/PejabatDashboardController.php

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

        $latestPayments = \DB::table('families')
            ->selectRaw('MAX(id) as id')
            ->where('payment_status', 'paid')
            ->groupBy('family_id');

        $latestFamilyPayments = \DB::table('families as f')
            ->joinSub($latestPayments, 'latest', fn($join) =>
                $join->on('f.id', '=', 'latest.id')
            )
            ->get();
        $yuranTotal = $latestFamilyPayments->sum(function ($f) {
            return min($f->amount_paid, 100);
        });

        $sumbanganTotal = $latestFamilyPayments->sum(function ($f) {
            return max($f->amount_paid - 100, 0);
        });

        $sumbanganFamilyCount = \App\Models\Family::where('payment_status', 'paid')
            ->where('amount_paid', '>', 100)
            ->distinct('family_id')
            ->count('family_id');

        $classBreakdown = Family::where('family_id', '!=', 'F999')
            ->selectRaw('class_name, COUNT(DISTINCT family_id) as total')
            ->selectRaw("COUNT(DISTINCT IF(payment_status = 'paid', family_id, NULL)) as paid")
            ->selectRaw("COUNT(DISTINCT IF(payment_status = 'pending', family_id, NULL)) as pending")
            ->groupBy('class_name')
            ->orderByRaw("CAST(SUBSTRING_INDEX(class_name, ' ', 1) AS UNSIGNED) DESC")
            ->orderBy('class_name')
            ->get();

        $dailyCollections = \DB::table('families as f')
            ->joinSub(
                \DB::table('families')
                    ->selectRaw('MAX(id) as id')
                    ->whereNotNull('paid_at')
                    ->where('payment_status', 'paid')
                    ->groupByRaw('family_id, DATE(paid_at)'),
                'latest',
                function ($join) {
                    $join->on('f.id', '=', 'latest.id');
                }
            )
            ->selectRaw('DATE(f.paid_at) as date, SUM(f.amount_paid) as total, COUNT(f.family_id) as families')
            ->groupByRaw('DATE(f.paid_at)')
            ->orderByRaw('DATE(f.paid_at)')
            ->get();

        $chartDates = $dailyCollections->map(function ($row) {
            $localDate = \Carbon\Carbon::parse($row->date)
                ->timezone('Asia/Kuala_Lumpur'); // convert from UTC to GMT+8

            $dayName = $localDate->locale('ms')->isoFormat('dddd'); // e.g., "Isnin"

            return $localDate->toDateString() . ' (' . ucfirst($dayName) . ')';
        });
        $chartDates = $chartDates->values();
        $chartAmounts = $dailyCollections->pluck('total');
        $chartFamilies = $dailyCollections->pluck('families');

    return view('pejabat.dashboard', compact(
        'familyCount',
        'paidCount',
        'pendingCount',
        'targetAmount',
        'totalCollected',
        'classBreakdown',
        'chartDates',
        'chartAmounts',
        'chartFamilies',
        'yuranTotal',
        'sumbanganTotal',
        'sumbanganFamilyCount'
    ));
    }

    // Show class list
    public function statusPage()
    {
        $classes = \App\Models\Family::select('class_name')
            ->where('family_id', '!=', 'F999')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        $greenCount = 0;
        $yellowCount = 0;
        $redCount = 0;

        $classStats = $classes->mapWithKeys(function ($class) use (&$greenCount, &$yellowCount, &$redCount) {
            $students = \App\Models\Family::where('class_name', $class)
                ->where('family_id', '!=', 'F999')
                ->get();

            $total = $students->count();
            $paid = $students->where('payment_status', 'paid')->count();
            $percent = $total > 0 ? round(($paid / $total) * 100) : 0;

            if ($percent >= 80) {
                $greenCount++;
            } elseif ($percent >= 50) {
                $yellowCount++;
            } else {
                $redCount++;
            }

            return [$class => [
                'percent' => $percent,
                'paid' => $paid,
                'total' => $total,
            ]];
        });

        return view('pejabat.status', [
            'classes' => $classes,
            'classStats' => $classStats,
            'greenCount' => $greenCount,
            'yellowCount' => $yellowCount,
            'redCount' => $redCount,
        ]);

        return view('pejabat.status', [
            'classes' => $classes,
            'classStats' => $classStats,
        ]);
    }

    // Show student payment status for a specific class
    public function showClassStatus($className)
    {
        $students = \App\Models\Family::where('class_name', $className)
        ->where('family_id', '!=', 'F999')  // 👈 Exclude F999
        ->get();

        $paid = $students->where('payment_status', 'paid')->sortBy('student_name');
        $pending = $students->where('payment_status', 'pending')->sortBy('student_name');

        return view('pejabat.status_single', [
            'class_name' => $className,
            'paid' => $paid,
            'pending' => $pending,
        ]);
    }
}