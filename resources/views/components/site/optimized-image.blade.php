@php
    $srcset = $srcset();
    $webpSrcset = $webpSrcset();
    $hasSrcset = filled($srcset);
    $hasWebpSrcset = filled($webpSrcset);
@endphp

<picture class="block size-full">
    @if ($hasWebpSrcset)
        <source type="image/webp" srcset="{{ $webpSrcset }}" sizes="{{ $sizes }}">
    @elseif ($webp = $webpSrc())
        <source type="image/webp" srcset="{{ $webp }}" sizes="{{ $sizes }}">
    @endif

    <img
        src="{{ $assetSrc() }}"
        alt="{{ $alt }}"
        @if ($hasSrcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
        @if ($width) width="{{ $width }}" @endif
        @if ($height) height="{{ $height }}" @endif
        @class([$class])
        @if ($priority) fetchpriority="high" loading="eager" decoding="sync" @else loading="lazy" decoding="async" @endif
    >
</picture>
