<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\PaymentFlow;

class SimulateToyyibpayStatusCheck extends Command
{
    protected $signature = 'simulate:toyyibpay {bill_code}';
    protected $description = 'Simulate ToyyibPay status check using bill code';

    public function handle()
    {
        $billCode = $this->argument('bill_code');

        $this->info("Fetching status for BillCode: {$billCode}");

        $response = Http::asForm()->post('https://toyyibpay.com/index.php/api/getBillTransactions', [
            'billCode' => $billCode
        ]);

        if (!$response->successful()) {
            $this->error('Failed to fetch data from ToyyibPay');
            $this->line($response->body());
            return 1;
        }

        $transactions = $response->json();

        if (empty($transactions)) {
            $this->warn('No transactions found.');
            return 0;
        }

        $latest = $transactions[0];
        $statusCode = $latest['billpaymentStatus'] ?? '0';
        $statusText = [
            '1' => 'Success',
            '2' => 'Pending',
            '3' => 'Failed',
            '4' => 'Pending'
        ][$statusCode] ?? 'Unknown';

        $this->line("Status: $statusText");
        $this->line("Invoice No: " . ($latest['billpaymentInvoiceNo'] ?? '-'));
        $this->line("Email: " . ($latest['billEmail'] ?? '-'));
        $this->line("Phone: " . ($latest['billPhone'] ?? '-'));
        $this->line("Amount (RM): " . ($latest['billpaymentAmount'] ?? '0.00'));
        $this->line("Family ID: " . ($latest['billExternalReferenceNo'] ?? '-'));

        // Optional: update your DB for testing
        $flow = PaymentFlow::where('bill_code', $billCode)->latest()->first();

        if ($flow) {
            $flow->update([
                'transaction_id' => $latest['billpaymentInvoiceNo'],
                'status' => $statusCode == '1' ? 'paid' : 'cancelled',
                'paid_at' => $statusCode == '1' ? now() : null,
                'cancelled_at' => $statusCode != '1' ? now() : null,
            ]);

            $this->info('✅ PaymentFlow updated.');
        } else {
            $this->warn('⚠️ No PaymentFlow entry found for this bill_code.');
        }

        return 0;
    }
}