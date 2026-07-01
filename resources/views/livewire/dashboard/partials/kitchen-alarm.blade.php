@php
    use App\Support\OrderingMetrics;

    $kitchenPendingCount = OrderingMetrics::pendingCount();
@endphp

<div
    x-data="createKitchenAlarm()"
    x-init="
        const pendingCount = (event) => {
            if (event == null) {
                return 0;
            }

            if (typeof event === 'number' || typeof event === 'string') {
                return Number(event) || 0;
            }

            return Number(event.pendingCount ?? event.detail?.pendingCount ?? 0) || 0;
        };

        const sync = (event) => syncPendingAlarm(pendingCount(event));

        sync(@js($kitchenPendingCount));

        Livewire.on('kitchen-pending-updated', (event) => {
            sync(event);
        });

        Livewire.on('kitchen-new-order', (event) => {
            sync(event);
            if (pendingCount(event) > 0) {
                playKitchenBeep();
            }
        });
    "
    @destroy="stopPendingAlarm()"
    @click="unlockAudio()"
    @keydown.window="unlockAudio()"
    wire:ignore
>
    <div
        x-show="! audioUnlocked"
        x-cloak
        class="mb-4 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-base text-amber-900"
    >
        Dodirnite ekran da uključite zvučno obaveštenje za nove porudžbine.
    </div>
</div>
