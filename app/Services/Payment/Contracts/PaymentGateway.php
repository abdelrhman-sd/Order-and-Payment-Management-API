<?php

namespace App\Services\Payment\Contracts;

use App\Models\{Order, Payment};

/**
 * Unified contract every payment gateway must implement.
 * PaymentService and WebhookController only ever talk to this interface
 * never to a concrete gateway class directly.
 */
interface PaymentGateway
{
    /**
     * Start a payment for the given order.
     * Returns a normalized array:
     * [
     *   'gateway_payment_id' => string,   // gateway's reference for this attempt
     *   'gateway_order_id'   => string|null,
     *   'checkout_url'       => string,   // where to send the customer
     * ]
     */
    public function initiatePayment(Order $order, Payment $payment): array;

    /**
     * Ask the gateway directly for a payment's current status.
     * Used by the reconciliation job in the background.
     * Returns normalized: ['status' => 'paid'|'failed'|'pending', 'raw' => array]
     */
    public function verifyPayment(string $gatewayPaymentId): array;

    /**
     * Issue a refund (full or partial). Amount in smallest currency unit.
     */
    public function refund(Payment $payment, int $amountInCents): bool;

    /**
     * Normalize a raw incoming payload (POST webhook body OR GET query params)
     * into one consistent flat array shape, so the rest of the app never has
     * to care whether this came from a webhook or a redirect.
     *
     * Normalized shape:
     * [
     *   'gateway_payment_id' => string,
     *   'gateway_order_id'   => string,
     *   'status'             => 'paid'|'failed'|'pending',
     *   'amount_cents'       => int,
     *   'currency'           => string,
     * ]
     */
    public function normalizePayload(array $payload): array;

    /**
     * Processing the incoming request from the Payment Gateway
     */
    public function processGatewayWebhook(array $payload): array;
}
