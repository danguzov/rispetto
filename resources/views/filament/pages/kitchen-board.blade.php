<x-filament-panels::page wire:poll.15s="refreshOrders">
    <div
        class="space-y-6"
        x-data="{
            alarmInterval: null,
            syncPendingAlarm(count) {
                const pending = Number(count) || 0;

                if (pending > 0) {
                    if (! this.alarmInterval) {
                        this.playKitchenBeep();
                        this.alarmInterval = window.setInterval(() => this.playKitchenBeep(), 4000);
                    }

                    return;
                }

                this.stopPendingAlarm();
            },
            stopPendingAlarm() {
                if (this.alarmInterval) {
                    window.clearInterval(this.alarmInterval);
                    this.alarmInterval = null;
                }
            },
            playKitchenBeep() {
                try {
                    const context = new (window.AudioContext || window.webkitAudioContext)();
                    const gain = context.createGain();
                    gain.gain.value = 0.1;
                    gain.connect(context.destination);

                    const playTone = (frequency, start, duration) => {
                        const oscillator = context.createOscillator();
                        oscillator.type = 'sine';
                        oscillator.frequency.value = frequency;
                        oscillator.connect(gain);
                        oscillator.start(context.currentTime + start);
                        oscillator.stop(context.currentTime + start + duration);
                    };

                    playTone(880, 0, 0.18);
                    playTone(1100, 0.22, 0.18);
                } catch (error) {
                    console.warn('Zvuk nije dostupan.', error);
                }
            },
            destroy() {
                this.stopPendingAlarm();
            },
        }"
        x-init="syncPendingAlarm(@js($this->pendingCount))"
        x-effect="syncPendingAlarm($wire.pendingCount)"
    >
        @if (! $this->acceptingOrders)
            <x-filament::section icon="heroicon-o-pause-circle" icon-color="warning">
                <x-slot name="heading">
                    Ne primamo nove porudžbine
                </x-slot>

                <x-slot name="description">
                    Aktivne porudžbine možete i dalje obraditi. Uključite prijem na pregledu kada budete spremni.
                </x-slot>
            </x-filament::section>
        @endif

        @php
            $columns = [
                \App\Enums\OrderStatus::Pending,
                \App\Enums\OrderStatus::Accepted,
                \App\Enums\OrderStatus::Preparing,
                \App\Enums\OrderStatus::Ready,
                \App\Enums\OrderStatus::OutForDelivery,
            ];
        @endphp

        <div class="flex gap-4 overflow-x-auto pb-2 snap-x snap-mandatory">
            @foreach ($columns as $columnStatus)
                @php
                    $columnOrders = $this->activeOrders->where('status', $columnStatus);
                    $isPendingColumn = $columnStatus === \App\Enums\OrderStatus::Pending;
                    $hasPendingOrders = $isPendingColumn && $columnOrders->isNotEmpty();
                @endphp

                <div class="w-[22rem] min-w-[22rem] shrink-0 snap-start">
                    <x-filament::section
                        :compact="true"
                        class="h-full {{ $hasPendingOrders ? 'ring-2 ring-warning-500/40' : '' }}"
                    >
                        <x-slot name="heading">
                            <div class="flex w-full items-center justify-between gap-2">
                                <span @class(['font-semibold' => $hasPendingOrders])>
                                    {{ $this->statusColumnLabel($columnStatus) }}
                                </span>
                                <x-filament::badge :color="$columnStatus->filamentColor()">
                                    {{ $columnOrders->count() }}
                                </x-filament::badge>
                            </div>
                        </x-slot>

                        <div class="space-y-3">
                            @forelse ($columnOrders as $order)
                                @include('filament.pages.kitchen-board.order-card', ['order' => $order])
                            @empty
                                <div class="rounded-xl border border-dashed border-gray-200 p-4 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                    Nema porudžbina
                                </div>
                            @endforelse
                        </div>
                    </x-filament::section>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
