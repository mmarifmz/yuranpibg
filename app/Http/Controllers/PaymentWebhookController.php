<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Family;
use App\Models\WebhookLog;
use App\Models\PaymentFlow;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('ToyyibPay Webhook Received', $payload);

        $transactionId = $payload['transaction_id'] ?? null;
        $billCode      = $payload['billcode'] ?? null;
        $familyId      = $payload['order_id'] ?? null;
        $amount        = $payload['amount'] ?? null;
        $status        = strtolower($payload['status'] ?? '');

        if (!$transactionId || !$billCode || !$familyId) {
            Log::warning('Webhook missing required fields', $payload);
            return response('Missing fields', 400);
        }

        // Save Webhook Log
        WebhookLog::create([
            'family_id'      => $familyId,
            'transaction_id' => $transactionId,
            'status'         => $status,
            'amount'         => $amount,
            'raw_payload'    => json_encode($payload),
        ]);

        // Update Payment Flow
        $payment = PaymentFlow::where('family_id', $familyId)
            ->where('transaction_id', $transactionId)
            ->orWhereNull('transaction_id')
            ->latest()
            ->first();

        if ($payment) {
            $payment->updateFromWebhook($transactionId, $billCode, $status);
        }

        return response('OK', 200);
    }

    public function retry(Request $request, $familyId)
    {
        $family = \App\Models\Family::where('family_id', $familyId)->firstOrFail();
        $students = \App\Models\Family::where('family_id', $familyId)->get();

        $eldestStudent = $students->sortByDesc(function ($student) {
            return (int) filter_var($student->class_name, FILTER_SANITIZE_NUMBER_INT);
        })->first();

        $studentDisplay = Str::title(Str::lower($eldestStudent->student_name));

        $billDescription = 'Bayaran Semula PIBG 2025/2026 untuk: ' . $studentDisplay;
        $billAmount = 100; // Or calculate dynamically if needed
        $billCode = Str::random(10);

        $payload = [
            'userSecretKey' => env('TOYYIBPAY_SECRET_KEY'),
            'categoryCode' => env('TOYYIBPAY_CATEGORY_CODE'),
            'billName' => 'Ulangan Bayaran PIBG 2025 / 2026',
            'billDescription' => $billDescription,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $billAmount * 100,
            'billReturnUrl' => route('payment.return'),
            'billCallbackUrl' => route('payment.webhook'),
            'billExternalReferenceNo' => $familyId,
            'billTo' => $request->input('email'),
            'billEmail' => $request->input('email'),
            'billPhone' => $request->input('phone'),
            'billSplitPayment' => 0,
            'billPaymentChannel' => 0,
            'billDisplayMerchant' => 1
        ];

        $response = Http::asForm()->post('https://toyyibpay.com/index.php/api/createBill', $payload);

        if ($response->successful()) {
            $data = $response->json();
            $billCode = $data[0]['BillCode'] ?? null;

            PaymentFlow::create([
                'family_id'   => $familyId,
                'status'      => 'initiated',
                'initiated_at'=> now(),
                'bill_code'   => $billCode,
                'ip'          => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'bill_email'  => $request->input('email'),
                'bill_phone'  => $request->input('phone'),
            ]);

            return redirect("https://toyyibpay.com/{$billCode}");
        }

        Log::error('Failed to regenerate bill for retry', [
            'family_id' => $familyId,
            'response' => $response->body(),
        ]);

        return back()->withErrors(['msg' => 'Gagal menjana semula bil. Sila cuba lagi.']);
    }
}
