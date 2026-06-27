@php
    use App\Enums\FulfillmentType;
    use App\Enums\OrderStatus;

    $defaultFulfillment = old('fulfillment_type');

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
                                            <span class="site-checkout-option__text">Dostavljamo na vašu adresu</span>
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

                        <div data-checkout-address @unless ($defaultFulfillment === FulfillmentType::Delivery->value) hidden @endunless>
                            <x-site.form-field
                                label="Adresa za dostavu"
                                name="address"
                                autocomplete="street-address"
                                :required="$defaultFulfillment === FulfillmentType::Delivery->value"
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
                            <button type="submit" class="site-btn-primary min-h-12 flex-1">
                                Pošalji porudžbinu
                            </button>
                            <a href="{{ route('cart.show') }}" class="site-btn-secondary min-h-12 flex-1">
                                Nazad u korpu
                            </a>
                        </div>
                    </form>

                    <aside class="space-y-4">
                        <x-site.cart-summary
                            :items="$items"
                            :subtotal="$subtotal"
                            :item-count="$itemCount"
                            :interactive="false"
                            :show-checkout-cta="false"
                        />
                    </aside>
                </div>
            </div>
        </x-site.container>
    </x-site.section>
</x-site.layout>
