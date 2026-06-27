<?php

namespace App\Services\Ordering;

use App\Events\Ordering\OrderingAvailabilityChanged;
use App\Models\RestaurantSettings;
use Illuminate\Support\Facades\Cache;

class RestaurantSettingsService
{
    private const CACHE_KEY = 'ordering.restaurant_settings';

    private static ?RestaurantSettings $resolved = null;

    public function settings(): RestaurantSettings
    {
        if (self::$resolved instanceof RestaurantSettings) {
            return self::$resolved;
        }

        // Ukloni zastareli keš ako je model serializovan pre učitavanja klase.
        Cache::forget(self::CACHE_KEY);

        $settings = RestaurantSettings::query()->first();

        if ($settings === null) {
            $settings = RestaurantSettings::query()->create([
                'accepting_orders' => true,
                'delivery_enabled' => true,
                'pickup_enabled' => true,
            ]);
        }

        return self::$resolved = $settings;
    }

    public function acceptingOrders(): bool
    {
        return $this->settings()->accepting_orders;
    }

    public function deliveryEnabled(): bool
    {
        return $this->settings()->delivery_enabled;
    }

    public function pickupEnabled(): bool
    {
        return $this->settings()->pickup_enabled;
    }

    public function minOrderAmount(): ?float
    {
        $amount = $this->settings()->min_order_amount;

        return $amount !== null ? (float) $amount : null;
    }

    public function closedMessage(): string
    {
        return $this->settings()->closed_message
            ?? (string) config('ordering.closed_message');
    }

    public function updateAcceptingOrders(bool $acceptingOrders): RestaurantSettings
    {
        $settings = $this->settings();

        if ($settings->accepting_orders === $acceptingOrders) {
            return $settings;
        }

        $settings->update(['accepting_orders' => $acceptingOrders]);
        $this->clearCache();
        $settings->refresh();

        OrderingAvailabilityChanged::dispatch(
            $settings->accepting_orders,
            $this->closedMessage(),
        );

        return $settings;
    }

    public function clearCache(): void
    {
        self::$resolved = null;
        Cache::forget(self::CACHE_KEY);
    }
}
