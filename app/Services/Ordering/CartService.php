<?php

namespace App\Services\Ordering;

use App\Models\MenuItem;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(
        private readonly RestaurantSettingsService $settings,
    ) {}

    /**
     * @return list<array{key: string, menu_item_id: int, name: string, variant_label: string|null, unit_price: float, quantity: int}>
     */
    public function items(): array
    {
        /** @var list<array{key: string, menu_item_id: int, name: string, variant_label: string|null, unit_price: float, quantity: int}> $items */
        $items = session($this->sessionKey().'.items', []);

        return $items;
    }

    public function add(MenuItem $menuItem, ?string $variantLabel = null, int $quantity = 1): void
    {
        $this->ensureAcceptingOrders();

        $menuItem = $this->resolveActiveMenuItem($menuItem);
        $quantity = $this->normalizeQuantity($quantity);
        $unitPrice = $this->resolveUnitPrice($menuItem, $variantLabel);
        $key = $this->itemKey($menuItem->id, $variantLabel);

        $items = $this->items();
        $found = false;

        foreach ($items as $index => $item) {
            if ($item['key'] !== $key) {
                continue;
            }

            $items[$index]['quantity'] = $this->normalizeQuantity($item['quantity'] + $quantity);
            $found = true;
            break;
        }

        if (! $found) {
            $items[] = [
                'key' => $key,
                'menu_item_id' => $menuItem->id,
                'name' => $menuItem->name,
                'variant_label' => $variantLabel,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
            ];
        }

        $this->persistItems($items);
    }

    public function update(string $key, int $quantity): void
    {
        $quantity = $this->normalizeQuantity($quantity);
        $items = $this->items();
        $updated = false;

        foreach ($items as $index => $item) {
            if ($item['key'] !== $key) {
                continue;
            }

            $items[$index]['quantity'] = $quantity;
            $updated = true;
            break;
        }

        if (! $updated) {
            throw ValidationException::withMessages([
                'key' => 'Stavka nije pronađena u korpi.',
            ]);
        }

        $this->persistItems($items);
    }

    public function remove(string $key): void
    {
        $items = array_values(array_filter(
            $this->items(),
            fn (array $item): bool => $item['key'] !== $key,
        ));

        $this->persistItems($items);
    }

    public function clear(): void
    {
        session()->forget($this->sessionKey());
    }

    public function isEmpty(): bool
    {
        return $this->items() === [];
    }

    public function itemCount(): int
    {
        return array_sum(array_column($this->items(), 'quantity'));
    }

    public function subtotal(): float
    {
        return round(array_sum(array_map(
            fn (array $item): float => $item['unit_price'] * $item['quantity'],
            $this->items(),
        )), 2);
    }

    public function resolveUnitPrice(MenuItem $menuItem, ?string $variantLabel): float
    {
        if ($menuItem->hasPriceVariants()) {
            if (! filled($variantLabel)) {
                throw ValidationException::withMessages([
                    'variant_label' => 'Izaberite veličinu pice.',
                ]);
            }

            foreach ($menuItem->normalizedPriceVariants() as $variant) {
                if ($variant['label'] === $variantLabel) {
                    return (float) $variant['price'];
                }
            }

            throw ValidationException::withMessages([
                'variant_label' => 'Izabrana veličina nije dostupna.',
            ]);
        }

        if ($menuItem->price === null) {
            throw ValidationException::withMessages([
                'menu_item_id' => 'Jelo nema definisanu cenu.',
            ]);
        }

        return (float) $menuItem->price;
    }

    public function itemKey(int $menuItemId, ?string $variantLabel): string
    {
        return Str::lower($menuItemId.':'.($variantLabel ?? ''));
    }

    private function sessionKey(): string
    {
        return (string) config('ordering.cart.session_key', 'cart');
    }

    /**
     * @param  list<array{key: string, menu_item_id: int, name: string, variant_label: string|null, unit_price: float, quantity: int}>  $items
     */
    private function persistItems(array $items): void
    {
        session([$this->sessionKey().'.items' => $items]);
    }

    private function ensureAcceptingOrders(): void
    {
        if (! $this->settings->acceptingOrders()) {
            throw ValidationException::withMessages([
                'ordering' => $this->settings->closedMessage(),
            ]);
        }
    }

    private function resolveActiveMenuItem(MenuItem $menuItem): MenuItem
    {
        $menuItem->loadMissing('category');

        if (! $menuItem->is_active || ! ($menuItem->category?->is_active ?? false)) {
            throw ValidationException::withMessages([
                'menu_item_id' => 'Jelo trenutno nije dostupno za poručivanje.',
            ]);
        }

        return $menuItem;
    }

    private function normalizeQuantity(int $quantity): int
    {
        $max = (int) config('ordering.cart.max_quantity_per_item', 20);

        if ($quantity < 1 || $quantity > $max) {
            throw ValidationException::withMessages([
                'quantity' => "Količina mora biti između 1 i {$max}.",
            ]);
        }

        return $quantity;
    }
}
