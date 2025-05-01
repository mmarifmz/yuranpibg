<?php

// app/Http/Controllers/PaymentController.php
namespace App\Http\Controllers;

use App\Models\Family;
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

        $billAmount = 100 + ($request->donation_amount ?? 0);

        $billCode = Str::random(10);
        $callbackUrl = route('payment.return');

        // ToyyibPay params
        $payload = [
            'userSecretKey' => env('TOYYIBPAY_SECRET_KEY'),
            'categoryCode' => env('TOYYIBPAY_CATEGORY_CODE'),
            'billName' => 'Yuran PIBG ' . now()->year,
            'billDescription' => 'Bayaran Yuran PIBG untuk keluarga ' . $familyId,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $billAmount * 100, // in cents
            'billReturnUrl' => $callbackUrl,
            'billCallbackUrl' => route('payment.update'),
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
            if ($billCode) {
                return redirect("https://toyyibpay.com/{$billCode}");
            }
        }

        return back()->withErrors(['msg' => 'Gagal menjana bil. Sila cuba lagi.']);
    }
}