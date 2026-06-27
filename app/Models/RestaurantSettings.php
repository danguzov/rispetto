<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property bool $accepting_orders
 * @property string|null $closed_message
 * @property bool $delivery_enabled
 * @property bool $pickup_enabled
 * @property string|null $min_order_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'accepting_orders',
    'closed_message',
    'delivery_enabled',
    'pickup_enabled',
    'min_order_amount',
])]
class RestaurantSettings extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'accepting_orders' => 'boolean',
            'delivery_enabled' => 'boolean',
            'pickup_enabled' => 'boolean',
            'min_order_amount' => 'decimal:2',
        ];
    }
}
