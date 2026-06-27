<?php

namespace App\Services\Ordering;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderPushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    public function isConfigured(): bool
    {
        return filled(config('ordering.webpush.vapid_public_key'))
            && filled(config('ordering.webpush.vapid_private_key'));
    }

    public function notifyOrder(Order $order): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $subscriptions = OrderPushSubscription::query()
            ->where('order_id', $order->id)
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = json_encode([
            'title' => 'Rispetto — '.$order->status->label(),
            'body' => $this->messageFor($order),
            'url' => route('order.tracking', $order->tracking_token),
        ], JSON_THROW_ON_ERROR);

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => config('ordering.webpush.vapid_subject'),
                'publicKey' => (string) config('ordering.webpush.vapid_public_key'),
                'privateKey' => (string) config('ordering.webpush.vapid_private_key'),
            ],
        ]);

        foreach ($subscriptions as $subscription) {
            $report = $webPush->sendOneNotification(
                Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->public_key,
                    'authToken' => $subscription->auth_token,
                    'contentEncoding' => $subscription->content_encoding,
                ]),
                $payload,
            );

            if ($report->isSubscriptionExpired()) {
                $subscription->delete();
            }
        }
    }

    private function messageFor(Order $order): string
    {
        $message = 'Porudžbina #'.$order->order_number.' — '.$order->status->label().'.';

        if (
            $order->estimated_ready_at !== null
            && in_array($order->status, [
                OrderStatus::Accepted,
                OrderStatus::Preparing,
                OrderStatus::Ready,
                OrderStatus::OutForDelivery,
            ], true)
        ) {
            $message .= ' Spremno oko '.$order->estimated_ready_at->format('H:i').'.';
        }

        return $message;
    }
}
