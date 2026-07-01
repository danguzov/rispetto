<?php

namespace App\Http\Controllers;

use App\Enums\FulfillmentType;
use App\Services\Ordering\CartService;
use App\Services\Ordering\OrderService;
use App\Services\Ordering\RestaurantSettingsService;
use App\Support\SiteOrdering;
use App\Support\DeliveryPricing;
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
            'deliveryAreaKey' => DeliveryPricing::deliveryAreaKey(),
            'deliveryAreaLabel' => DeliveryPricing::deliveryAreaLabel(),
            'deliveryAreaFees' => DeliveryPricing::configuredAreaFees(),
            'deliveryFreeAbove' => DeliveryPricing::freeAbove(),
            'deliveryMinimum' => (float) config('ordering.delivery_minimum_order_amount', 700),
        ]);
    }

    public function store(Request $request, OrderService $orders): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'fulfillment_type' => ['required', 'in:delivery,pickup'],
            'delivery_area' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['fulfillment_type'] === FulfillmentType::Delivery->value) {
            if ($request->filled('delivery_area') && $request->input('delivery_area') !== DeliveryPricing::deliveryAreaKey()) {
                return back()
                    ->withErrors(['fulfillment_type' => 'Dostava je dostupna samo u Batajnici.'])
                    ->withInput();
            }

            $request->validate([
                'address' => ['required', 'string', 'max:255'],
            ]);

            $validated['delivery_area'] = DeliveryPricing::deliveryAreaKey();
        }

        $order = $orders->createFromCheckout($validated);

        return redirect()
            ->route('order.tracking', ['token' => $order->tracking_token])
            ->with('order_placed', true);
    }
}
