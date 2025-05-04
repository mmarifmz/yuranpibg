<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PaymentFlow extends Model
{   
    protected $table = 'payment_flow';

    protected $fillable = [
        'family_id',
        'status',
        'initiated_at',
        'redirected_at',
        'cancelled_at',
        'paid_at',
        'transaction_id',
        'bill_code',
        'ip',
        'user_agent',
        'bill_email',
        'bill_phone',
    ];

    public function updateFromWebhook($transactionId, $billCode, $status)
    {
        $isPaid = $status === 'success';

        $this->update([
            'transaction_id' => $transactionId,
            'bill_code'      => $billCode,
            'status'         => $isPaid ? 'paid' : 'cancelled',
            'paid_at'        => $isPaid ? now() : null,
            'cancelled_at'   => $isPaid ? null : now(),
        ]);
    }
}