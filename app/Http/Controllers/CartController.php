<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Services\Ordering\CartService;
use App\Support\SiteOrdering;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request, CartService $cart): JsonResponse|View
    {
        if ($request->expectsJson()) {
            return $this->cartResponse($cart);
        }

        return view('pages.cart', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
            'itemCount' => $cart->itemCount(),
            'acceptingOrders' => SiteOrdering::isAcceptingOrders(),
        ]);
    }

    public function add(Request $request, CartService $cart): JsonResponse
    {
        $validated = $request->validate([
            'menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'variant_label' => ['nullable', 'string', 'max:50'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ]);

        $menuItem = MenuItem::query()->findOrFail($validated['menu_item_id']);

        $cart->add(
            $menuItem,
            $validated['variant_label'] ?? null,
            $validated['quantity'] ?? 1,
        );

        return $this->cartResponse($cart, 201);
    }

    public function update(Request $request, string $key, CartService $cart): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart->update($key, $validated['quantity']);

        return $this->cartResponse($cart);
    }

    public function remove(string $key, CartService $cart): JsonResponse
    {
        $cart->remove($key);

        return $this->cartResponse($cart);
    }

    private function cartResponse(CartService $cart, int $status = 200): JsonResponse
    {
        return response()->json([
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
            'item_count' => $cart->itemCount(),
            'is_empty' => $cart->isEmpty(),
        ], $status);
    }
}
