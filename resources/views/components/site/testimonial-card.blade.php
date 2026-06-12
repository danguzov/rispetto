@props([
    'name',
    'text',
    'avatar' => null,
    'initial' => null,
    'avatarColor' => 'bg-violet-600',
])

<article {{ $attributes->class(['flex h-full flex-col rounded-2xl border border-rispetto-border bg-rispetto-elevated p-5 sm:p-6']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
            @if ($avatar && file_exists(public_path($avatar)))
                <x-site.optimized-image
                    :src="$avatar"
                    :alt="$name"
                    class="size-10 shrink-0 rounded-full object-cover"
                    :width="80"
                    :height="80"
                    sizes="40px"
                />
            @else
                <span
                    class="inline-flex size-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white {{ $avatarColor }}"
                    aria-hidden="true"
                >
                    {{ $initial ?? strtoupper(substr($name, 0, 1)) }}
                </span>
            @endif
            <p class="truncate text-sm font-medium text-rispetto-text sm:text-base">{{ $name }}</p>
        </div>

        <svg class="size-5 shrink-0" viewBox="0 0 24 24" aria-label="Google recenzija" role="img">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
    </div>

    <div class="mt-4 flex text-rispetto-accent" aria-hidden="true">
        @for ($i = 0; $i < 5; $i++)
            <svg class="size-4 fill-current sm:size-5" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        @endfor
    </div>

    <blockquote class="mt-4 flex-1 text-sm leading-relaxed text-rispetto-muted sm:text-base">
        „{{ $text }}"
    </blockquote>
</article>
