@php
    use App\Enums\OrderStatus;
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-dashboard-text">Istorija porudžbina</h1>
            <p class="mt-1 text-base text-dashboard-muted">Pregled završenih i svih prošlih narudžbina po mesecima i danima.</p>
        </div>

        @include('livewire.dashboard.partials.dashboard-nav')
    </div>

    <section class="dashboard-card p-5 sm:p-6">
        <h2 class="text-lg font-bold text-dashboard-text">Meseci</h2>

        @if ($months === [])
            <p class="mt-4 text-base text-dashboard-muted">Još nema sačuvanih porudžbina u istoriji.</p>
        @else
            <div class="mt-4 flex flex-wrap gap-3">
                @foreach ($months as $month)
                    <button
                        type="button"
                        wire:click="selectMonth('{{ $month['key'] }}')"
                        wire:key="history-month-{{ $month['key'] }}"
                        @class([
                            'inline-flex min-h-12 flex-col items-start justify-center rounded-xl border px-4 py-3 text-left transition-colors',
                            'border-dashboard-primary bg-dashboard-primary text-white' => $selectedMonth === $month['key'],
                            'border-dashboard-border bg-white text-dashboard-text hover:border-dashboard-primary/40 hover:bg-dashboard-primary-soft' => $selectedMonth !== $month['key'],
                        ])
                    >
                        <span class="text-base font-semibold capitalize">{{ $month['label'] }}</span>
                        <span @class([
                            'text-sm tabular-nums',
                            'text-white/80' => $selectedMonth === $month['key'],
                            'text-dashboard-muted' => $selectedMonth !== $month['key'],
                        ])>
                            {{ $month['count'] }} {{ $month['count'] === 1 ? 'narudžbina' : 'narudžbine' }}
                        </span>
                    </button>
                @endforeach
            </div>
        @endif
    </section>

    @if ($selectedMonth !== '' && $months !== [])
        <section class="dashboard-card p-5 sm:p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold capitalize text-dashboard-text">
                        {{ \App\Support\OrderHistory::monthLabel($selectedMonth) }}
                    </h2>
                    <p class="mt-1 text-base text-dashboard-muted">
                        {{ $monthSummary['orders'] }} narudžbina u {{ $monthSummary['days'] }} {{ $monthSummary['days'] === 1 ? 'danu' : 'dana' }}
                    </p>
                </div>
            </div>

            @if ($days === [])
                <p class="mt-4 text-base text-dashboard-muted">Za izabrani mesec nema porudžbina.</p>
            @else
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($days as $day)
                        <button
                            type="button"
                            wire:click="selectDay('{{ $day['key'] }}')"
                            wire:key="history-day-{{ $day['key'] }}"
                            @class([
                                'inline-flex min-h-12 items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition-colors',
                                'border-dashboard-primary bg-dashboard-primary text-white' => $selectedDay === $day['key'],
                                'border-dashboard-border bg-dashboard-muted-bg text-dashboard-text hover:border-dashboard-primary/40 hover:bg-dashboard-primary-soft' => $selectedDay !== $day['key'],
                            ])
                        >
                            <span>{{ $day['label'] }}</span>
                            <span @class([
                                'rounded-full px-2 py-0.5 text-xs tabular-nums',
                                'bg-white/20 text-white' => $selectedDay === $day['key'],
                                'bg-white text-dashboard-muted' => $selectedDay !== $day['key'],
                            ])>
                                {{ $day['count'] }}
                            </span>
                        </button>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    @if ($selectedDay !== '')
        <section class="dashboard-card overflow-hidden">
            <div class="border-b border-dashboard-border px-5 py-4 sm:px-6">
                <h2 class="text-lg font-bold text-dashboard-text">
                    {{ \App\Support\OrderHistory::dayHeading($selectedDay) }}
                </h2>
                <p class="mt-1 text-base text-dashboard-muted">{{ $orders->count() }} porudžbina</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-base">
                    <thead class="border-b border-dashboard-border bg-dashboard-muted-bg text-sm font-semibold text-dashboard-muted">
                        <tr>
                            <th class="px-4 py-3 font-semibold sm:px-6">Porudžbina</th>
                            <th class="px-4 py-3 font-semibold sm:px-6">Vreme</th>
                            <th class="px-4 py-3 font-semibold sm:px-6">Kupac</th>
                            <th class="hidden px-4 py-3 font-semibold md:table-cell sm:px-6">Način</th>
                            <th class="px-4 py-3 font-semibold sm:px-6">Status</th>
                            <th class="px-4 py-3 font-semibold sm:px-6">Ukupno</th>
                            <th class="px-4 py-3 font-semibold sm:px-6"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dashboard-border">
                        @forelse ($orders as $order)
                            <tr wire:key="history-order-{{ $order->id }}" class="hover:bg-dashboard-muted-bg/60">
                                <td class="px-4 py-4 font-semibold text-dashboard-text sm:px-6">
                                    {{ $this->orderDisplayId($order) }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-dashboard-muted sm:px-6">
                                    {{ $order->created_at?->format('H:i') }}
                                </td>
                                <td class="px-4 py-4 sm:px-6">
                                    <p class="font-medium text-dashboard-text">{{ $order->customer_name }}</p>
                                    <p class="text-sm text-dashboard-muted">{{ $order->phone }}</p>
                                </td>
                                <td class="hidden px-4 py-4 text-dashboard-muted md:table-cell sm:px-6">
                                    {{ $order->fulfillment_type->label() }}
                                </td>
                                <td class="px-4 py-4 sm:px-6">
                                    <span @class([
                                        'inline-flex min-h-10 items-center rounded-xl px-3 text-sm font-semibold',
                                        'bg-dashboard-primary text-white' => $order->status === OrderStatus::Completed,
                                        'bg-dashboard-primary-soft text-dashboard-primary' => $order->status->isInKitchenProcess(),
                                        'bg-dashboard-muted-bg text-dashboard-muted' => in_array($order->status, [OrderStatus::Pending, OrderStatus::Cancelled], true),
                                    ])>
                                        {{ $order->status->dashboardLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 font-semibold tabular-nums text-dashboard-text sm:px-6">
                                    {{ number_format((float) $order->total, 0, ',', '.') }} din
                                </td>
                                <td class="px-4 py-4 sm:px-6">
                                    <a
                                        href="{{ route('dashboard.orders.show', $order) }}"
                                        class="dashboard-btn-ghost px-4 py-2 text-sm"
                                        wire:navigate
                                    >
                                        Detalji
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-dashboard-muted">
                                    Nema porudžbina za izabrani dan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>
