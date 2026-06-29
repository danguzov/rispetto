@php
    use App\Enums\OrderStatus;
@endphp

<div class="space-y-6" wire:poll.3s="refreshOrder">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('dashboard.orders.index') }}" class="inline-flex min-h-12 items-center gap-2 text-base font-medium text-dashboard-muted hover:text-dashboard-primary" wire:navigate>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Nazad na listu
            </a>
            <h1 class="mt-2 text-3xl font-bold text-dashboard-text">{{ $this->orderDisplayId($order) }}</h1>
            <p class="mt-1 text-base text-dashboard-muted">
                {{ $order->order_number }} · {{ $order->created_at?->format('d.m.Y H:i') }}
            </p>
        </div>

        <span @class([
            'inline-flex min-h-12 items-center rounded-xl px-4 text-base font-semibold',
            'bg-dashboard-primary text-white' => $order->status === OrderStatus::Completed,
            'bg-dashboard-primary-soft text-dashboard-primary' => $order->status->isInKitchenProcess(),
            'bg-dashboard-muted-bg text-dashboard-muted' => in_array($order->status, [OrderStatus::Pending, OrderStatus::Cancelled], true),
        ])>
            {{ $order->status->dashboardLabel() }}
        </span>
    </div>

    @if (session('dashboard_message'))
        <div class="rounded-2xl border border-dashboard-primary/30 bg-dashboard-primary-soft px-4 py-3 text-base text-dashboard-primary">
            {{ session('dashboard_message') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <div class="space-y-6">
            <section class="dashboard-card p-6">
                <h2 class="text-xl font-bold text-dashboard-text">Podaci kupca</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-dashboard-muted">Ime</dt>
                        <dd class="mt-1 text-base font-semibold text-dashboard-text">{{ $order->customer_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-dashboard-muted">Telefon</dt>
                        <dd class="mt-1 font-mono text-base">{{ $order->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-dashboard-muted">Email</dt>
                        <dd class="mt-1 text-base">{{ $order->email ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-dashboard-muted">Način</dt>
                        <dd class="mt-1 text-base">{{ $order->fulfillment_type->label() }}</dd>
                    </div>
                    @if ($order->address)
                        <div class="sm:col-span-2">
                            <dt class="text-sm text-dashboard-muted">Adresa</dt>
                            <dd class="mt-1 text-base">{{ $order->address }}</dd>
                        </div>
                    @endif
                    @if ($order->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-sm text-dashboard-muted">Napomena</dt>
                            <dd class="mt-2 rounded-xl bg-dashboard-primary-soft px-4 py-3 text-base text-dashboard-primary">{{ $order->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            <section class="dashboard-card p-6">
                <h2 class="text-xl font-bold text-dashboard-text">Stavke</h2>
                <ul class="mt-4 divide-y divide-dashboard-border">
                    @foreach ($order->items as $item)
                        <li class="flex items-start justify-between gap-4 py-4 text-base">
                            <div>
                                <p class="font-semibold text-dashboard-text">{{ $item->quantity }}× {{ $item->name }}</p>
                                @if ($item->variant_label)
                                    <p class="text-dashboard-muted">{{ $item->variant_label }}</p>
                                @endif
                            </div>
                            <p class="font-semibold tabular-nums">{{ number_format((float) $item->line_total, 0, ',', '.') }} din</p>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-4 border-t border-dashboard-border pt-4 text-xl font-bold tabular-nums text-dashboard-text">
                    Ukupno: {{ number_format((float) $order->total, 0, ',', '.') }} din
                </p>
            </section>

            <section class="dashboard-card p-6">
                <h2 class="text-xl font-bold text-dashboard-text">Istorija statusa</h2>
                <ol class="mt-4 space-y-3">
                    @foreach ($order->statusLogs->sortBy('created_at') as $log)
                        <li class="rounded-xl bg-dashboard-muted-bg px-4 py-3 text-base">
                            <p class="font-semibold text-dashboard-text">
                                {{ $log->from_status?->label() ?? '—' }}
                                →
                                {{ $log->to_status->label() }}
                            </p>
                            <p class="mt-1 text-sm text-dashboard-muted">{{ $log->created_at?->format('d.m.Y H:i') }}</p>
                            @if ($log->note)
                                <p class="mt-1 text-dashboard-text">{{ $log->note }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="dashboard-card p-6">
                <h2 class="text-xl font-bold text-dashboard-text">Operacije</h2>

                <p class="mt-3 text-base font-medium text-dashboard-primary">
                    {{ $this->readyCountdownLabel($order) }}
                </p>

                @if ($this->isAutoStatusEnabled() && $order->status !== OrderStatus::Pending)
                    <div class="mt-3 space-y-1 text-sm text-dashboard-muted">
                        @if ($label = $this->preparingDueLabel($order))
                            <p>{{ $label }}</p>
                        @endif
                        @if ($label = $this->readyDueLabel($order))
                            <p>{{ $label }}</p>
                        @endif
                        @if ($label = $this->completeDueLabel($order))
                            <p>{{ $label }}</p>
                        @endif
                    </div>
                @endif

                <div class="mt-4">
                    @include('livewire.dashboard.partials.order-actions', ['order' => $order, 'compact' => false])
                </div>
            </section>

            <section class="dashboard-card p-6">
                <h2 class="text-xl font-bold text-dashboard-text">Ručna promena statusa</h2>
                <form wire:submit="updateStatusManually" class="mt-4 space-y-4">
                    <div>
                        <label for="adminStatus" class="block text-sm font-medium text-dashboard-muted">Status</label>
                        <select id="adminStatus" wire:model="adminStatus" class="dashboard-input mt-2">
                            @foreach (OrderStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->dashboardLabel() }}</option>
                            @endforeach
                        </select>
                        @error('adminStatus')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="adminNote" class="block text-sm font-medium text-dashboard-muted">Napomena</label>
                        <textarea id="adminNote" wire:model="adminNote" rows="3" class="dashboard-input mt-2"></textarea>
                    </div>

                    <button type="submit" class="dashboard-btn-primary w-full">
                        Sačuvaj status
                    </button>
                </form>
            </section>

            <section class="dashboard-card p-6">
                <h2 class="text-xl font-bold text-dashboard-text">Link za kupca</h2>
                <p class="mt-3 break-all text-sm text-dashboard-muted">
                    {{ route('order.tracking', $order->tracking_token) }}
                </p>
            </section>
        </aside>
    </div>
</div>
