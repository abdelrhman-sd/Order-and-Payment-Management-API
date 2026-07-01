<?php

namespace App\Http\Requests\Payment;

use App\Models\Order;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_id'          => 'required|exists:' . Order::class . ',id',
            'payment_method'    => 'required|in:card,wallet',
        ];
    }
}
