@php
    use App\Enums\FulfillmentType;

    $isInKitchenProcess = $order->status->isInKitchenProcess();
    $isDelivery = $order->fulfillment_type === FulfillmentType::Delivery;
@endphp

<article
    wire:key="dashboard-order-card-{{ $order->id }}"
    @class([
        'dashboard-order-card',
        'dashboard-order-card-active' => $isInKitchenProcess,
    ])
>
  <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-lg font-bold text-dashboard-text">{{ $this->orderDisplayId($order) }}</h2>
                <time class="text-base text-dashboard-muted" datetime="{{ $order->created_at?->toIso8601String() }}">
                    {{ $order->created_at?->format('H:i') }}
                </time>
            </div>
        </div>

        <span @class([
            'inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-semibold',
            'bg-dashboard-primary-soft text-dashboard-primary' => $isDelivery,
            'bg-dashboard-muted-bg text-dashboard-muted' => ! $isDelivery,
        ])>
            @if ($isDelivery)
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 18h14M7 14h10l1-8H6l1 8zM9 6V4h6v2" /></svg>
            @else
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            @endif
            {{ $order->fulfillment_type->label() }}
        </span>
    </div>

    <div class="mt-4 flex items-start gap-2 text-base text-dashboard-muted">
        <svg class="mt-0.5 size-5 shrink-0 text-dashboard-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <div class="min-w-0">
            <p class="font-semibold text-dashboard-text">{{ $order->customer_name }}</p>
            <p class="mt-0.5 break-words">
                @if ($isDelivery && $order->address)
                    {{ $order->address }}
                @else
                    {{ $order->fulfillment_type->label() }}
                @endif
            </p>
        </div>
    </div>

    @if ($order->notes)
        <div class="mt-4 flex items-start gap-2 rounded-xl bg-dashboard-primary-soft px-4 py-3 text-base text-dashboard-primary">
            <svg class="mt-0.5 size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <p class="min-w-0 break-words">{{ $order->notes }}</p>
        </div>
    @endif

    <div class="mt-auto flex flex-col gap-4 pt-5 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xl font-bold tabular-nums text-dashboard-text">
            {{ number_format((float) $order->total, 0, ',', '.') }} din
        </p>

        @include('livewire.dashboard.partials.order-actions', ['order' => $order, 'compact' => true])
    </div>
</article>
