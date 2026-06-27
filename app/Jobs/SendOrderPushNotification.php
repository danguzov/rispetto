<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Ordering\WebPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendOrderPushNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function handle(WebPushService $webPush): void
    {
        $webPush->notifyOrder($this->order);
    }
}
