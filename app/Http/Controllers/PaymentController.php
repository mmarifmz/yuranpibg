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
        $students = Family::where('family_id', $familyId)->get();

        // Sort descending by class level (assuming class format like "6 ANGGERIK")
        $students = $students->sortByDesc(function ($student) {
            return (int) filter_var($student->class_name, FILTER_SANITIZE_NUMBER_INT);
        });

        $eldestStudent = $students->first();
        $studentDisplay = Str::title(Str::lower($eldestStudent->student_name));

        $billDescription = 'Bayaran PIBG 2025/2026 untuk: ' . $studentDisplay;

        $billAmount = 100 + ($request->donation_amount ?? 0);

        $billCode = Str::random(10);
        $callbackUrl = route('payment.webhook'); // 👈 backend webhook for ToyyibPay to confirm payment

        if (app()->environment('local')) {
            $billAmount = 1; // Always RM 1 in local
        } else {
            $billAmount = 100 + ($request->donation_amount ?? 0);
        }

        // ToyyibPay params
        $payload = [
            'userSecretKey' => env('TOYYIBPAY_SECRET_KEY'),
            'categoryCode' => env('TOYYIBPAY_CATEGORY_CODE'),
            'billName' => 'Bayaran Yuran PIBG 2025 / 2026',
            'billDescription' => $billDescription,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $billAmount * 100, // in cents
            'billReturnUrl' => route('payment.return'), // Smart redirect handler
            'billCallbackUrl' => $callbackUrl,
            'billExternalReferenceNo' => $familyId,
            'billTo' => $studentDisplay,
            'billEmail' => $request->input('email'),
            'billPhone' => $request->input('phone'),
            'billSplitPayment' => 0,
            'billPaymentChannel' => 0,
            'billDisplayMerchant' => 1
        ];

        // Track when user gets redirected to ToyyibPay
        PaymentFlow::where('family_id', $familyId)
            ->where('status', 'initiated')
            ->latest()
            ->first()
            ?->update([
                'status' => 'redirected',
                'redirected_at' => now(),
                'bill_email' => $request->input('email'),
                'bill_phone' => $request->input('phone'),
            ]);

        // debug payload
        Log::info('ToyyibPay payload', $payload);
        Log::debug("Generated billDescription: [$billDescription]");

        $response = Http::asForm()->post('https://toyyibpay.com/index.php/api/createBill', $payload);

        \Log::error('ToyyibPay Error Response:', [
            'body' => $response->body(),
            'status' => $response->status()
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            $billCode = $data[0]['BillCode'] ?? null;
            if ($billCode) {

                PaymentFlow::create([
                    'family_id'   => $familyId,
                    'status'      => 'initiated',
                    'created_at'  => now(),
                    'bill_code'   => $billCode,
                    'bill_email'  => $request->input('email'),
                    'bill_phone'  => $request->input('phone'),
                    'bill_amount' => 10000, // amount in sen
                    'bill_to'     => $studentDisplay,
                    'ip'          => $request->ip(),
                    'user_agent'  => $request->userAgent(),
                ]);

                return redirect("https://toyyibpay.com/{$billCode}");
            }
        }

        return back()->withErrors(['msg' => 'Gagal menjana bil. Sila cuba lagi.']);
    }
}