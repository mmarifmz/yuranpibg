<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $table = 'webhook_logs';

    protected $fillable = [
        'family_id',
        'transaction_id',
        'status',
        'amount',
        'raw_payload',
    ];
}