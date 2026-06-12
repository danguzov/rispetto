<x-site.layout title="Proslave" description="Organizujte proslave i događaje u restoranu Rispetto.">
    @include('sections.page-hero', [
        'eyebrow' => 'Proslave',
        'title' => 'Proslavite sa stilom',
        'description' => 'Privatni prostor, prilagođen meni i tim koji brine o svakom detalju.',
    ])

    <x-site.section>
        <x-site.container>
            <div class="grid gap-8 lg:grid-cols-3">
                @foreach ([
                    ['title' => 'Rođendani i godišnjice', 'text' => 'Intimna atmosfera i meni koji oduševljava goste svih uzrasta.'],
                    ['title' => 'Korporativni događaji', 'text' => 'Ručkovi, prezentacije i timski ručkovi u profesionalnom ambijentu.'],
                    ['title' => 'Porodične proslave', 'text' => 'Krštenja, veridbe i svečanosti — prilagođavamo prostor vašim željama.'],
                ] as $block)
                    <div class="site-panel p-6 sm:p-8">
                        <h2 class="font-display text-xl font-bold text-rispetto-text sm:text-2xl">{{ $block['title'] }}</h2>
                        <p class="mt-3 text-sm leading-relaxed text-rispetto-muted sm:text-base">{{ $block['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-site.container>
    </x-site.section>

    @include('sections.cta')
</x-site.layout>
