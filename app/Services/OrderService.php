<?php

namespace App\Services;

use App\Http\Requests\Order\CreateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{

    private static function getTax(): int
    {
        return env('TAX', 0);
    }

    public static function create(CreateOrderRequest $request): Order
    {
        DB::beginTransaction();

        Product::decreaseProductStock($request->input('products', []));

        $order = ($request->user)->orders()
            ->create(
                $request->validated() + [
                    'subtotal'  => $request->getSubtotal(),
                    'tax'       => self::getTax(),
                    'total'     => $request->getSubtotal() + self::getTax()
                ]
            );

        DB::commit();

        return $order;
    }
}
