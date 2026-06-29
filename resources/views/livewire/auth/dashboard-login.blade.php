<div class="mx-auto w-full max-w-md space-y-6">
    <div class="space-y-2 text-center">
        <p class="font-dashboard-display text-3xl font-bold text-dashboard-primary">RISPETTO</p>
        <h1 class="text-2xl font-bold text-dashboard-text">Prijava</h1>
        <p class="text-base text-dashboard-muted">Pristupite dashboardu za praćenje narudžbina.</p>
    </div>

    <form wire:submit="login" class="dashboard-card space-y-5 p-6">
        <div class="space-y-2">
            <label for="email" class="block text-base font-medium text-dashboard-text">Email</label>
            <input
                id="email"
                type="email"
                wire:model="email"
                autocomplete="email"
                required
                class="dashboard-input"
            >
            @error('email')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="password" class="block text-base font-medium text-dashboard-text">Lozinka</label>
            <input
                id="password"
                type="password"
                wire:model="password"
                autocomplete="current-password"
                required
                class="dashboard-input"
            >
            @error('password')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex min-h-12 items-center gap-3 text-base text-dashboard-text">
            <input type="checkbox" wire:model="remember" class="size-5 rounded border-dashboard-border text-dashboard-primary focus:ring-dashboard-primary/30">
            Zapamti me
        </label>

        <button
            type="submit"
            class="dashboard-btn-primary w-full"
            wire:loading.attr="disabled"
        >
            Prijavi se
        </button>
    </form>
</div>
