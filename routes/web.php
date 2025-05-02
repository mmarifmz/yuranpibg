<?php

//use App\Livewire\StudentSearch;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FamilyController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\PaymentSuccessController;
use App\Http\Controllers\AdminWebhookLogController;
use App\Http\Controllers\AdminPendingReportController;

use App\Http\Controllers\StudentController;

// main landing page search form    
Route::get('/', [StudentController::class, 'showForm'])->name('search.form');
Route::post('/search', [StudentController::class, 'handleSearch'])->name('search.handle');

Route::get('/preview-more', [StudentController::class, 'loadMorePreview'])->name('search.preview.more');

// ToyyibPay Webhook Callback (server-to-server)
Route::post('/payment-webhook', [PaymentWebhookController::class, 'handle']);

// Payment Update after webhook or redirect (optional endpoint if you manually call it)
Route::post('/payment-update', [FamilyController::class, 'updatePayment']);

// Show the review/payment breakdown page
Route::get('/review-payment/{familyId}', [PaymentController::class, 'reviewPayment'])->name('review.payment');

// Handle final confirmation + create ToyyibPay bill
Route::post('/confirm-payment/{familyId}', [PaymentController::class, 'confirmPayment'])->name('confirm.payment');

// ToyyibPay RETURN URL: user redirect after paying
Route::get('/payment-return', [PaymentSuccessController::class, 'handleToyyibPayReturn'])->name('payment.return');

// User-facing thank you page after return
Route::get('/payment-success/{familyId}', [PaymentSuccessController::class, 'show'])->name('payment.success');

// Receipt PDF download
Route::get('/download-receipt/{familyId}', [PaymentSuccessController::class, 'downloadReceipt'])->name('download.receipt');

// Receipt Web View (clean public view)
Route::get('/resit/{familyId}', [PaymentSuccessController::class, 'webReceipt'])->name('receipt.web');

// Admin view - Webhook logs
Route::get('/admin/webhook-logs', [AdminWebhookLogController::class, 'index'])
    ->name('admin.webhook.logs');

// Admin view - Pending parent transaction
Route::get('/admin/reports/pending-parents', [AdminPendingReportController::class, 'index'])
    ->name('admin.reports.pending');
