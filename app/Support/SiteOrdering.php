<?php

namespace App\Support;

use App\Services\Ordering\RestaurantSettingsService;

final class SiteOrdering
{
    public static function isEnabled(): bool
    {
        return (bool) config('ordering.enabled', true);
    }

    public static function isAcceptingOrders(): bool
    {
        if (! self::isEnabled()) {
            return false;
        }

        return app(RestaurantSettingsService::class)->acceptingOrders();
    }

    public static function closedMessage(): string
    {
        return app(RestaurantSettingsService::class)->closedMessage();
    }
}
