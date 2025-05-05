<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\PaymentFlow;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redirect;

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
        $family = Family::where('family_id', $familyId)->firstOrFail();

        // Get last flow that contains usable email/phone
        $flow = PaymentFlow::where('family_id', $familyId)
            ->whereNotNull('bill_email')
            ->latest()
            ->firstOrFail();

        // Get students
        $students = $family->students;

        // Get eldest student by class name number OR null
        $eldestStudent = $students
            ? $students->sortByDesc(fn ($s) => (int) filter_var($s->class_name, FILTER_SANITIZE_NUMBER_INT))->first()
            : null;

        // Fallback name logic
        $studentName = $eldestStudent
            ? Str::title(Str::lower($eldestStudent->student_name))
            : 'Ibubapa ' . $family->family_id;

        $billDescription = 'Bayaran Semula PIBG 2025/2026 untuk: ' . $studentName;

        $payload = [
            'userSecretKey'           => env('TOYYIBPAY_SECRET_KEY'),
            'categoryCode'            => env('TOYYIBPAY_CATEGORY_CODE'),
            'billName'                => 'Ulangan Bayaran PIBG 2025 / 2026',
            'billDescription'         => $billDescription,
            'billPriceSetting'        => 1,
            'billPayorInfo'           => 1,
            'billAmount'              => 100 * 100, // in sen
            'billReturnUrl'           => route('payment.return'),
            'billCallbackUrl'         => route('payment.webhook'),
            'billExternalReferenceNo' => $familyId,
            'billTo'                  => $studentName,
            'billEmail'               => $flow->bill_email,
            'billPhone'               => $flow->bill_phone,
            'billSplitPayment'        => 0,
            'billPaymentChannel'      => 0,
            'billDisplayMerchant'     => 1,
        ];

        Log::info('Retry ToyyibPay Payload', $payload);

        $response = \Http::asForm()->post('https://toyyibpay.com/index.php/api/createBill', $payload);

        if ($response->successful()) {
            $data = $response->json();
            $billCode = $data[0]['BillCode'] ?? null;

            if ($billCode) {
                PaymentFlow::create([
                    'family_id'   => $familyId,
                    'status'      => 'initiated',
                    'created_at'  => now(),
                    'bill_code'   => $billCode,
                    'bill_email'  => $flow->bill_email,
                    'bill_phone'  => $flow->bill_phone,
                    'ip'          => $request->ip(),
                    'user_agent'  => $request->userAgent(),
                ]);

                return redirect("https://toyyibpay.com/{$billCode}");
            }
        }

        return back()->with('error', 'Tidak dapat mencipta bil baru.');
    }
}
