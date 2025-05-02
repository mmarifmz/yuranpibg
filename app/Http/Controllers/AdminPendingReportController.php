<?php
// app/Http/Controllers/AdminPendingReportController.php
namespace App\Http\Controllers;

use App\Models\Family;

class AdminPendingReportController extends Controller
{
    public function index()
    {
        $families = Family::where('payment_status', 'Pending')->paginate(20);
        return view('admin.reports.pending_parents', compact('families'));
    }
}