<?php

namespace App\Jobs;

use App\Mail\OrderAcceptedMail;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendOrderAcceptedNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        if (! filled($this->order->email)) {
            return;
        }

        Mail::to($this->order->email)->send(new OrderAcceptedMail($this->order));
    }
}
