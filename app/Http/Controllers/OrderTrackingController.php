<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\OrderTracking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function show(Request $request, string $token): View
    {
        $order = $this->findOrder($token);

        return view('pages.order-tracking', [
            'order' => $order,
            'tracking' => OrderTracking::payload($order),
            'justPlaced' => $request->session()->pull('order_placed', false),
        ]);
    }

    public function status(string $token): JsonResponse
    {
        $order = $this->findOrder($token);

        return response()->json(OrderTracking::payload($order));
    }

    private function findOrder(string $token): Order
    {
        return Order::query()
            ->with('items')
            ->where('tracking_token', $token)
            ->firstOrFail();
    }
}
