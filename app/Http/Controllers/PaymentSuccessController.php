<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use App\Models\Family;
use App\Models\WebhookLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PaymentSuccessController extends Controller
{
    // GET /payment-return (ToyyibPay will redirect users here)
    public function handleToyyibPayReturn(Request $request)
    {
        $transactionId = $request->query('transaction_id');

        if (!$transactionId) {
            Log::warning('Missing transaction_id on payment-return', [
                'ip' => $request->ip(),
                'agent' => $request->userAgent(),
            ]);
            return Redirect::to('/')->with('error', 'Maklumat transaksi tidak lengkap.');
        }

        $log = WebhookLog::where('transaction_id', $transactionId)->latest()->first();

        if (!$log) {
            Log::alert('Unmatched transaction_id return detected', [
                'transaction_id' => $transactionId,
            ]);
            return Redirect::to('/')->with('error', 'Resit tidak dijumpai.');
        }

        $familyId = $log->family_id;
        $status = strtolower($log->status);

        if ($status !== 'success') {
            return Redirect::to('/')->with('error', 'Pembayaran tidak berjaya.');
        }

        if (!$log) {
            Log::warning('Skipping log check (dev fallback)', [
                'transaction_id' => $transactionId,
            ]);

            return redirect()->route('payment.success', ['familyId' => 'F050']);
        }

        return Redirect::route('payment.success', $familyId);
    }

    // GET /payment-success/{familyId}
    public function show($familyId)
    {
        $family = Family::where('family_id', $familyId)->firstOrFail();
        $students = Student::where('family_id', $familyId)->get();

        // get from latest successful webhook
        $webhook = WebhookLog::where('family_id', $familyId)
            ->where('status', 'Success')
            ->latest()
            ->first();

        return view('payment.payment_success', [
            'family' => $family,
            'students' => $students,
            'transactionId' => $webhook->transaction_id ?? '-',
            'amount' => $webhook->amount ?? 0,
        ]);
    }

    // resit web version
    public function webReceipt($familyId)
    {
        $record = Family::where('family_id', $familyId)->first();

        if (!$record || strtolower($record->payment_status) !== 'paid') {
            return redirect()->route('review.payment', $familyId)
                ->with('error', 'Resit hanya tersedia selepas bayaran disahkan.');
        }

        return view('payment.resit', [
            'familyId'    => $record->family_id,
            'studentName' => $record->student_name,
            'amountPaid'  => $record->amount_paid,
            'paymentRef'  => $record->payment_reference,
            'paidAt'      => $record->paid_at ? \Carbon\Carbon::parse($record->paid_at) : null,
        ]);
    }


    // PDF resit version
    public function downloadReceipt($familyId)
    {
        $students = Family::where('family_id', $familyId)->get();
        $studentName = $students->pluck('student_name')->first();
        $transaction = WebhookLog::where('family_id', $familyId)->latest()->first();

        $pdf = Pdf::loadView('payment.receipt_pdf', [
            'studentName' => $studentName,
            'familyId' => $familyId,
            'transaction' => $transaction
        ]);

        return $pdf->download("resit_{$familyId}.pdf");
    }
}
