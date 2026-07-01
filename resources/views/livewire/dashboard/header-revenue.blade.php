<div
    wire:poll.3s="refreshRevenue"
    class="hidden min-h-12 items-center gap-3 rounded-xl border border-dashboard-border bg-dashboard-muted-bg px-4 py-2 sm:flex"
>
    <div class="min-w-0 text-right">
        <p class="text-xs font-medium uppercase tracking-wide text-dashboard-muted">Prihod danas</p>
        <p class="text-base font-bold tabular-nums text-dashboard-text">
            {{ number_format($revenueToday, 0, ',', '.') }} din
        </p>
    </div>
</div>
