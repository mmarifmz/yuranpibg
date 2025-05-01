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
        $data = $request->all();

        // Step 1: Validate signature
        $receivedSignature = $data['signature'] ?? null;
        $secretKey = env('TOYYIBPAY_SECRET_KEY');
        $generatedSignature = sha1(
            $data['refno'] . '|' .
            $data['billcode'] . '|' .
            $data['amount'] . '|' .
            $secretKey
        );

        if ($receivedSignature !== $generatedSignature) {
            Log::warning('Invalid ToyyibPay signature received', $data);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Step 2: Status mapping
        $statusCode = $data['status'] ?? null;
        $statusText = match ($statusCode) {
            1 => 'Success',
            2 => 'Failed',
            3 => 'Pending',
            default => 'Unknown',
        };

        // Step 3: Log the raw payload
        WebhookLog::create([
            'family_id' => $data['billExternalReference'] ?? null,
            'transaction_id' => $data['refno'] ?? null,
            'status' => $statusText,
            'amount' => $data['amount'] ?? 0,
            'raw_payload' => json_encode($data),
        ]);

        // Step 4: Update payment status in families
        if (!empty($data['billExternalReference'])) {
            Family::where('family_id', $data['billExternalReference'])
                ->update([
                    'payment_status' => $statusText,
                ]);
        } else {
            Log::error('Missing family_id in webhook payload', $data);
        }

        return response()->json(['message' => 'Webhook processed'], 200);
    }
}
