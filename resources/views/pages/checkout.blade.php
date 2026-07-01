@php
    use App\Enums\FulfillmentType;
    use App\Enums\OrderStatus;
    use App\Support\DeliveryPricing;

    $defaultFulfillment = old('fulfillment_type');
    $formattedDeliveryMinimum = number_format($deliveryMinimum, 0, ',', '.');
    $formattedDeliveryFreeAbove = number_format($deliveryFreeAbove, 0, ',', '.');
    $deliveryMinimumMet = $subtotal >= $deliveryMinimum;
    $isDeliverySelected = $defaultFulfillment === FulfillmentType::Delivery->value;
    $initialDeliveryFee = $isDeliverySelected
        ? DeliveryPricing::fee($subtotal, FulfillmentType::Delivery)
        : 0.0;
    $initialOrderTotal = $subtotal + $initialDeliveryFee;

    if ($defaultFulfillment === null) {
        if ($deliveryEnabled && ! $pickupEnabled) {
            $defaultFulfillment = FulfillmentType::Delivery->value;
        } elseif ($pickupEnabled && ! $deliveryEnabled) {
            $defaultFulfillment = FulfillmentType::Pickup->value;
        } else {
            $defaultFulfillment = FulfillmentType::Pickup->value;
        }
    }
@endphp

<x-site.layout title="Naručivanje">
    <x-site.section>
        <x-site.container class="narrow">
            <div class="space-y-8">
                <div class="space-y-3 text-center sm:text-left">
                    <p class="site-eyebrow">Poručivanje</p>
                    <h1 class="site-heading text-4xl sm:text-5xl">Završite porudžbinu</h1>
                    <p class="site-subheading mx-auto max-w-xl sm:mx-0">
                        Unesite podatke za dostavu ili lično preuzimanje. Plaćanje je gotovinom.
                    </p>
                </div>

                @error('ordering')
                    <div class="site-panel border-red-500/40 p-4 text-sm text-red-300">
                        {{ $message }}
                    </div>
                @enderror

                @error('cart')
                    <div class="site-panel border-red-500/40 p-4 text-sm text-red-300">
                        {{ $message }}
                    </div>
                @enderror

                <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,22rem)] lg:items-start">
                    <form
                        method="POST"
                        action="{{ route('checkout.store') }}"
                        class="site-panel space-y-6 p-6 sm:p-8"
                        data-checkout-form
                        data-delivery-minimum="{{ (int) $deliveryMinimum }}"
                        data-delivery-free-above="{{ (int) $deliveryFreeAbove }}"
                        data-delivery-area-key="{{ $deliveryAreaKey }}"
                        data-delivery-area-fees='@json($deliveryAreaFees)'
                        data-cart-subtotal="{{ (int) round($subtotal) }}"
                    >
                        @csrf

                        <fieldset class="space-y-4">
                            <legend class="text-base font-medium text-rispetto-text">Način preuzimanja</legend>

                            <div class="grid gap-3 sm:grid-cols-2">
                                @if ($pickupEnabled)
                                    <label class="site-checkout-option">
                                        <input
                                            type="radio"
                                            name="fulfillment_type"
                                            value="{{ FulfillmentType::Pickup->value }}"
                                            class="site-checkout-option__input"
                                            data-fulfillment-option
                                            @checked($defaultFulfillment === FulfillmentType::Pickup->value)
                                        >
                                        <span class="site-checkout-option__content">
                                            <span class="site-checkout-option__title">Lično preuzimanje</span>
                                            <span class="site-checkout-option__text">Preuzimate jelo u restoranu</span>
                                        </span>
                                    </label>
                                @endif

                                @if ($deliveryEnabled)
                                    <label class="site-checkout-option">
                                        <input
                                            type="radio"
                                            name="fulfillment_type"
                                            value="{{ FulfillmentType::Delivery->value }}"
                                            class="site-checkout-option__input"
                                            data-fulfillment-option
                                            @checked($defaultFulfillment === FulfillmentType::Delivery->value)
                                        >
                                        <span class="site-checkout-option__content">
                                            <span class="site-checkout-option__title">Dostava</span>
                                            <span class="site-checkout-option__text">Dostava samo za Batajnicu</span>
                                            <span class="site-checkout-option__text">Minimalna porudžbina – {{ $formattedDeliveryMinimum }} din</span>
                                        </span>
                                    </label>
                                @endif
                            </div>

                            @error('fulfillment_type')
                                <p class="text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <x-site.form-field
                            label="Ime i prezime"
                            name="customer_name"
                            autocomplete="name"
                            required
                        />

                        <x-site.form-field
                            label="Telefon"
                            name="phone"
                            type="tel"
                            inputmode="tel"
                            autocomplete="tel"
                            required
                        />

                        <x-site.form-field
                            label="Email (opciono)"
                            name="email"
                            type="email"
                            autocomplete="email"
                        />

                        <div
                            class="space-y-6"
                            data-checkout-delivery-fields
                            @unless ($defaultFulfillment === FulfillmentType::Delivery->value) hidden @endunless
                        >
                            <div
                                class="site-checkout-delivery-notice"
                                data-checkout-delivery-minimum
                                role="status"
                                @class([
                                    'site-checkout-delivery-notice--warning' => $defaultFulfillment === FulfillmentType::Delivery->value && ! $deliveryMinimumMet,
                                ])
                            >
                                <p class="site-checkout-delivery-notice__title">
                                    Dostava je dostupna isključivo u {{ $deliveryAreaLabel }}
                                </p>
                                <p class="text-sm text-rispetto-muted">
                                    Minimalna porudžbina: {{ $formattedDeliveryMinimum }} RSD
                                </p>
                                <p
                                    class="site-checkout-delivery-notice__hint"
                                    data-checkout-delivery-minimum-hint
                                    @unless ($defaultFulfillment === FulfillmentType::Delivery->value && ! $deliveryMinimumMet) hidden @endunless
                                >
                                    Još {{ number_format($deliveryMinimum - $subtotal, 0, ',', '.') }} RSD do minimuma.
                                </p>
                                <p class="mt-2 text-sm text-rispetto-muted">
                                    Besplatna dostava za porudžbine preko {{ $formattedDeliveryFreeAbove }} RSD.
                                </p>
                                <p
                                    class="mt-2 text-sm font-medium text-rispetto-text"
                                    data-checkout-delivery-fee-notice
                                    @unless ($isDeliverySelected) hidden @endunless
                                >
                                    @if ($initialDeliveryFee > 0)
                                        Cena dostave: {{ number_format($initialDeliveryFee, 0, ',', '.') }} RSD
                                    @else
                                        Dostava je besplatna za ovu porudžbinu.
                                    @endif
                                </p>
                            </div>

                            <x-site.form-field
                                label="Adresa u Batajnici"
                                name="address"
                                autocomplete="street-address"
                                placeholder="Ulica i broj"
                                :required="$isDeliverySelected"
                            />
                        </div>

                        <x-site.form-textarea
                            label="Napomena (opciono)"
                            name="notes"
                            rows="3"
                            placeholder="Npr. bez luka, pozvati na interfon..."
                        />

                        <div class="rounded-xl border border-rispetto-border bg-rispetto-elevated p-4 text-sm text-rispetto-muted">
                            Plaćanje gotovinom {{ $defaultFulfillment === FulfillmentType::Delivery->value ? 'pri dostavi' : 'pri preuzimanju' }}.
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button
                                type="submit"
                                class="site-btn-primary min-h-12 flex-1"
                                data-checkout-submit
                                @if ($defaultFulfillment === FulfillmentType::Delivery->value && ! $deliveryMinimumMet) disabled @endif
                            >
                                Pošalji porudžbinu
                            </button>
                            <a href="{{ route('cart.show') }}" class="site-btn-secondary min-h-12 flex-1">
                                Nazad u korpu
                            </a>
                        </div>
                    </form>

                    <aside class="space-y-4" data-checkout-summary>
                        <x-site.cart-summary
                            :items="$items"
                            :subtotal="$subtotal"
                            :item-count="$itemCount"
                            :interactive="false"
                            :show-checkout-cta="false"
                            :show-order-total="true"
                            :delivery-fee="$initialDeliveryFee"
                            :order-total="$initialOrderTotal"
                            :show-delivery-breakdown="$isDeliverySelected"
                        />
                    </aside>
                </div>
            </div>
        </x-site.container>
    </x-site.section>
</x-site.layout>
