<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'status',
    'gateway',
    'gateway_payment_id',
    'gateway_order_id',
    'payment_method',
    'idempotency_key',
    'amount',
    'currency',
    'metadata',
    'failed_reason',
    'paid_at'
])]
class Payment extends Model
{
    protected $table = 'payments';

    protected $casts = [
        'metadata' => 'array'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
