<x-site.layout title="Korpa">
    <x-site.section>
        <x-site.container class="narrow">
            <div class="space-y-8">
                <div class="space-y-3 text-center sm:text-left">
                    <p class="site-eyebrow">Poručivanje</p>
                    <h1 class="site-heading text-4xl sm:text-5xl">Vaša korpa</h1>
                </div>

                <x-site.cart-summary
                    :items="$items"
                    :subtotal="$subtotal"
                    :item-count="$itemCount"
                    :accepting-orders="$acceptingOrders"
                />
            </div>
        </x-site.container>
    </x-site.section>
</x-site.layout>
