<?php
// app/Http/Controllers/FamilyController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function updatePayment(Request $request)
    {
        // TODO: Implement ToyyibPay payment update logic
        return response()->json(['message' => 'Payment update received.']);
    }
}