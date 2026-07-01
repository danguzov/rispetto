@php
    use App\Enums\OrderStatus;

    $currentIndex = $tracking['current_index'];
@endphp

<x-site.layout
    title="Porudžbina {{ $order->order_number }}"
    robots="noindex, nofollow"
>
    <x-site.section>
        <x-site.container class="narrow">
            <div
                class="space-y-8"
                data-order-tracking
                data-tracking-token="{{ $order->tracking_token }}"
                data-status-url="{{ route('order.tracking.status', $order->tracking_token) }}"
                @if (filled(config('ordering.webpush.vapid_public_key')))
                    data-vapid-public-key="{{ config('ordering.webpush.vapid_public_key') }}"
                    data-push-subscribe-url="{{ route('order.push.subscribe', $order->tracking_token) }}"
                @endif
            >
                <div class="space-y-3 text-center sm:text-left">
                    <p class="site-eyebrow">Praćenje porudžbine</p>
                    <h1 class="site-heading text-4xl sm:text-5xl">#{{ $order->order_number }}</h1>
                    @if ($justPlaced)
                        <p class="site-subheading mx-auto max-w-xl text-rispetto-accent sm:mx-0">
                            Hvala! Porudžbina je poslata. Sačuvajte ovaj link da pratite status.
                        </p>
                    @else
                        <p class="site-subheading mx-auto max-w-xl sm:mx-0">
                            Status vaše porudžbine u restoranu Rispetto.
                        </p>
                    @endif
                </div>

                <div data-order-tracking-status>
                    @if ($order->status === OrderStatus::Cancelled)
                        <div class="site-panel border-red-500/40 p-6 text-center">
                            <p class="text-lg font-medium text-rispetto-text">Porudžbina je otkazana</p>
                            <p class="mt-2 text-sm text-rispetto-muted">Kontaktirajte restoran ako imate pitanja.</p>
                        </div>
                    @else
                        <ol class="site-order-timeline" data-order-timeline>
                            @foreach ($tracking['timeline'] as $index => $step)
                                @php
                                    $isComplete = $currentIndex !== -1 && $index < $currentIndex;
                                    $isCurrent = $currentIndex === $index;
                                @endphp
                                <li
                                    @class([
                                        'site-order-timeline__item',
                                        'site-order-timeline__item--complete' => $isComplete,
                                        'site-order-timeline__item--current' => $isCurrent,
                                    ])
                                    data-timeline-index="{{ $index }}"
                                >
                                    <div class="site-order-timeline__marker" aria-hidden="true"></div>
                                    <div class="min-w-0">
                                        <p class="text-base font-medium text-rispetto-text">{{ $step['label'] }}</p>
                                        <p class="mt-1 text-sm text-rispetto-muted">{{ $step['customer'] }}</p>
                                        @if ($isCurrent && $order->estimated_ready_at && in_array($order->status, [OrderStatus::Accepted, OrderStatus::Preparing, OrderStatus::Ready, OrderStatus::OutForDelivery], true))
                                            <p
                                                class="mt-3 text-lg font-semibold text-rispetto-accent"
                                                data-order-countdown
                                                data-ready-at="{{ $order->estimated_ready_at->toIso8601String() }}"
                                            >
                                                Spremno za ~{{ max(0, (int) now()->diffInMinutes($order->estimated_ready_at, false)) }} min
                                            </p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>

                <div class="site-panel space-y-4 p-6 sm:p-8">
                    <h2 class="text-lg font-medium text-rispetto-text">Detalji porudžbine</h2>

                    <dl class="grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-rispetto-muted">Ime</dt>
                            <dd class="font-medium text-rispetto-text">{{ $order->customer_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-rispetto-muted">Telefon</dt>
                            <dd class="font-medium text-rispetto-text">{{ $order->phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-rispetto-muted">Način</dt>
                            <dd class="font-medium text-rispetto-text">{{ $order->fulfillment_type->label() }}</dd>
                        </div>
                        <div>
                            <dt class="text-rispetto-muted">Plaćanje</dt>
                            <dd class="font-medium text-rispetto-text">{{ $order->payment_method->label() }}</dd>
                        </div>
                        @if ($order->address)
                            <div class="sm:col-span-2">
                                <dt class="text-rispetto-muted">Adresa</dt>
                                <dd class="font-medium text-rispetto-text">{{ $order->address }}</dd>
                            </div>
                        @endif
                    </dl>

                    <ul class="divide-y divide-rispetto-border border-t border-rispetto-border pt-4">
                        @foreach ($order->items as $item)
                            <li class="flex items-start justify-between gap-4 py-3 text-sm">
                                <div>
                                    <p class="font-medium text-rispetto-text">
                                        {{ $item->quantity }}× {{ $item->name }}
                                    </p>
                                    @if ($item->variant_label)
                                        <p class="text-rispetto-muted">{{ $item->variant_label }}</p>
                                    @endif
                                </div>
                                <p class="font-medium text-rispetto-accent">
                                    {{ number_format((float) $item->line_total, 0, ',', '.') }} RSD
                                </p>
                            </li>
                        @endforeach
                    </ul>

                    @if ($order->fulfillment_type === \App\Enums\FulfillmentType::Delivery)
                        <div class="space-y-2 border-t border-rispetto-border pt-4 text-sm">
                            <div class="flex items-center justify-between gap-4 text-rispetto-muted">
                                <span>Jela</span>
                                <span class="font-medium text-rispetto-text">{{ number_format((float) $order->subtotal, 0, ',', '.') }} RSD</span>
                            </div>
                            <div class="flex items-center justify-between gap-4 text-rispetto-muted">
                                <span>Dostava</span>
                                <span class="font-medium text-rispetto-text">
                                    @if ((float) $order->delivery_fee > 0)
                                        {{ number_format((float) $order->delivery_fee, 0, ',', '.') }} RSD
                                    @else
                                        Besplatno
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endif

                    <p class="border-t border-rispetto-border pt-4 text-lg font-medium text-rispetto-text">
                        Ukupno:
                        <span class="text-rispetto-accent">{{ number_format((float) $order->total, 0, ',', '.') }} RSD</span>
                    </p>
                </div>

                <div class="site-panel space-y-3 p-6 text-center sm:text-left">
                    <h2 class="text-base font-medium text-rispetto-text">Link za praćenje</h2>
                    <p class="text-sm text-rispetto-muted">
                        Sačuvajte ovaj link — možete pratiti status porudžbine bez prijave. Link stiže i na mejl kada restoran prihvati porudžbinu.
                    </p>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-stretch">
                        <input
                            type="text"
                            readonly
                            value="{{ route('order.tracking', $order->tracking_token) }}"
                            class="min-h-12 w-full flex-1 rounded-xl border border-rispetto-border bg-rispetto-bg px-4 py-3 text-sm text-rispetto-text select-all"
                            data-order-tracking-link
                            onfocus="this.select()"
                            aria-label="Link za praćenje porudžbine"
                        >
                        <button
                            type="button"
                            class="site-btn-secondary min-h-12 shrink-0 px-5"
                            data-order-copy-link
                        >
                            Kopiraj link
                        </button>
                    </div>
                    <p class="hidden text-sm text-rispetto-accent" data-order-copy-feedback role="status">
                        Link je kopiran.
                    </p>
                </div>

                @if (filled(config('ordering.webpush.vapid_public_key')))
                    <div class="flex justify-center">
                        <button
                            type="button"
                            class="site-btn-secondary min-h-12"
                            data-order-push-subscribe
                            data-push-state="idle"
                        >
                            Obavesti me
                        </button>
                    </div>
                @endif
            </div>
        </x-site.container>
    </x-site.section>
</x-site.layout>
