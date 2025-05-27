<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentSuccessController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\PejabatDashboardController;

/*
|--------------------------------------------------------------------------
| Student Search & Payment Check Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [StudentController::class, 'showForm'])->name('search.form');
Route::post('/search', [StudentController::class, 'handleSearch'])->name('search.handle');
Route::get('/preview-more', [StudentController::class, 'loadMorePreview'])->name('search.preview.more');

/*
|--------------------------------------------------------------------------
| Payment Flow Routes
|--------------------------------------------------------------------------
*/
Route::get('/review-payment/{familyId}', [PaymentController::class, 'review'])->name('payment.review');
Route::post('/confirm-payment/{familyId}', [PaymentController::class, 'confirmPayment'])->name('confirm.payment');

// ToyyibPay - Server to Server Callback (backend verification)
Route::post('/payment-webhook', [PaymentWebhookController::class, 'handle'])->name('payment.webhook');

// ToyyibPay - Optional backend trigger (manual call)
Route::post('/payment-update', [FamilyController::class, 'updatePayment'])->name('payment.update');

// ToyyibPay - Redirect return after user pays
Route::get('/payment-return', [PaymentSuccessController::class, 'handleToyyibPayReturn'])->name('payment.return');

//  Success / Summary page after payment
Route::get('/payment-success/{familyId}', [PaymentSuccessController::class, 'show'])
    ->name('payment.success')
    ->middleware('throttle:10,1'); // prevent abuse
Route::get('/payment-summary/{familyId}', [PaymentSuccessController::class, 'summary'])->name('payment.summary');
Route::post('/payment-retry/{familyId}', [PaymentWebhookController::class, 'retry'])->name('payment.retry');

// Receipt PDF and public view
Route::get('/download-receipt/{familyId}', [PaymentSuccessController::class, 'downloadReceipt'])->name('download.receipt');

/*
|--------------------------------------------------------------------------
| Backoffice Views - Logs and Reports
|--------------------------------------------------------------------------
*/

Route::get('/pejabat/dashboard', [PejabatDashboardController::class, 'index'])->name('pejabat.dashboard');
Route::get('/pejabat/status', [PejabatDashboardController::class, 'statusPage'])->name('pejabat.status');
Route::get('/pejabat/status/{className}', [PejabatDashboardController::class, 'showClassStatus'])->name('pejabat.class.status');