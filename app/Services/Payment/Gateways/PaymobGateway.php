<?php

namespace App\Services\Payment\Gateways;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Override;

class PaymobGateway extends BasePaymentGateway
{
    private string $baseUrl;
    private string $secretKey;
    private string $publicKey;
    private string $apiKey;
    private array $integrationIds;

    public function __construct()
    {
        $this->baseUrl      = config('payment.paymob.base_url', 'https://accept.paymob.com');
        $this->secretKey    = config('payment.paymob.secret_key');
        $this->publicKey    = config('payment.paymob.public_key');
        $this->apiKey       = config('payment.paymob.api_key');
        $this->integrationIds = array_filter(config('payment.paymob.integrations'));
    }

    #[Override]
    public function initiatePayment(Order $order, Payment $payment): array
    {
        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->withHeader('Idempotency-Key', $payment->idempotency_key)
            ->post("{$this->baseUrl}/v1/intention/", [
                'amount'            => $payment->amount,
                'currency'          => $payment->currency,
                'payment_methods'   => $this->integrationIds,
                'billing_data'      => $this->billingDataFor($order),
                'extras'            => ['internal_order_id' => $order->id],
                //'special_reference' => (string) $order->id,
            ]);

        if ($response->failed()) {

            Log::error('Paymob intention creation failed', [
                'order_id' => $order->id,
                'response' => $response->body(),
            ]);

            throw ValidationException::withMessages($response->json());
        }

        $data = $response->json();

        $checkoutUrl = sprintf(
            '%s/unifiedcheckout/?publicKey=%s&clientSecret=%s',
            $this->baseUrl,
            $this->publicKey,
            $data['client_secret']
        );

        return [
            'gateway_payment_id' => $data['id'],
            'gateway_order_id'   => (string) $data['intention_order_id'],
            'checkout_url'       => $checkoutUrl,
        ];
    }

    #[Override]
    public function verifyPayment(string $gatewayPaymentId): array
    {
        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->get("{$this->baseUrl}/api/acceptance/transactions/{$gatewayPaymentId}");

        if ($response->failed()) {
            return ['status' => 'pending'];
        }

        $data = $response->json();

        return ['status' => $this->mapStatus($data)];
    }

    #[Override]
    public function refund(Payment $payment, int $refundedAmount): bool
    {
        $response = Http::post("{$this->baseUrl}/api/auth/tokens", [
            'api_key' => $this->apiKey
        ]);

        $token = $response->json('token');

        $response = Http::withToken($token)
            ->acceptJson()
            ->post("{$this->baseUrl}/api/acceptance/void_refund/refund", [
                'transaction_id' => $payment->gateway_payment_id,
                'amount_cents'   => $refundedAmount,
            ]);

        if ($response->successful()) {

            $this->processRefunding(
                $payment,
                $refundedAmount,
                $response['id'],
                $response['parent_transaction']
            );

            return true;
        }

        return false;
    }

    #[Override]
    public function normalizePayload(array $payload): array
    {
        return [
            'gateway_payment_id'    => (string) ($payload['id'] ?? null),
            'gateway_order_id'      => (string) ($payload['order'] ?? null),
            'status'                => $this->mapStatus($payload),
            'amount'                => (int) ($payload['amount_cents'] ?? 0),
            'currency'              => $payload['currency'] ?? null,
        ];
    }

    protected function getFailedReason(array $payload): string
    {
        return $payload['raw']['data_message'] ?? 'UNKNOWN';
    }

    private function stringifyForHmac(mixed $value): string
    {
        return (is_bool($value) ? 'true' : 'false') ?: (string) $value;
    }

    private function mapStatus(array $data): string
    {
        $success = $data['success'] ?? null;
        $pending = $data['pending'] ?? null;

        if (filter_var($pending, FILTER_VALIDATE_BOOLEAN)) {
            return PaymentStatus::PENDING->value;
        }

        return filter_var($success, FILTER_VALIDATE_BOOLEAN)
            ? PaymentStatus::PAID->value
            : PaymentStatus::FAILED->value;
    }

    private function billingDataFor(Order $order): array
    {
        $user = $order->user;

        return [
            'first_name'      => $user->first_name ?? 'NA',
            'last_name'       => $user->last_name ?? 'NA',
            'phone_number'    => $user->phone ?? 'NA',
            'email'           => $user->email ?? 'NA',
            'apartment'       => 'NA',
            'street'          => 'NA',
            'building'        => 'NA',
            'city'            => 'NA',
            'country'         => 'NA',
            'state'           => 'NA',
            'floor'           => 'NA',
            'postal_code'     => 'NA',
        ];
    }
}
