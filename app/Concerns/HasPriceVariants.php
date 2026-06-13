<?php

namespace App\Concerns;

trait HasPriceVariants
{
    /**
     * @return list<array{label: string, price: float|string}>
     */
    public function normalizedPriceVariants(): array
    {
        return collect($this->price_variants ?? [])
            ->filter(function (mixed $variant): bool {
                if (! is_array($variant)) {
                    return false;
                }

                return filled($variant['label'] ?? null)
                    && isset($variant['price'])
                    && $variant['price'] !== ''
                    && $variant['price'] !== null;
            })
            ->map(fn (array $variant): array => [
                'label' => (string) $variant['label'],
                'price' => (float) $variant['price'],
            ])
            ->values()
            ->all();
    }

    public function hasPriceVariants(): bool
    {
        return $this->normalizedPriceVariants() !== [];
    }

    public function formattedPrice(?float $price): string
    {
        if ($price === null) {
            return '';
        }

        return number_format($price, 0, ',', '.');
    }

    public function displayPrice(): ?string
    {
        if ($this->hasPriceVariants()) {
            return null;
        }

        if ($this->price === null) {
            return null;
        }

        return $this->formattedPrice((float) $this->price);
    }

    public function startingPrice(): ?float
    {
        if ($this->hasPriceVariants()) {
            return collect($this->normalizedPriceVariants())->min('price');
        }

        return $this->price !== null ? (float) $this->price : null;
    }

    public function displayStartingPrice(): ?string
    {
        $starting = $this->startingPrice();

        return $starting !== null ? $this->formattedPrice($starting) : null;
    }
}
