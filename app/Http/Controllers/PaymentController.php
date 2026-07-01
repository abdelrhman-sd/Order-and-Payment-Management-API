<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::build(data: Payment::all());
    }

    public function show(Payment $payment): JsonResponse
    {
        return ApiResponse::build(data: $payment->load('order'));
    }
}
