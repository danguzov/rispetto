@php
    use App\Enums\OrderStatus;

    $compact = $compact ?? false;
    $isPending = $order->status === OrderStatus::Pending;
@endphp

<div @class([
    'flex flex-wrap items-center gap-2',
    'justify-end' => $compact,
])>
    @if ($isPending)
        <button
            type="button"
            wire:click="cancelOrder({{ $order->id }})"
            wire:confirm="Odbiti porudžbinu?"
            class="dashboard-btn-danger px-4"
        >
            Odbij
        </button>

        <div class="relative" x-data="{ open: false }">
            <button
                type="button"
                class="dashboard-btn-primary gap-1 px-4"
                @click="open = ! open"
            >
                Prihvati
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>

            <div
                x-show="open"
                x-cloak
                @click.outside="open = false"
                x-transition
                class="absolute bottom-full right-0 z-20 mb-2 w-[min(100vw-2rem,16rem)] rounded-xl border border-dashboard-border bg-white p-3 shadow-lg"
            >
                <p class="mb-2 text-sm font-medium text-dashboard-muted">Vreme pripreme</p>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($this->etaPresets() as $preset)
                        <button
                            type="button"
                            wire:click="acceptOrder({{ $order->id }}, {{ $preset }})"
                            @click="open = false"
                            class="min-h-12 rounded-lg bg-dashboard-muted-bg px-2 text-sm font-semibold text-dashboard-text hover:bg-dashboard-primary-soft hover:text-dashboard-primary"
                        >
                            {{ $preset }}′
                        </button>
                    @endforeach
                </div>
                <div class="mt-2 flex gap-2">
                    <input
                        type="number"
                        min="5"
                        max="180"
                        wire:model.live="customEtaByOrder.{{ $order->id }}"
                        placeholder="{{ $this->customEtaFor($order) }}"
                        class="dashboard-input min-h-12 py-2 text-sm"
                    >
                    <button
                        type="button"
                        wire:click="acceptOrderWithCustomEta({{ $order->id }})"
                        @click="open = false"
                        class="dashboard-btn-primary shrink-0 px-4"
                    >
                        OK
                    </button>
                </div>
            </div>
        </div>
    @else
        <span @class([
            'inline-flex min-h-12 items-center rounded-xl px-4 text-sm font-semibold',
            'bg-dashboard-primary text-white' => $order->status === OrderStatus::Completed,
            'bg-dashboard-primary-soft text-dashboard-primary' => in_array($order->status, [OrderStatus::Accepted, OrderStatus::Preparing, OrderStatus::Ready, OrderStatus::OutForDelivery], true),
            'bg-dashboard-muted-bg text-dashboard-muted' => $order->status === OrderStatus::Cancelled,
        ])>
            {{ $order->status->dashboardLabel() }}
        </span>

        @unless ($compact)
            @if ($order->status === OrderStatus::Accepted)
                <button type="button" wire:click="markPreparing({{ $order->id }})" class="dashboard-btn-ghost px-4 text-sm">U pripremi</button>
            @endif
            @if ($order->status === OrderStatus::Preparing)
                <button type="button" wire:click="markReady({{ $order->id }})" class="dashboard-btn-primary px-4 text-sm">Spremno</button>
            @endif
            @if ($order->status === OrderStatus::Ready)
                @if ($this->isDelivery($order))
                    <button type="button" wire:click="markOutForDelivery({{ $order->id }})" class="dashboard-btn-ghost px-4 text-sm">Na putu</button>
                @endif
                <button type="button" wire:click="completeOrder({{ $order->id }})" class="dashboard-btn-primary px-4 text-sm">Završeno</button>
            @endif
            @if ($order->status === OrderStatus::OutForDelivery)
                <button type="button" wire:click="completeOrder({{ $order->id }})" class="dashboard-btn-primary px-4 text-sm">Završeno</button>
            @endif
        @endunless
    @endif

    <a
        href="{{ route('dashboard.orders.show', $order) }}"
        class="dashboard-btn-ghost px-4"
        wire:navigate
    >
        Detalji
    </a>
</div>
