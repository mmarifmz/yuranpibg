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
        Log::info('ToyyibPay Webhook Payload:', $payload);

        $billCode = $payload['billcode'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;
        $statusId = $payload['status_id'] ?? null;
        $familyId = $payload['order_id'] ?? null;
        $isPaid = $statusId === '1';

        if (!$billCode || !$transactionId || !$familyId) {
            Log::warning('Webhook missing key data.', compact('billCode', 'transactionId', 'familyId'));
            return response()->json(['error' => 'Invalid webhook payload'], 400);
        }

        // Update payment_flows
        $flow = PaymentFlow::where('bill_code', $billCode)->latest()->first();

        if ($flow) {
            $flow->fill([
                'transaction_id' => $transactionId,
                'status' => $isPaid ? 'paid' : 'cancelled',
                'paid_at' => $isPaid ? now() : null,
                'cancelled_at' => !$isPaid ? now() : null,
                'bill_amount' => $flow->bill_amount ?? 10000,
                'bill_to' => $flow->bill_to ?? 'Nama tidak ditemui',
            ])->save();

            Log::info("✅ PaymentFlow updated for $billCode");
        } else {
            Log::warning("❌ PaymentFlow not found for BillCode: $billCode");
        }

        // Update families table
        if ($isPaid) {
            $family = Family::where('family_id', $familyId)->first();
            if ($family) {
                $family->update([
                    'payment_status' => 'paid',
                    'payment_reference' => $transactionId,
                    'amount_paid' => ($flow->bill_amount ?? 10000) / 100,
                    'paid_at' => now(),
                ]);
                Log::info("✅ Family table updated for $familyId");
            } else {
                Log::warning("❌ Family not found for ID: $familyId");
            }
        }

        // Save raw webhook payload
        WebhookLog::create([
            'family_id' => $familyId,
            'transaction_id' => $transactionId,
            'status' => $statusId,
            'amount' => ($flow->bill_amount ?? 10000) / 100,
            'raw_payload' => json_encode($payload, JSON_PRETTY_PRINT),
        ]);

        return response()->json(['message' => 'Webhook processed']);
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

        $secretKey = config('services.toyyibpay.secret_key');
        $categoryCode = config('services.toyyibpay.category_code');
        
        $payload = [
            'userSecretKey' => $secretKey,
            'categoryCode' => $categoryCode,
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
