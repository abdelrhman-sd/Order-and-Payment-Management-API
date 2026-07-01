<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Requests\Payment\CreatePaymentRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use App\Services\Payment\Contracts\PaymentGateway;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Illuminate\Support\Str;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    public function __construct(private PaymentGateway $gateway) {}

    public function index(): JsonResponse
    {
        return ApiResponse::build(data: Payment::all());
    }

    public function initiate(CreatePaymentRequest $request, string $gateway): JsonResponse|RedirectResponse
    {
        $order = Order::find($request->order_id);

        if ($order->status !== OrderStatus::CONFIRMED->value) {
            throw new AccessDeniedException("Order #{$order->id} is not confirmed yet!");
        }

        if ($order->payments()->where('status', PaymentStatus::PAID->value)->exists()) {
            throw new AccessDeniedException('Order is already paid!');
        }

        /** @var Payment */
        $payment = $order->payments()->make(
            array_merge($request->validated(), [
                'amount'    => $order->total,
                'gateway'   => $gateway,
                'currency'  => $order->currency,
                'status'    => PaymentStatus::PENDING->value,
                'idempotency_key' => Str::uuid()->toString(),
            ])
        );

        $result = $this->gateway->initiatePayment($order, $payment);

        $payment->gateway_payment_id    = $result['gateway_payment_id'];
        $payment->gateway_order_id      = $result['gateway_order_id'];
        $payment->save();

        return ApiResponse::build(data: [
            'checkout_url'  => $result['checkout_url'],
            'payment'       => $payment
        ]);
    }

    public function returnFromGateway(Request $request): JsonResponse
    {
        return ApiResponse::build(data: [
            'payment' => $this->gateway->processGatewayWebhook($request->all())
        ]);
    }

    public function refund(Request $request, string $gateway, Payment $payment)
    {
        $request->validate(['amount' => 'required|integer']);

        if ($this->gateway->refund($payment, $request->amount)) {
            return ApiResponse::build('Refunded successfully.');
        }

        return ApiResponse::build('Refunded successfully.', status: Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
