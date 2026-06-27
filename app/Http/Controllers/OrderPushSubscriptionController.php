<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPushSubscription;
use App\Services\Ordering\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderPushSubscriptionController extends Controller
{
    public function store(Request $request, string $token, WebPushService $webPush): JsonResponse
    {
        if (! $webPush->isConfigured()) {
            return response()->json([
                'message' => 'Web push obaveštenja nisu podešena.',
            ], 503);
        }

        $order = Order::query()
            ->where('tracking_token', $token)
            ->firstOrFail();

        $validated = $request->validate([
            'endpoint' => ['required', 'url', 'max:2000'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
        ]);

        OrderPushSubscription::query()->updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            [
                'order_id' => $order->id,
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => 'aesgcm',
            ],
        );

        return response()->json([
            'subscribed' => true,
        ]);
    }
}
