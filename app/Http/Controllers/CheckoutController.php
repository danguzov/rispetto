<?php

namespace App\Http\Controllers;

use App\Enums\FulfillmentType;
use App\Services\Ordering\CartService;
use App\Services\Ordering\OrderService;
use App\Services\Ordering\RestaurantSettingsService;
use App\Support\SiteOrdering;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly RestaurantSettingsService $settings,
    ) {}

    public function show(CartService $cart): RedirectResponse|View
    {
        if ($cart->isEmpty()) {
            return redirect()->route('cart.show');
        }

        if (! SiteOrdering::isAcceptingOrders()) {
            return redirect()
                ->route('cart.show')
                ->withErrors(['ordering' => $this->settings->closedMessage()]);
        }

        return view('pages.checkout', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
            'itemCount' => $cart->itemCount(),
            'deliveryEnabled' => $this->settings->deliveryEnabled(),
            'pickupEnabled' => $this->settings->pickupEnabled(),
        ]);
    }

    public function store(Request $request, OrderService $orders): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'fulfillment_type' => ['required', 'in:delivery,pickup'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['fulfillment_type'] === FulfillmentType::Delivery->value) {
            $request->validate([
                'address' => ['required', 'string', 'max:255'],
            ]);
        }

        $order = $orders->createFromCheckout($validated);

        return redirect()
            ->route('order.tracking', ['token' => $order->tracking_token])
            ->with('order_placed', true);
    }
}
