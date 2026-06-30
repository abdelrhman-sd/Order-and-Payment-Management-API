<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable([
    'user_id',
    'status',
    'subtotal',
    'tax',
    'total',
    'delivery_address',
    'notes',
    'source_platform'
])]
class Order extends Pivot
{
    protected $table = 'orders';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
