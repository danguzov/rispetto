<?php

namespace App\Filament\Concerns;

trait ListensForKitchenOrderBroadcasts
{
    /**
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        if (! self::kitchenBroadcastingEnabled()) {
            return [];
        }

        return [
            'echo-private:kitchen.orders,.OrderCreated' => 'onKitchenBroadcast',
            'echo-private:kitchen.orders,.OrderStatusUpdated' => 'onKitchenBroadcast',
        ];
    }

    public function onKitchenBroadcast(): void
    {
        $this->resetOrderingWidgetCaches();

        if (method_exists($this, 'afterKitchenBroadcast')) {
            $this->afterKitchenBroadcast();
        }
    }

    protected function resetOrderingWidgetCaches(): void
    {
        if (property_exists($this, 'cachedStats')) {
            $this->cachedStats = null;
        }
    }

    protected static function kitchenBroadcastingEnabled(): bool
    {
        $connection = (string) config('broadcasting.default', 'null');

        return $connection !== '' && $connection !== 'null';
    }
}
