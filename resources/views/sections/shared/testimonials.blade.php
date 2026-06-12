@php
    $reviews = config('site.testimonials');
@endphp

<x-site.section>
    <x-site.container>
        <div class="mx-auto mb-10 max-w-3xl text-center sm:mb-12">
            <h2 class="font-display text-3xl font-bold leading-tight text-rispetto-text sm:text-4xl lg:text-5xl">
                Utisci
                <em class="font-display font-bold italic text-rispetto-accent">naših gostiju</em>
            </h2>
            <p class="site-subheading mx-auto mt-4">
                Vaše zadovoljstvo je merilo našeg uspeha. Hvala vam na poverenju.
            </p>
        </div>

        <div data-testimonials class="relative">
            <div class="overflow-hidden">
                <div
                    data-testimonials-track
                    class="flex gap-6 transition-transform duration-300 ease-out"
                >
                    @foreach ($reviews as $review)
                        <div
                            data-testimonials-slide
                            class="w-full shrink-0 md:w-[calc((100%-3rem)/3)]"
                        >
                            <x-site.testimonial-card
                                :name="$review['name']"
                                :text="$review['text']"
                                :avatar="$review['avatar'] ?? null"
                                :initial="$review['initial'] ?? null"
                                :avatar-color="$review['avatar_color'] ?? 'bg-violet-600'"
                            />
                        </div>
                    @endforeach
                </div>
            </div>

            @if (count($reviews) > 1)
                <div class="mt-8 flex justify-center gap-2" data-testimonials-dots role="tablist" aria-label="Recenzije">
                    @foreach ($reviews as $index => $review)
                        <button
                            type="button"
                            data-testimonials-dot="{{ $index }}"
                            class="size-2.5 rounded-full bg-rispetto-border transition-colors data-[active=true]:bg-rispetto-accent"
                            aria-label="Recenzija {{ $index + 1 }}"
                            @if ($index === 0) data-active="true" @endif
                        ></button>
                    @endforeach
                </div>
            @endif
        </div>
    </x-site.container>
</x-site.section>
