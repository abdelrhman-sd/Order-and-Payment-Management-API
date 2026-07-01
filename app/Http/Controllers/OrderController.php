<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', Rule::in(OrderStatus::class)]
        ]);

        $orders = $request->has('status')
            ? Order::where('status', $request->status)->get()
            : Order::all();

        return ApiResponse::build(data: ['orders' => $orders]);
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        return ApiResponse::build(
            __('resource.created', ['resource' => 'Order']),
            OrderService::create($request)
        );
    }

    public function show(Order $order): JsonResponse
    {
        return ApiResponse::build(data: $order->load('payments'));
    }

    public function destroy(Order $order): JsonResponse
    {
        if ($order->payments()->get()->count() == 0) {

            $order->delete();

            return ApiResponse::build(
                __('resource.deleted', ['resource' => 'Order']),
                ['order' => $order]
            );
        }

        return ApiResponse::build(
            message: "Action forbidden, this order has payments",
            status: Response::HTTP_FORBIDDEN
        );
    }
}
