<?php

namespace App\Services\Ordering;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\Ordering\OrderCreated;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Support\DeliveryPricing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly RestaurantSettingsService $settings,
    ) {}

    /**
     * @param  array{
     *     customer_name: string,
     *     phone: string,
     *     email?: string|null,
     *     address?: string|null,
     *     delivery_area?: string|null,
     *     notes?: string|null,
     *     fulfillment_type: FulfillmentType|string,
     * }  $checkoutData
     */
    public function createFromCheckout(array $checkoutData): Order
    {
        $this->ensureAcceptingOrders();

        if ($this->cart->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Korpa je prazna.',
            ]);
        }

        $fulfillmentType = $checkoutData['fulfillment_type'] instanceof FulfillmentType
            ? $checkoutData['fulfillment_type']
            : FulfillmentType::from((string) $checkoutData['fulfillment_type']);

        $checkoutData = $this->normalizeCheckoutData($checkoutData, $fulfillmentType);

        $this->validateFulfillmentType($fulfillmentType);
        $this->validateCheckoutFields($checkoutData, $fulfillmentType);

        $subtotal = $this->cart->subtotal();
        $this->validateMinimumOrderAmount($subtotal, $fulfillmentType);

        $deliveryArea = $fulfillmentType === FulfillmentType::Delivery
            ? DeliveryPricing::deliveryAreaKey()
            : null;
        $deliveryFee = DeliveryPricing::fee($subtotal, $fulfillmentType, $deliveryArea);
        $total = DeliveryPricing::total($subtotal, $fulfillmentType, $deliveryArea);

        return DB::transaction(function () use ($checkoutData, $fulfillmentType, $subtotal, $deliveryArea, $deliveryFee, $total): Order {
            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'tracking_token' => (string) Str::uuid(),
                'status' => OrderStatus::Pending,
                'fulfillment_type' => $fulfillmentType,
                'customer_name' => trim($checkoutData['customer_name']),
                'phone' => trim($checkoutData['phone']),
                'email' => filled($checkoutData['email'] ?? null) ? trim((string) $checkoutData['email']) : null,
                'address' => $this->resolveOrderAddress($checkoutData, $fulfillmentType),
                'delivery_area' => $deliveryArea !== '' ? $deliveryArea : null,
                'notes' => filled($checkoutData['notes'] ?? null) ? trim((string) $checkoutData['notes']) : null,
                'payment_method' => PaymentMethod::Cash,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
            ]);

            foreach ($this->cart->items() as $cartItem) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'menu_item_id' => $cartItem['menu_item_id'],
                    'name' => $cartItem['name'],
                    'variant_label' => $cartItem['variant_label'],
                    'quantity' => $cartItem['quantity'],
                    'unit_price' => $cartItem['unit_price'],
                    'line_total' => round($cartItem['unit_price'] * $cartItem['quantity'], 2),
                ]);
            }

            OrderStatusLog::query()->create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => OrderStatus::Pending,
                'note' => 'Porudžbina kreirana.',
                'created_at' => now(),
            ]);

            $this->cart->clear();

            $order = $order->load('items');

            OrderCreated::dispatch($order);

            return $order;
        });
    }

    public function generateOrderNumber(): string
    {
        $prefix = (string) config('ordering.order_number.prefix', 'R');
        $date = now()->format('Ymd');
        $pattern = "{$prefix}-{$date}-%";

        $latestSequence = Order::query()
            ->where('order_number', 'like', $pattern)
            ->count();

        return sprintf('%s-%s-%04d', $prefix, $date, $latestSequence + 1);
    }

    private function ensureAcceptingOrders(): void
    {
        if (! $this->settings->acceptingOrders()) {
            throw ValidationException::withMessages([
                'ordering' => $this->settings->closedMessage(),
            ]);
        }
    }

    private function validateFulfillmentType(FulfillmentType $fulfillmentType): void
    {
        if ($fulfillmentType === FulfillmentType::Delivery && ! $this->settings->deliveryEnabled()) {
            throw ValidationException::withMessages([
                'fulfillment_type' => 'Dostava trenutno nije dostupna.',
            ]);
        }

        if ($fulfillmentType === FulfillmentType::Pickup && ! $this->settings->pickupEnabled()) {
            throw ValidationException::withMessages([
                'fulfillment_type' => 'Lično preuzimanje trenutno nije dostupno.',
            ]);
        }
    }

    /**
     * @param  array{
     *     customer_name: string,
     *     phone: string,
     *     email?: string|null,
     *     address?: string|null,
     *     delivery_area?: string|null,
     *     notes?: string|null,
     *     fulfillment_type: FulfillmentType|string,
     * }  $checkoutData
     */
    private function validateCheckoutFields(array $checkoutData, FulfillmentType $fulfillmentType): void
    {
        $errors = [];

        if (! filled($checkoutData['customer_name'])) {
            $errors['customer_name'] = 'Unesite ime.';
        }

        if (! filled($checkoutData['phone'])) {
            $errors['phone'] = 'Unesite broj telefona.';
        }

        if ($fulfillmentType === FulfillmentType::Delivery && ! filled($checkoutData['address'] ?? null)) {
            $errors['address'] = 'Unesite adresu za dostavu u Batajnici.';
        }

        if ($fulfillmentType === FulfillmentType::Delivery && ! DeliveryPricing::deliveryIsConfigured()) {
            $errors['fulfillment_type'] = 'Dostava trenutno nije dostupna.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function validateMinimumOrderAmount(float $subtotal, FulfillmentType $fulfillmentType): void
    {
        if ($fulfillmentType !== FulfillmentType::Delivery) {
            return;
        }

        $minimum = (float) config('ordering.delivery_minimum_order_amount', 700);

        if ($subtotal < $minimum) {
            throw ValidationException::withMessages([
                'cart' => 'Minimalna porudžbina za dostavu je '.number_format($minimum, 0, ',', '.').' RSD.',
            ]);
        }
    }

    /**
     * @param  array{
     *     address?: string|null,
     *     delivery_area?: string|null,
     * }  $checkoutData
     */
    private function resolveOrderAddress(array $checkoutData, FulfillmentType $fulfillmentType): ?string
    {
        if ($fulfillmentType !== FulfillmentType::Delivery) {
            return null;
        }

        $streetAddress = trim((string) ($checkoutData['address'] ?? ''));
        $areaLabel = DeliveryPricing::deliveryAreaLabel();

        return $streetAddress !== ''
            ? "{$areaLabel}, {$streetAddress}"
            : $areaLabel;
    }

    /**
     * @param  array{
     *     customer_name: string,
     *     phone: string,
     *     email?: string|null,
     *     address?: string|null,
     *     delivery_area?: string|null,
     *     notes?: string|null,
     *     fulfillment_type: FulfillmentType|string,
     * }  $checkoutData
     * @return array{
     *     customer_name: string,
     *     phone: string,
     *     email?: string|null,
     *     address?: string|null,
     *     delivery_area?: string|null,
     *     notes?: string|null,
     *     fulfillment_type: FulfillmentType|string,
     * }
     */
    private function normalizeCheckoutData(array $checkoutData, FulfillmentType $fulfillmentType): array
    {
        if ($fulfillmentType !== FulfillmentType::Delivery) {
            return $checkoutData;
        }

        $requestedArea = (string) ($checkoutData['delivery_area'] ?? '');

        if ($requestedArea !== '' && $requestedArea !== DeliveryPricing::deliveryAreaKey()) {
            throw ValidationException::withMessages([
                'fulfillment_type' => 'Dostava je dostupna samo u Batajnici.',
            ]);
        }

        $checkoutData['delivery_area'] = DeliveryPricing::deliveryAreaKey();

        return $checkoutData;
    }
}
