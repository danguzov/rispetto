<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Porudžbine' }} — Rispetto</title>

    @include('partials.favicon')

    @include('partials.site-display-fonts')

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dashboard.js'])
    @livewireStyles

    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="dashboard-shell">
    @auth
        <header class="sticky top-0 z-40 border-b border-dashboard-border bg-white">
            <div class="mx-auto flex min-h-[4.5rem] max-w-[120rem] items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    @if (request()->routeIs('dashboard.orders.index', 'dashboard.orders.show'))
                        <button
                            type="button"
                            class="inline-flex min-h-12 min-w-12 items-center justify-center rounded-xl border border-dashboard-border text-dashboard-muted lg:hidden"
                            x-data
                            @click="$dispatch('toggle-dashboard-sidebar')"
                            aria-label="Otvori meni"
                        >
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    @endif

                    <a href="{{ route('dashboard.orders.index') }}" class="shrink-0" wire:navigate>
                        @php
                            $dashboardLogo = config('site.dashboard_logo', 'images/site/RISPETTO.svg');
                        @endphp
                        @if (file_exists(public_path($dashboardLogo)))
                            <img
                                src="{{ asset($dashboardLogo) }}"
                                alt="Rispetto"
                                class="h-8 w-auto sm:h-9"
                                width="{{ config('site.dashboard_logo_width', 144) }}"
                                height="{{ config('site.dashboard_logo_height', 24) }}"
                            />
                        @else
                            <span class="font-dashboard-display text-2xl font-bold tracking-wide text-dashboard-primary sm:text-3xl">
                                RISPETTO
                            </span>
                        @endif
                    </a>
                </div>

                <div class="flex items-center gap-2 sm:gap-4">
                    <livewire:dashboard.header-revenue />

                    <a
                        href="{{ route('dashboard.orders.index', ['filter' => 'pending']) }}"
                        class="relative inline-flex min-h-12 min-w-12 items-center justify-center rounded-xl text-dashboard-muted transition-colors hover:bg-dashboard-muted-bg hover:text-dashboard-text"
                        wire:navigate
                        aria-label="Obaveštenja"
                    >
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if (($pendingNotifications = \App\Support\OrderingMetrics::pendingCount()) > 0)
                            <span class="absolute right-2 top-2 size-2.5 rounded-full bg-orange-500 ring-2 ring-white" aria-hidden="true"></span>
                            <span class="sr-only">{{ $pendingNotifications }} novih narudžbina</span>
                        @endif
                    </a>

                    <div class="hidden h-8 w-px bg-dashboard-border sm:block" aria-hidden="true"></div>

                    <div class="relative" x-data="{ open: false }">
                        <button
                            type="button"
                            class="flex min-h-12 max-w-[16rem] items-center gap-3 rounded-xl px-2 py-2 text-left transition-colors hover:bg-dashboard-muted-bg sm:max-w-none sm:px-3"
                            @click="open = ! open"
                            @click.outside="open = false"
                            aria-expanded="false"
                        >
                            <div class="hidden min-w-0 sm:block">
                                <p class="truncate text-sm font-semibold text-dashboard-text">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-dashboard-muted">{{ auth()->user()->email }}</p>
                            </div>
                            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg bg-dashboard-primary text-sm font-bold text-white">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <svg class="hidden size-4 shrink-0 text-dashboard-muted sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            x-transition
                            class="absolute right-0 z-50 mt-2 w-56 rounded-xl border border-dashboard-border bg-white p-2 shadow-lg"
                        >
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dashboard-btn-ghost w-full justify-start px-4">
                                    Odjava
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    @endauth

    <main @class([
        'mx-auto w-full max-w-[120rem]',
        'px-4 py-6 sm:px-6 lg:px-8' => auth()->check(),
        'flex min-h-screen items-center justify-center px-4 py-8' => ! auth()->check(),
    ])>
        @auth
            @include('livewire.dashboard.partials.kitchen-alarm')
        @endauth

        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
