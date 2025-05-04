<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use App\Models\Family;
use App\Models\WebhookLog;
use App\Models\PaymentFlow;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PaymentSuccessController extends Controller
{
    // GET /payment-return (ToyyibPay will redirect users here)
    public function handleToyyibPayReturn(Request $request)
    {
        $transactionId = $request->query('transaction_id');
        $status        = strtolower($request->query('status'));
        $familyId      = $request->query('order_id');
        $billCode      = $request->query('billcode');

        if (!$transactionId || !$status || !$familyId) {
            Log::warning('Missing transaction_id, status, or family_id in ToyyibPay return', [
                'ip'     => $request->ip(),
                'agent'  => $request->userAgent(),
                'params' => $request->all(),
            ]);
            return Redirect::to('/')->with('error', 'Maklumat transaksi tidak lengkap.');
        }

        // Update payment_flows (use fallback if webhook failed)
        $flow = PaymentFlow::where('family_id', $familyId)
            ->whereNull('transaction_id')
            ->latest()
            ->first();

        if ($flow) {
            $flow->update([
                'transaction_id' => $transactionId,
                'bill_code'      => $billCode,
                'status'         => $status === 'success' ? 'paid' : 'cancelled',
                'paid_at'        => $status === 'success' ? now() : null,
                'cancelled_at'   => $status !== 'success' ? now() : null,
            ]);
        }

        // Try to fetch webhook log
        $log = WebhookLog::where('transaction_id', $transactionId)->latest()->first();

        if (!$log) {
            Log::alert('WebhookLog missing (possibly delayed ToyyibPay callback)', [
                'transaction_id' => $transactionId,
                'status'         => $status,
            ]);

            // Fallback to summary view for retry or display
            if ($flow) {
                return Redirect::route('payment.summary', ['familyId' => $familyId]);
            }

            return Redirect::to('/')->with('error', 'Transaksi tidak dapat disahkan.');
        }

        // Payment failed even with webhook received
        if (strtolower($log->status) !== 'success') {
            return Redirect::route('payment.summary', ['familyId' => $log->family_id]);
        }

        // ✅ Redirect to payment success
        return Redirect::route('payment.success', ['familyId' => $log->family_id]);
    }

    // redirect to payment.success and redirect to payment.summary if not success
    public function show($familyId)
    {
        $family = Family::where('family_id', $familyId)->firstOrFail();

        $webhook = WebhookLog::where('family_id', $familyId)
            ->where('status', 'Success')
            ->latest()
            ->first();

        if (!$webhook) {
            return redirect()->route('payment.summary', ['familyId' => $familyId]);
        }

        return view('payment.payment_success', [
            'family' => $family,
            'transactionId' => $webhook->transaction_id ?? '-',
            'amount' => $webhook->amount ?? 0,
        ]);
    }

    public function summary($familyId)
    {
        $family = Family::where('family_id', $familyId)->firstOrFail();

        $flow = PaymentFlow::where('family_id', $familyId)
            ->whereNotNull('transaction_id')
            ->latest()
            ->first();

        return view('payment.summary', [
            'family' => $family,
            'flow' => $flow,
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
