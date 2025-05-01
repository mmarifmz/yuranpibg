<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use App\Models\Family;
use App\Models\WebhookLog;

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

        return Redirect::route('payment.success', $familyId);
    }

    // GET /payment-success/{familyId}
    public function show($familyId)
    {
        $family = Family::where('family_id', $familyId)->firstOrFail();

        return view('payment.success', [
            'family' => $family,
        ]);
    }
}
