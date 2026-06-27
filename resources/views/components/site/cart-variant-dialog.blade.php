@if (config('ordering.enabled', true))
    <dialog class="site-cart-dialog" data-cart-variant-dialog aria-labelledby="cart-variant-dialog-title">
        <div class="site-cart-dialog__panel">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="site-eyebrow">Izaberite veličinu</p>
                    <h2 id="cart-variant-dialog-title" class="site-heading mt-2 text-2xl" data-cart-variant-title></h2>
                </div>
                <button type="button" class="inline-flex min-h-12 min-w-12 items-center justify-center rounded-xl border border-rispetto-border text-rispetto-text transition hover:border-rispetto-accent/40 hover:text-rispetto-accent" data-cart-variant-close aria-label="Zatvori">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-6 grid gap-3" data-cart-variant-options></div>
        </div>
    </dialog>

    <div class="site-cart-toast" data-cart-toast role="status" aria-live="polite" hidden></div>
@endif
