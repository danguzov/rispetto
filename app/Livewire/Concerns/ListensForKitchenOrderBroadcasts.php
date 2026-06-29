<?php

namespace App\Livewire\Concerns;

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
        if (method_exists($this, 'afterKitchenBroadcast')) {
            $this->afterKitchenBroadcast();
        }
    }

    protected static function kitchenBroadcastingEnabled(): bool
    {
        $connection = (string) config('broadcasting.default', 'null');

        return $connection !== '' && $connection !== 'null';
    }
}
