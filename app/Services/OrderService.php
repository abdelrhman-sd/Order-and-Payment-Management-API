<?php

namespace App\Services;

use App\Http\Requests\Order\CreateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public static function create(CreateOrderRequest $request): Order
    {
        DB::beginTransaction();

        Product::decreaseProductStock($request->input('products', []));

        /** @var Order $order*/
        $order = ($request->user)->orders()->create(
            $request->validated() + [
                'tax'       => $tax = (int) env('PAYMENT_TAX', 0),
                'subtotal'  => $request->getSubtotal(),
                'total'     => $request->getSubtotal() + $tax
            ]
        );

        $order->products()->createMany(
            collect($request->input('products'))
                ->map(function (array $product): array {
                    return [
                        'product_id' => $product['id'],
                        'quantity'   => $product['quantity']
                    ];
                })
        );

        DB::commit();

        return $order;
    }
}
