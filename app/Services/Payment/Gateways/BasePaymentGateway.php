<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\Contracts\PaymentGateway;
use App\Models\Payment;
use App\Enums\PaymentStatus;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Override;

abstract class BasePaymentGateway implements PaymentGateway
{
    /**
     * Every subclass should retrive it's payment failed reason by its own
     */
    abstract protected function getFailedReason(array $payload): string;

    /**
     * Processing the incoming request from the Payment Gateway
     */
    #[Override]
    public function processGatewayWebhook(array $payload): array
    {
        $payload = $this->normalizePayload($payload);

        /** @var Payment */
        $payment = Payment::where('gateway_order_id', $payload['gateway_order_id'])
            ->firstOrFail();

        DB::beginTransaction();

        $orderStatus    = OrderStatus::CANCELLED;
        $paymentStatus  = PaymentStatus::FAILED;
        $paidAt         = null;

        if ($payload['status'] === PaymentStatus::PAID->value) {
            $orderStatus    = OrderStatus::CONFIRMED;
            $paymentStatus  = PaymentStatus::PAID;
            $paidAt         = now();

            $payment->gateway_payment_id = $payload['gateway_payment_id'];
        }

        $payment->status    = $paymentStatus;
        $payment->paid_at   = $paidAt;

        $payment->save();
        $payment->order()->update(['status' => $orderStatus]);

        DB::commit();

        return $payment->load('order')->toArray();
    }

    public function processRefunding(
        Payment $payment,
        int     $refundedAmount,
        int     $gatewayPaymentId,
        ?int    $parentTransaction = null,
    ) {
        DB::beginTransaction();

        $totalRefunded = abs($payment->order->payments()
            ->where('status', PaymentStatus::REFUNDED)
            ->sum('amount')) + $refundedAmount;

        $payment->status = $totalRefunded >= $payment->amount
            ? PaymentStatus::REFUNDED
            : PaymentStatus::PARTIALLY_REFUNDED;

        if ($payment->status === PaymentStatus::REFUNDED) {
            $payment->order()->update(['status' => OrderStatus::REFUNDED]);
        }

        $payment->save();

        Payment::create([
            'order_id'  => $payment->order_id,
            'gateway'   => $payment->gateway,
            'amount'    => -$refundedAmount,
            'currency'  => $payment->currency,
            'status'    => PaymentStatus::REFUNDED,
            'idempotency_key'  => Str::uuid()->toString(),
            'gateway_order_id' => $payment->gateway_order_id,
            'gateway_payment_id' => $gatewayPaymentId,
            'metadata'           => [
                'refunded_for'       => $payment->id,
                'parent_transaction' => $parentTransaction,
            ],
        ]);

        DB::commit();
    }
}
