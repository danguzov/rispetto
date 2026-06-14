<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Preuzimanje QR kodova
            </x-slot>

            <x-slot name="description">
                SVG format — spreman za štampu u bilo kojoj veličini. Boje: Rispetto crna pozadina i zelena (#00ff03).
                Error correction: High (H) — otporniji na ogrebotine i fleke na jelovniku.
            </x-slot>

            <div class="grid gap-4 lg:grid-cols-3">
                @foreach ($this->qrCodes as $qrCode)
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $qrCode['label'] }}
                        </h3>

                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ $qrCode['description'] }}
                        </p>

                        <p class="mt-3 break-all text-xs text-gray-500 dark:text-gray-400">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Link:</span>
                            {{ $qrCode['url'] }}
                        </p>

                        @if ($qrCode['configured'])
                            <x-filament::button
                                tag="a"
                                :href="$qrCode['download_url']"
                                icon="heroicon-o-arrow-down-tray"
                                class="mt-5"
                            >
                                Preuzmi SVG
                            </x-filament::button>
                        @else
                            <x-filament::badge color="warning" class="mt-5">
                                Podesite URL u config/site.php
                            </x-filament::badge>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Napomene za štampu
            </x-slot>

            <ul class="list-disc space-y-2 ps-5 text-sm text-gray-600 dark:text-gray-300">
                <li>Ne štampajte QR kod manji od 2 × 2 cm.</li>
                <li>Instagram i Facebook linkove ažurirajte u <code class="rounded bg-gray-100 px-1 py-0.5 dark:bg-gray-800">config/site.php</code> → <code class="rounded bg-gray-100 px-1 py-0.5 dark:bg-gray-800">social</code>.</li>
                <li>Jelovnik koristi rutu <code class="rounded bg-gray-100 px-1 py-0.5 dark:bg-gray-800">/jelovnik</code> sa trenutnog domena.</li>
            </ul>
        </x-filament::section>
    </div>
</x-filament-panels::page>
