<?php

namespace App\Models;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $order_number
 * @property string $tracking_token
 * @property OrderStatus $status
 * @property FulfillmentType $fulfillment_type
 * @property string $customer_name
 * @property string $phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $notes
 * @property PaymentMethod $payment_method
 * @property string|null $delivery_area
 * @property string $subtotal
 * @property string $delivery_fee
 * @property string $total
 * @property Carbon|null $estimated_ready_at
 * @property Carbon|null $accepted_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'order_number',
    'tracking_token',
    'status',
    'fulfillment_type',
    'customer_name',
    'phone',
    'email',
    'address',
    'delivery_area',
    'notes',
    'payment_method',
    'subtotal',
    'delivery_fee',
    'total',
    'estimated_ready_at',
    'accepted_at',
    'completed_at',
])]
class Order extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'fulfillment_type' => FulfillmentType::class,
            'payment_method' => PaymentMethod::class,
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'estimated_ready_at' => 'datetime',
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderStatusLog, $this>
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }
}
