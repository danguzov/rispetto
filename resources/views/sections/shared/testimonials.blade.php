@php
$reviews = config('site.testimonials');
$desktopDotCount = max(1, count($reviews) - 2);
@endphp

<x-site.section>
    <x-site.container>
        <div class="mx-auto mb-10 max-w-3xl text-center sm:mb-12">
            <h2 class="font-display text-3xl font-medium leading-tight text-rispetto-text sm:text-4xl lg:text-5xl">
                Utisci
                <em class="font-display font-medium italic text-rispetto-accent">naših gostiju</em>
            </h2>
            <p class="site-subheading mx-auto mt-4">
                Vaše zadovoljstvo je merilo našeg uspeha. Hvala vam na poverenju.
            </p>
        </div>

        <div data-testimonials class="relative">
            <div class="overflow-hidden">
                <div
                    data-testimonials-track
                    class="flex cursor-grab gap-6 transition-transform duration-300 ease-out active:cursor-grabbing">
                    @foreach ($reviews as $review)
                    <div
                        data-testimonials-slide
                        class="w-full shrink-0 md:w-[calc((100%-3rem)/3)]">
                        <x-site.testimonial-card
                            :name="$review['name']"
                            :text="$review['text']"
                            :avatar="$review['avatar'] ?? null"
                            :initial="$review['initial'] ?? null"
                            :avatar-color="$review['avatar_color'] ?? 'bg-violet-600'" />
                    </div>
                    @endforeach
                </div>
            </div>

            @if (count($reviews) > 1)
            {{-- Mobilni: jedna tačka po recenziji --}}
            <div class="mt-8 flex justify-center gap-2 md:hidden" data-testimonials-dots="mobile" role="tablist" aria-label="Recenzije">
                @foreach ($reviews as $index => $review)
                <button
                    type="button"
                    data-testimonials-dot="{{ $index }}"
                    class="size-2.5 rounded-full bg-rispetto-border transition-colors data-[active=true]:bg-rispetto-accent"
                    aria-label="Recenzija {{ $index + 1 }}"
                    @if ($index === 0) data-active="true" @endif></button>
                @endforeach
            </div>

            {{-- Desktop: tačke po poziciji karusela (3 vidljive od 5) --}}
            @if ($desktopDotCount > 1)
            <div class="mt-8 hidden justify-center gap-2 md:flex" data-testimonials-dots="desktop" role="tablist" aria-label="Recenzije">
                @for ($index = 0; $index < $desktopDotCount; $index++)
                <button
                    type="button"
                    data-testimonials-dot="{{ $index }}"
                    class="size-2.5 rounded-full bg-rispetto-border transition-colors data-[active=true]:bg-rispetto-accent"
                    aria-label="Recenzija {{ $index + 1 }}"
                    @if ($index === 0) data-active="true" @endif></button>
                @endfor
            </div>
            @endif
            @endif
        </div>
    </x-site.container>
</x-site.section>