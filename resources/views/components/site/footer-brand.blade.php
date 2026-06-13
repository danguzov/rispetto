@php
    $logo = config('site.brand_logo', 'images/site/rispetto-logo.svg');
@endphp

<div {{ $attributes->class(['flex flex-col items-start']) }}>
    @if (file_exists(public_path($logo)))
        <img
            src="{{ asset($logo) }}"
            alt="Rispetto — italijanski restoran"
            class="h-10 w-auto sm:h-11"
            width="213"
            height="46"
        />
    @else
        <span class="text-2xl font-bold uppercase leading-none tracking-wide text-rispetto-accent sm:text-[1.75rem]">
            Rispetto
        </span>
    @endif
</div>
