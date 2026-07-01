<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'quantity', 'price'])]
class Product extends Model
{
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function decreaseProductStock(array $products): void
    {
        $productIds = collect($products)->pluck('id');

        Product::whereIn('id', $productIds)->lockForUpdate();

        $cases = collect($products)->map(function (array $product): string {
            return "WHEN {$product['id']} THEN stock - {$product['quantity']}";
        })->implode(' ');

        DB::table('products')
            ->whereIn('id', $productIds)
            ->update(['stock' => DB::raw("CASE id {$cases} END")]);
    }
}
