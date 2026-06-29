@php
    use App\Enums\FulfillmentType;
    use App\Enums\OrderStatus;

    $sidebarItems = [
        'today' => ['label' => 'Sve narudžbine', 'icon' => 'grid'],
        'pending' => ['label' => 'Čeka potvrdu', 'icon' => 'clock'],
        'preparing' => ['label' => 'U pripremi', 'icon' => 'pot'],
        'delivery' => ['label' => 'U dostavi', 'icon' => 'bike'],
        'pickup' => ['label' => 'Lično preuzimanje', 'icon' => 'walk'],
        'completed' => ['label' => 'Isporučeno', 'icon' => 'check'],
        'cancelled' => ['label' => 'Odbijeno', 'icon' => 'x'],
    ];
@endphp

<div
    class="space-y-5 lg:space-y-6"
    wire:poll.3s="refreshOrders"
    x-data="createKitchenAlarm()"
    x-init="
        syncPendingAlarm(@js($pendingCount));
        $wire.on('kitchen-pending-updated', (event) => {
            const count = event?.pendingCount ?? $wire.pendingCount;
            syncPendingAlarm(count);
            if (Number(count) > 0) playKitchenBeep();
        });
        $wire.on('kitchen-new-order', () => {
            syncPendingAlarm($wire.pendingCount);
            playKitchenBeep();
        });
    "
    x-effect="syncPendingAlarm($wire.pendingCount)"
    @toggle-dashboard-sidebar.window="sidebarOpen = ! sidebarOpen"
    @click="unlockAudio()"
    @keydown.window="unlockAudio()"
>
    <div
        x-show="! audioUnlocked"
        x-cloak
        class="rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-base text-amber-900"
    >
        Dodirnite ekran da uključite zvučno obaveštenje za nove porudžbine.
    </div>

    @if (session('dashboard_message'))
        <div class="rounded-2xl border border-dashboard-primary/30 bg-dashboard-primary-soft px-4 py-3 text-base text-dashboard-primary">
            {{ session('dashboard_message') }}
        </div>
    @endif

    {{-- Metrike --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5 xl:gap-4">
        <div class="dashboard-metric-card">
            <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl bg-dashboard-muted-bg text-dashboard-primary">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
            </span>
            <div>
                <p class="text-2xl font-bold tabular-nums text-dashboard-text">{{ $stats['today'] }}</p>
                <p class="text-sm text-dashboard-muted">Narudžbine danas</p>
            </div>
        </div>
        <div class="dashboard-metric-card">
            <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl bg-dashboard-muted-bg text-dashboard-primary">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </span>
            <div>
                <p class="text-2xl font-bold tabular-nums text-dashboard-text">{{ $stats['pending'] }}</p>
                <p class="text-sm text-dashboard-muted">Čeka potvrdu</p>
            </div>
        </div>
        <div class="dashboard-metric-card">
            <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl bg-dashboard-muted-bg text-dashboard-primary">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 18h14M7 14h10l1-8H6l1 8zM9 6V4h6v2" /></svg>
            </span>
            <div>
                <p class="text-2xl font-bold tabular-nums text-dashboard-text">{{ $stats['delivery'] }}</p>
                <p class="text-sm text-dashboard-muted">U dostavi</p>
            </div>
        </div>
        <div class="dashboard-metric-card">
            <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl bg-dashboard-muted-bg text-dashboard-primary">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            </span>
            <div>
                <p class="text-2xl font-bold tabular-nums text-dashboard-text">{{ $stats['pickup'] }}</p>
                <p class="text-sm text-dashboard-muted">Lično preuzimanje</p>
            </div>
        </div>
        <div class="dashboard-metric-card col-span-2 md:col-span-1">
            <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl bg-dashboard-muted-bg text-dashboard-primary">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            </span>
            <div class="min-w-0">
                <p class="truncate text-xl font-bold tabular-nums text-dashboard-text sm:text-2xl">{{ number_format($stats['revenue_today'], 0, ',', '.') }} din</p>
                <p class="text-sm text-dashboard-muted">Prihod danas</p>
            </div>
        </div>
    </div>

    @if ($stats['pending'] > 0)
        <div class="flex flex-col gap-2 rounded-2xl border border-amber-200 bg-dashboard-warning-bg px-4 py-3 text-dashboard-warning-text sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <p class="text-base font-medium">
                <span class="mr-1 inline-block size-2 rounded-full bg-orange-500 align-middle" aria-hidden="true"></span>
                {{ $stats['pending'] }} {{ $stats['pending'] === 1 ? 'nova narudžbina čeka potvrdu' : 'nove narudžbine čekaju potvrdu' }}
            </p>
            <p class="text-sm italic text-dashboard-warning-text/80">
                Narudžbine starije od 5 minuta bez potvrde se automatski otkazuju
            </p>
        </div>
    @endif

    <div class="flex flex-col gap-5 lg:flex-row lg:gap-6">
        {{-- Overlay --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-40 bg-black/40 lg:hidden"
            @click="sidebarOpen = false"
            aria-hidden="true"
        ></div>

        {{-- Sidebar --}}
        <aside
            class="dashboard-card fixed bottom-0 left-0 top-[4.5rem] z-50 flex w-[min(100%,18rem)] -translate-x-full flex-col overflow-y-auto p-4 shadow-xl transition-transform duration-200 lg:static lg:top-auto lg:z-auto lg:h-auto lg:w-64 lg:shrink-0 lg:translate-x-0 lg:shadow-sm"
            :class="{ 'translate-x-0': sidebarOpen }"
        >
            <div class="mb-4 flex items-center justify-between lg:hidden">
                <p class="text-sm font-semibold uppercase tracking-wide text-dashboard-muted">Meni</p>
                <button type="button" class="inline-flex min-h-12 min-w-12 items-center justify-center rounded-xl text-dashboard-muted" @click="sidebarOpen = false" aria-label="Zatvori meni">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <nav class="space-y-1" aria-label="Filteri narudžbina">
                <p class="mb-2 hidden px-4 text-xs font-semibold uppercase tracking-wide text-dashboard-muted lg:block">Meni</p>
                @foreach ($sidebarItems as $key => $item)
                    <button
                        type="button"
                        wire:click="setStatusFilter('{{ $key }}')"
                        @class([
                            'dashboard-sidebar-link',
                            'dashboard-sidebar-link-active' => $statusFilter === $key,
                        ])
                    >
                        <span>{{ $item['label'] }}</span>
                        <span @class([
                            'dashboard-sidebar-badge',
                            'bg-white/20 text-white' => $statusFilter === $key,
                            'bg-dashboard-muted-bg text-dashboard-muted' => $statusFilter !== $key,
                        ])>
                            {{ $sidebarCounts[$key] }}
                        </span>
                    </button>
                @endforeach
            </nav>

            <div class="mt-6 border-t border-dashboard-border pt-5">
                <p class="mb-3 px-4 text-xs font-semibold uppercase tracking-wide text-dashboard-muted">Arhiva</p>
                <a
                    href="{{ route('dashboard.orders.history') }}"
                    class="dashboard-sidebar-link"
                    wire:navigate
                >
                    <span>Istorija porudžbina</span>
                    <svg class="size-5 text-dashboard-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </a>
            </div>

            <div class="mt-6 border-t border-dashboard-border pt-5">
                <p class="mb-3 px-4 text-xs font-semibold uppercase tracking-wide text-dashboard-muted">Brze akcije</p>
                <button
                    type="button"
                    wire:click="toggleAcceptingOrders"
                    @class([
                        'dashboard-btn-warning w-full justify-center gap-2',
                        'border-red-300 text-red-700 hover:bg-red-50' => ! $this->acceptingOrders(),
                    ])
                >
                    <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        @if ($this->acceptingOrders())
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        @endif
                    </svg>
                    {{ $this->acceptingOrders() ? 'Pauziraj dostavu' : 'Uključi dostavu' }}
                </button>
            </div>
        </aside>

        {{-- Main content --}}
        <section class="min-w-0 flex-1 space-y-4">
            <h1 class="text-2xl font-bold text-dashboard-text">{{ $this->filterLabel() }}</h1>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                @forelse ($orders as $order)
                    @include('livewire.dashboard.partials.order-card', ['order' => $order])
                @empty
                    <div class="dashboard-card col-span-full px-6 py-16 text-center">
                        <p class="text-lg font-medium text-dashboard-text">Nema narudžbina</p>
                        <p class="mt-2 text-base text-dashboard-muted">Za izabrani filter trenutno nema porudžbina.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
