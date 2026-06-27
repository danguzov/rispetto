@props(['order'])

@php
    $isPending = $order->status === \App\Enums\OrderStatus::Pending;
    $isDelivery = $this->isDelivery($order);
    $waitingMinutes = $isPending ? max(0, (int) $order->created_at?->diffInMinutes(now())) : 0;
@endphp

<article
    wire:key="kitchen-order-{{ $order->id }}"
    @class([
        'overflow-hidden rounded-xl border bg-white shadow-sm dark:bg-gray-900',
        'border-warning-500/50 shadow-lg shadow-warning-500/10 ring-2 ring-warning-500/30' => $isPending,
        'border-gray-200 dark:border-gray-700' => ! $isPending,
    ])
>
    @if ($isPending)
        <div class="flex items-center justify-between gap-2 bg-warning-500/15 px-4 py-2.5">
            <div class="flex min-w-0 items-center gap-2">
                <x-filament::icon
                    icon="heroicon-m-bell-alert"
                    class="size-5 shrink-0 text-warning-600 dark:text-warning-400"
                />
                <p class="text-sm font-semibold text-warning-700 dark:text-warning-300">
                    Nova porudžbina
                </p>
            </div>

            <x-filament::badge color="warning" size="sm">
                {{ $waitingMinutes < 1 ? 'Upravo stigla' : "Čeka {$waitingMinutes} min" }}
            </x-filament::badge>
        </div>
    @endif

    <div class="p-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p @class([
                    'font-bold tracking-tight text-gray-950 dark:text-white',
                    'text-lg' => $isPending,
                    'text-sm' => ! $isPending,
                ])>
                    #{{ $order->order_number }}
                </p>

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $order->created_at?->format('H:i') }}
                    · {{ $order->created_at?->diffForHumans() }}
                </p>
            </div>

            <x-filament::badge :color="$isDelivery ? 'info' : 'gray'" size="sm">
                {{ $order->fulfillment_type->label() }}
            </x-filament::badge>
        </div>

        <div class="mt-4 space-y-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                    Ime kupca
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        readonly
                        :value="$order->customer_name"
                        class="select-all"
                        onfocus="this.select()"
                    />
                </x-filament::input.wrapper>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                    Telefon
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="tel"
                        readonly
                        :value="$order->phone"
                        class="select-all"
                        onfocus="this.select()"
                    />
                </x-filament::input.wrapper>
            </div>

            @if ($order->address)
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                        Adresa
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            readonly
                            :value="$order->address"
                            class="select-all"
                            onfocus="this.select()"
                        />
                    </x-filament::input.wrapper>
                </div>
            @endif
        </div>

        <div class="mt-4">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Stavke ({{ $order->items->count() }})
            </p>

            <div class="space-y-2">
                @foreach ($order->items as $item)
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            readonly
                            :value="$this->orderItemLineLabel($item)"
                            class="select-all text-sm"
                            onfocus="this.select()"
                        />
                    </x-filament::input.wrapper>
                @endforeach
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between gap-3 border-t border-gray-200 pt-3 dark:border-gray-700">
            <p @class([
                'font-bold text-gray-950 dark:text-white',
                'text-base' => $isPending,
                'text-sm' => ! $isPending,
            ])>
                Ukupno: {{ number_format((float) $order->total, 0, ',', '.') }} RSD
            </p>

            @if ($eta = $this->etaLabelFor($order))
                <p class="text-xs font-medium text-primary-600 dark:text-primary-400">
                    ETA {{ $eta }}
                </p>
            @endif
        </div>

        @if ($isPending)
            <div class="mt-4 space-y-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Prihvati za (min)
                </p>

                <div class="grid grid-cols-3 gap-2">
                    @foreach ($this->etaPresets as $preset)
                        <x-filament::button
                            size="sm"
                            color="primary"
                            wire:click="acceptOrder({{ $order->id }}, {{ $preset }})"
                            wire:loading.attr="disabled"
                            wire:target="acceptOrder({{ $order->id }}, {{ $preset }})"
                        >
                            {{ $preset }} min
                        </x-filament::button>
                    @endforeach
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                        Drugi ETA (min)
                    </label>
                    <div class="flex gap-2">
                        <div class="min-w-0 flex-1">
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    type="number"
                                    min="5"
                                    max="180"
                                    wire:model.live="customEtaByOrder.{{ $order->id }}"
                                    placeholder="{{ $this->customEtaFor($order) }}"
                                />
                            </x-filament::input.wrapper>
                        </div>

                        <x-filament::button
                            size="md"
                            icon="heroicon-m-check-circle"
                            wire:click="acceptOrderWithCustomEta({{ $order->id }})"
                            wire:loading.attr="disabled"
                            wire:target="acceptOrderWithCustomEta({{ $order->id }})"
                        >
                            Prihvati
                        </x-filament::button>
                    </div>
                </div>

                <x-filament::button
                    class="w-full"
                    size="sm"
                    color="danger"
                    outlined
                    wire:click="cancelOrder({{ $order->id }})"
                    wire:confirm="Otkazati porudžbinu?"
                >
                    Otkaži porudžbinu
                </x-filament::button>
            </div>
        @else
            <div class="mt-4 flex flex-wrap items-center gap-2">
                @if ($order->status === \App\Enums\OrderStatus::Accepted)
                    <x-filament::button
                        size="sm"
                        wire:click="markPreparing({{ $order->id }})"
                    >
                        U pripremi
                    </x-filament::button>
                @endif

                @if ($order->status === \App\Enums\OrderStatus::Preparing)
                    <x-filament::button
                        size="sm"
                        color="success"
                        wire:click="markReady({{ $order->id }})"
                    >
                        Spremno
                    </x-filament::button>
                @endif

                @if ($order->status === \App\Enums\OrderStatus::Ready)
                    @if ($isDelivery)
                        <x-filament::button
                            size="sm"
                            wire:click="markOutForDelivery({{ $order->id }})"
                        >
                            Na putu
                        </x-filament::button>
                    @endif

                    <x-filament::button
                        size="sm"
                        color="success"
                        wire:click="completeOrder({{ $order->id }})"
                    >
                        Završeno
                    </x-filament::button>
                @endif

                @if ($order->status === \App\Enums\OrderStatus::OutForDelivery)
                    <x-filament::button
                        size="sm"
                        color="success"
                        wire:click="completeOrder({{ $order->id }})"
                    >
                        Završeno
                    </x-filament::button>
                @endif

                <x-filament::icon-button
                    icon="heroicon-m-x-mark"
                    color="danger"
                    size="sm"
                    label="Otkaži"
                    wire:click="cancelOrder({{ $order->id }})"
                    wire:confirm="Otkazati porudžbinu?"
                />
            </div>
        @endif
    </div>
</article>
