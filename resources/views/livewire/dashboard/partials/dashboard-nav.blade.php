@php
    $onOrders = request()->routeIs('dashboard.orders.index', 'dashboard.orders.show');
    $onHistory = request()->routeIs('dashboard.orders.history');
@endphp

<nav class="flex flex-wrap gap-2" aria-label="Dashboard navigacija">
    <a
        href="{{ route('dashboard.orders.index') }}"
        wire:navigate
        @class([
            'dashboard-btn min-h-12 px-4 text-sm',
            'dashboard-btn-primary' => $onOrders,
            'dashboard-btn-ghost' => ! $onOrders,
        ])
    >
        Porudžbine
    </a>
    <a
        href="{{ route('dashboard.orders.history') }}"
        wire:navigate
        @class([
            'dashboard-btn min-h-12 px-4 text-sm',
            'dashboard-btn-primary' => $onHistory,
            'dashboard-btn-ghost' => ! $onHistory,
        ])
    >
        Istorija
    </a>
</nav>
