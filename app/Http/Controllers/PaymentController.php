<?php

// app/Http/Controllers/PaymentController.php
namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\ReviewLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function reviewPayment($familyId)
    {
        $students = Family::where('family_id', $familyId)->get();

        if ($students->isEmpty()) {
            abort(404);
        }

        ReviewLog::create([
            'family_id' => $familyId,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

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
        $callbackUrl = route('payment.return');

        if (app()->environment('local')) {
            $billAmount = 1; // Always RM 1 in local
        } else {
            $billAmount = 100 + ($request->donation_amount ?? 0);
        }

        // ToyyibPay params
        $payload = [
            'userSecretKey' => env('TOYYIBPAY_SECRET_KEY'),
            'categoryCode' => env('TOYYIBPAY_CATEGORY_CODE'),
            'billName' => 'Sumbangan PIBG 2025 / 2026',
            'billDescription' => $billDescription,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $billAmount * 100, // in cents
            'billReturnUrl' => $callbackUrl,
            'billCallbackUrl' => route('payment.webhook'),
            'billExternalReferenceNo' => $familyId,
            'billTo' => $request->input('email'),
            'billEmail' => $request->input('email'),
            'billPhone' => $request->input('phone'),
            'billSplitPayment' => 0,
            'billPaymentChannel' => 0,
            'billDisplayMerchant' => 1
        ];

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
                return redirect("https://toyyibpay.com/{$billCode}");
            }
        }

        return back()->withErrors(['msg' => 'Gagal menjana bil. Sila cuba lagi.']);
    }
}