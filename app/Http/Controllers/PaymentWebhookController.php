<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Family;
use App\Models\WebhookLog;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::debug('ToyyibPay Webhook received', $payload);

        // Validate required fields
        if (!isset($payload['billCode'], $payload['billpaymentStatus'], $payload['billpaymentAmount'], $payload['billpaymentInvoice'])) {
            Log::warning('Invalid webhook payload', $payload);
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $transactionId = $payload['billpaymentInvoice'];
        $amount = $payload['billpaymentAmount'] / 100; // convert from cents to RM
        $statusCode = $payload['billpaymentStatus'];
        $familyId = $payload['billExternalReferenceNo'];

        $statusMap = [
            1 => 'Success',
            2 => 'Failed',
            3 => 'Pending',
        ];

        $status = $statusMap[$statusCode] ?? 'Unknown';

        // Save webhook log
        WebhookLog::create([
            'family_id'      => $familyId,
            'transaction_id' => $transactionId,
            'status'         => $status,
            'amount'         => $amount,
            'raw_payload'    => json_encode($payload),
        ]);

        // Update family payment info if successful
        if (strtolower($status) === 'success') {
            Family::where('family_id', $familyId)->update([
                'payment_status'     => 'paid',
                'amount_paid'        => $amount,
                'payment_reference'  => $transactionId,
                'paid_at'            => now(),
            ]);

            Log::info("Payment updated for family $familyId", [
                'transaction_id' => $transactionId,
                'amount' => $amount,
            ]);
        } else {
            Log::notice("Payment not successful for family $familyId", [
                'status' => $status,
                'payload' => $payload,
            ]);
        }

        return response()->json(['message' => 'OK'], 200);
    }
}
