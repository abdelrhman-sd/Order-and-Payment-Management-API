<?php

namespace App\Http\Requests\Order;

use App\Enums\Currencies;
use App\Enums\SourcePlatform;
use App\Enums\OrderStatus;
use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Override;

class CreateOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'currency' => ['required', Rule::enum(Currencies::class)],
            'source_platform' => ['required', Rule::enum(SourcePlatform::class)],

            'products'              => 'required|array',
            'products.*.id'         => 'required|integer|exists:' . Product::class,
            'products.*.quantity'   => 'required|integer|min:1'
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {

            $products   = $this->input('products', []);
            $productIds = collect($products)->pluck('id')->filter()->unique();

            $productMap = Product::whereIn('id', $productIds)
                ->select('id', 'stock', 'price')
                ->get()
                ->keyBy('id');

            $subtotalPrice = 0;

            foreach ($products as $i => $product) {

                $productData = $productMap->get($id = $product['id']);

                if ($product['quantity'] > $productData->stock) {
                    $validator->errors()->add(
                        "products.{$i}.quantity",
                        "Only {$productData->stock} units available for product #{$id}"
                    );

                    continue;
                }

                $subtotalPrice += $product['quantity'] * $productData->price;
            }

            $this->merge(['subtotal' => $subtotalPrice]);
        });
    }

    public function getSubtotal(): int
    {
        return $this->subtotal;
    }

    #[Override]
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);
        $validated['status'] = OrderStatus::PENDING->value;

        return $validated;
    }
}
