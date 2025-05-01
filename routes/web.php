<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentSuccessController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\AdminWebhookLogController;
use App\Http\Controllers\AdminPendingReportController;
use App\Http\Controllers\FamilyController;

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
Route::get('/review-payment/{familyId}', [PaymentController::class, 'reviewPayment'])->name('review.payment');
Route::post('/confirm-payment/{familyId}', [PaymentController::class, 'confirmPayment'])->name('confirm.payment');

// ToyyibPay - Server to Server Callback (backend verification)
Route::post('/payment-webhook', [PaymentWebhookController::class, 'handle'])->name('payment.webhook');

// ToyyibPay - Optional backend trigger (manual call)
Route::post('/payment-update', [FamilyController::class, 'updatePayment'])->name('payment.update');

// ToyyibPay - Redirect return after user pays
Route::get('/payment-return', [PaymentSuccessController::class, 'handleToyyibPayReturn'])->name('payment.return');

// Final success page after payment
Route::get('/payment-success/{familyId}', [PaymentSuccessController::class, 'show'])->name('payment.success');

// Receipt PDF and public view
Route::get('/download-receipt/{familyId}', [PaymentSuccessController::class, 'downloadReceipt'])->name('download.receipt');
Route::get('/resit/{familyId}', [PaymentSuccessController::class, 'webReceipt'])->name('receipt.web');

/*
|--------------------------------------------------------------------------
| Admin Views - Logs and Reports
|--------------------------------------------------------------------------
*/
Route::get('/admin/webhook-logs', [AdminWebhookLogController::class, 'index'])->name('admin.webhook.logs');
Route::get('/admin/reports/pending-parents', [AdminPendingReportController::class, 'index'])->name('admin.reports.pending');