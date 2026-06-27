<?php

namespace App\Listeners;

use App\Enums\OrderStatus;
use App\Events\Ordering\OrderStatusUpdated;
use App\Jobs\SendOrderAcceptedNotification;
use App\Jobs\SendOrderPushNotification;

class SendOrderCustomerNotifications
{
    public function handle(OrderStatusUpdated $event): void
    {
        $order = $event->order;

        if ($order->status === OrderStatus::Accepted && filled($order->email)) {
            SendOrderAcceptedNotification::dispatch($order);
        }

        SendOrderPushNotification::dispatch($order);
    }
}
