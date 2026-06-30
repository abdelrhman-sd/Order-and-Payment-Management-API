<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function store(CreateOrderRequest $request): JsonResponse
    {
        OrderService::create($request);
        return ApiResponse::build(__('resource.created', ['resource' => 'Order']));
    }
}
