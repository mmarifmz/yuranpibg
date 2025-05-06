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
    // Handle ToyyibPay return

    public function handleToyyibPayReturn(Request $request)
    {
        $transactionId = $request->query('transaction_id');
        $statusParam   = $request->query('status') ?? $request->query('status_id');
        $status        = strtolower((string) $statusParam);
        $familyId      = $request->query('order_id');
        $billCode      = $request->query('billcode');
        $amount        = $request->query('amount') ?? 0;

        if (!$transactionId || !$status || !$familyId || !$billCode) {
            Log::warning('Missing transaction_id, status, family_id or billcode in ToyyibPay return', [
                'ip'     => $request->ip(),
                'agent'  => $request->userAgent(),
                'params' => $request->all(),
            ]);
            return redirect('/')->with('error', 'Maklumat transaksi tidak lengkap.');
        }

        Log::debug('ToyyibPay RETURN params', $request->query());

        $flow = PaymentFlow::where('bill_code', $billCode)->latest()->first();

        if (!$flow) {
            Log::warning('No matching payment flow found for returned bill_code', [
                'bill_code'      => $billCode,
                'transaction_id' => $transactionId,
                'status'         => $status,
            ]);
            return redirect()->route('payment.summary', ['familyId' => $familyId]);
        }

        $isPaid = $status === 'success' || $status === '1';

        $flow->update([
            'transaction_id' => $transactionId,
            'status'         => $isPaid ? 'paid' : 'cancelled',
            'paid_at'        => $isPaid ? now() : null,
            'cancelled_at'   => !$isPaid ? now() : null,
            'bill_amount'    => $flow->bill_amount ?? 10000, // fallback if needed
    'bill_to'        => $flow->bill_to ?? 'Nama tidak ditemui',
        ]);

        // Save into webhook_logs table (updated for correct schema)
        WebhookLog::create([
            'family_id'      => $familyId,
            'transaction_id' => $transactionId,
            'status'         => $status,
            'amount'         => $amount / 100, // Convert from sen to RM if needed
            'raw_payload'    => json_encode($request->query(), JSON_PRETTY_PRINT),
        ]);

        return $isPaid
            ? redirect()->route('payment.success', ['familyId' => $familyId])
            : redirect()->route('payment.summary', ['familyId' => $familyId]);
    } 

    // Show success page
    public function show($familyId)
    {
        $family = Family::where('family_id', $familyId)->firstOrFail();
        $students = $family->students ?? collect();
        $flow = PaymentFlow::where('family_id', $familyId)->where('status', 'paid')->latest()->first();

        return view('payment.success', compact('family', 'students', 'flow'));
    }

    // Show fallback summary if failed
    public function summary($familyId)
    {
        $family = Family::where('family_id', $familyId)->firstOrFail();
        $students = $family->students ?? collect();
        $flow = PaymentFlow::where('family_id', $familyId)->latest()->first();

        return view('payment.summary', compact('family', 'students', 'flow', 'familyId'));
    }

    // Web version of receipt
    public function webreceipt($familyId)
    {
        $family = Family::where('family_id', $familyId)->firstOrFail();
        $flow = PaymentFlow::where('family_id', $familyId)->where('status', 'paid')->latest()->first();

        return view('payment.receipt', compact('family', 'flow'));
    }

    // Downloadable PDF receipt
    public function downloadReceipt($familyId)
    {
        $family = Family::where('family_id', $familyId)->firstOrFail();
        $flow = PaymentFlow::where('family_id', $familyId)->where('status', 'paid')->latest()->first();

        $pdf = PDF::loadView('payment.receipt', compact('family', 'flow'));
        return $pdf->download("resit-{$familyId}.pdf");
    }
}
