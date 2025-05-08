<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\PaymentFlow;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use PDF;

class PaymentSuccessController extends Controller
{
    public function handleToyyibPayReturn(Request $request)
    {
        $query = $request->query();
        Log::debug('ToyyibPay RETURN params', $query);

        $statusId = $query['status_id'] ?? null;
        $billCode = $query['billcode'] ?? null;
        $transactionId = $query['transaction_id'] ?? null;
        $familyId = $query['order_id'] ?? null;
        $isPaid = $statusId === '1';

        if (!$billCode || !$transactionId || !$familyId) {
            Log::warning('Missing ToyyibPay return params', compact('statusId', 'billCode', 'transactionId', 'familyId'));
            return redirect()->route('payment.summary', ['familyId' => $familyId ?? 'unknown']);
        }

        $flow = PaymentFlow::where('bill_code', $billCode)->latest()->first();

        if (!$flow) {
            Log::warning('No matching payment flow found for returned bill_code', compact('billCode', 'transactionId', 'statusId'));
            return redirect()->route('payment.summary', ['familyId' => $familyId]);
        }

        // Update payment_flow record
        $flow->fill([
            'transaction_id' => $transactionId,
            'status' => $isPaid ? 'paid' : 'cancelled',
            'paid_at' => $isPaid ? now() : null,
            'cancelled_at' => !$isPaid ? now() : null,
            'bill_amount' => $flow->bill_amount ?? 10000,
            'bill_to' => $flow->bill_to ?? 'Nama tidak ditemui',
        ])->save();

        Log::info("✅ PaymentFlow updated via return handler for $billCode");

        // Update family record on success
        if ($isPaid) {
            $updated = Family::where('family_id', $familyId)->update([
                'payment_status' => 'paid',
                'payment_reference' => $transactionId,
                'amount_paid' => ($flow->bill_amount ?? 10000) / 100,
                'paid_at' => now(),
            ]);

            if ($updated) {
                Log::info("✅ Family table updated for ALL under ID: $familyId");
            } else {
                Log::warning("❌ No records updated for family ID: $familyId");
            }
        }

        return redirect()->route(
            $isPaid ? 'payment.success' : 'payment.summary',
            ['familyId' => $familyId]
        );
    }

    // Show success page
    public function show($familyId)
    {
        $family = Family::where('family_id', $familyId)->firstOrFail();
        $students = Family::getStudentsByFamilyId($familyId);
        $flow = PaymentFlow::where('family_id', $familyId)->where('status', 'paid')->latest()->first();

        // ✅ Prevent access if no paid flow found
        if (!$flow) {
            return redirect('/')->with('error', 'Transaksi tidak sah atau belum dibayar.');
        }

        return view('payment.success', [
            'family' => $family,
            'students' => $students,
            'flow' => $flow,
            'transactionId' => $flow->transaction_id,
            'amount' => $flow->bill_amount,
        ]);
    }

    // Show fallback summary if failed
    public function summary($familyId)
    {
        $family = Family::where('family_id', $familyId)->firstOrFail();
        $students = $family->students ?? collect();
        $flow = PaymentFlow::where('family_id', $familyId)->latest()->first();

        // ✅ If the latest flow is already paid, redirect to receipt
        if ($flow && $flow->status === 'paid') {
            return redirect()->route('payment.success', ['familyId' => $familyId]);
        }

        return view('payment.summary', compact('family', 'students', 'flow', 'familyId'));
    }

    // Downloadable PDF receipt
    public function downloadReceipt($familyId)
    {
        $family = Family::where('family_id', $familyId)->firstOrFail();
        $students = Family::getStudentsByFamilyId($familyId);
        $flow = PaymentFlow::where('family_id', $familyId)
            ->where('status', 'paid')
            ->latest()
            ->first();

        if (!$flow) {
            return redirect('/')->with('error', 'Transaksi tidak sah atau belum dibayar.');
        }

        // Get eldest student (assuming highest class_name)
        $eldestStudent = $students->sortByDesc(function ($student) {
            return (int) filter_var($student->class_name, FILTER_SANITIZE_NUMBER_INT);
        })->first();

        $studentName = $eldestStudent->student_name . ' (' . $eldestStudent->class_name . ')';
        $familyId = $family->family_id;

        $pdf = PDF::loadView('payment.receipt_pdf', [
            'family' => $family,
            'students' => $students,
            'transaction' => $flow,
            'studentName' => $studentName,
            'familyId' => $familyId
        ]);

        return $pdf->download("Resit-Yuran-PIBG-SSP-2025-{$familyId}.pdf");
    }
}
