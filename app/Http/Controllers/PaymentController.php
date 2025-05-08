<?php

// app/Http/Controllers/PaymentController.php
namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\ReviewLog;
use App\Models\PaymentFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function reviewPayment($familyId)
    {
        $students = Family::where('family_id', $familyId)->get();
        $family = $students->firstOrFail(); // throws 404 if empty

        // Log intent
        $hasRecentInitiated = PaymentFlow::where('family_id', $familyId)
            ->where('status', 'initiated')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->exists();

        if (! $hasRecentInitiated) {
            PaymentFlow::create([
                'family_id' => $familyId,
                'status' => 'initiated',
                'initiated_at' => now(),
                'ip' => request()->ip(),
                'user_agent' => Str::limit(request()->userAgent(), 255),
            ]);
        }

        $recentLog = ReviewLog::where('family_id', $familyId)
            ->where('ip', request()->ip())
            ->where('created_at', '>=', now()->subHour())
            ->exists();

        if (! $recentLog) {
            ReviewLog::create([
                'family_id' => $familyId,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return view('payment.review', [
            'familyId' => $familyId,
            'students' => $students,
        ]);
    }

    public function confirmPayment(Request $request, $familyId)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required',
            'donation_amount' => 'nullable|numeric|min:0'
        ]);

        $family = Family::where('family_id', $familyId)->firstOrFail();
        $students = Family::getStudentsByFamilyId($familyId);

        $eldestStudent = $students->sortByDesc(fn($s) => (int) filter_var($s->class_name, FILTER_SANITIZE_NUMBER_INT))->first();
        $studentDisplay = Str::title(Str::lower($eldestStudent->student_name));
        $billDescription = 'Bayaran PIBG 2025/2026 untuk: ' . $studentDisplay;

        $students = Family::where('family_id', $familyId)->get();

        $baseAmount = $students->sortByDesc(function($s) {
            return intval(preg_replace('/\D/', '', $s->class_name)); // Extract year like 6 from '6 ANGGERIK'
        })->first()->amount_due ?? 0;
        $donationAmount = (float) ($request->donation_amount ?? 0);

        // Total bill in RM
        $billAmount = app()->environment('local') ? 1 : ($baseAmount + $donationAmount);

        $callbackUrl = route('payment.webhook');

        //production calling .env
        $secretKey = config('services.toyyibpay.secret_key');
        $categoryCode = config('services.toyyibpay.category_code');

        $payload = [
            'userSecretKey' => $secretKey,
            'categoryCode' => $categoryCode,
            'billName' => 'Bayaran Yuran PIBG 2025 / 2026',
            'billDescription' => $billDescription,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $billAmount * 100,
            'billReturnUrl' => route('payment.return'),
            'billCallbackUrl' => $callbackUrl,
            'billExternalReferenceNo' => $familyId,
            'billTo' => $studentDisplay,
            'billEmail' => $request->input('email'),
            'billPhone' => $request->input('phone'),
            'billSplitPayment' => 0,
            'billPaymentChannel' => 0,
            'billDisplayMerchant' => 1
        ];

        Log::info('ToyyibPay payload', $payload);
        Log::debug("Generated billDescription: [$billDescription]");

        $response = Http::asForm()->post('https://toyyibpay.com/index.php/api/createBill', $payload);

        if (!$response->successful()) {
            Log::error('ToyyibPay Error Response:', [
                'body' => $response->body(),
                'status' => $response->status()
            ]);
            return back()->withErrors(['msg' => 'Gagal menjana bil. Sila cuba lagi.']);
        }

        $data = $response->json();
        $billCode = $data[0]['BillCode'] ?? null;

        if (!$billCode) {
            return back()->withErrors(['msg' => 'ToyyibPay tidak mengembalikan BillCode.']);
        }

        // Try to find existing flow to reuse
        $existingFlow = PaymentFlow::where('family_id', $familyId)
            ->whereNull('transaction_id')
            ->latest()
            ->first();

        if ($existingFlow) {
            $existingFlow->update([
                'status' => 'redirected',
                'redirected_at' => now(),
                'bill_email' => $request->input('email'),
                'bill_phone' => $request->input('phone'),
                'bill_amount' => $billAmount * 100,
                'bill_to' => $studentDisplay,
                'bill_code' => $billCode,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } else {
            PaymentFlow::create([
                'family_id' => $familyId,
                'status' => 'initiated',
                'initiated_at' => now(),
                'bill_code' => $billCode,
                'bill_email' => $request->input('email'),
                'bill_phone' => $request->input('phone'),
                'bill_amount' => $billAmount * 100,
                'bill_to' => $studentDisplay,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return redirect("https://toyyibpay.com/{$billCode}");
    }

    public function review(Request $request, $familyId)
    {
        // Log the visit to review-payment page
        ReviewLog::create([
            'family_id' => $familyId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'visited_at' => now(),
        ]);

        // All student rows are in families table
        $students = Family::where('family_id', $familyId)->get();

        // Just take the first row as the family's main info (e.g., from eldest student)
        $family = $students->firstOrFail();

        // Reset any previous unfinished flows
        PaymentFlow::where('family_id', $familyId)
            ->whereIn('status', ['initiated', 'redirected'])
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

        return view('payment.review', compact('family', 'students', 'familyId'));
    }
}