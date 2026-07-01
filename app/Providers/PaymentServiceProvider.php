<?php

namespace App\Providers;

use App\Services\Payment\Contracts\PaymentGateway;
use App\Services\Payment\Gateways\PaymobGateway;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function ($app) {

            if (is_null($gateway = request()->route('gateway'))) {
                throw ValidationException::withMessages(['payment gateway query paramter is missing!']);
            }

            return match ($gateway) {
                'paymob' => new PaymobGateway(),
                default  => throw new \InvalidArgumentException('Unsupported payment gateway'),
            };
        });
    }
}
