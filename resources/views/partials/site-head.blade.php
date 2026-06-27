@php
    use App\Support\SiteImage;
    use App\Support\SiteScripts;
    use App\Support\SiteSeo;

    $seo = SiteSeo::resolve($title ?? null, $description ?? null, $ogImage ?? null, $robots ?? null);
    $jsonLd = [
        SiteSeo::restaurantSchema(),
        SiteSeo::webPageSchema($seo['full_title'], $seo['description']),
    ];

    $lcpSrc = filled($lcpImage ?? null) && SiteImage::exists($lcpImage)
        ? SiteImage::fallbackSrc($lcpImage)
        : null;
    $lcpMime = $lcpSrc ? SiteImage::mimeTypeForUrl($lcpSrc) : null;
@endphp

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<title>{{ $seo['full_title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}" />
<meta name="robots" content="{{ $seo['robots'] }}" />
<meta name="theme-color" content="{{ $seo['theme_color'] }}" />
<link rel="canonical" href="{{ $seo['canonical'] }}" />

<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16.png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">
<link rel="manifest" href="/site.webmanifest">

<meta property="og:type" content="website" />
<meta property="og:site_name" content="{{ $seo['site_name'] }}" />
<meta property="og:locale" content="{{ $seo['locale'] }}" />
<meta property="og:title" content="{{ $seo['full_title'] }}" />
<meta property="og:description" content="{{ $seo['description'] }}" />
<meta property="og:url" content="{{ $seo['canonical'] }}" />
<meta property="og:image" content="{{ $seo['og_image'] }}" />
<meta property="og:image:width" content="{{ $seo['og_image_width'] }}" />
<meta property="og:image:height" content="{{ $seo['og_image_height'] }}" />
<meta property="og:image:alt" content="{{ $seo['site_name'] }} — italijanski restoran u Batajnici" />

<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $seo['full_title'] }}" />
<meta name="twitter:description" content="{{ $seo['description'] }}" />
<meta name="twitter:image" content="{{ $seo['og_image'] }}" />
<meta name="twitter:image:alt" content="{{ $seo['site_name'] }} — italijanski restoran u Batajnici" />
@if (filled($seo['twitter_site']))
    <meta name="twitter:site" content="{{ $seo['twitter_site'] }}" />
@endif

<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

@if ($lcpSrc)
<link rel="preload" as="image" href="{{ $lcpSrc }}" @if ($lcpMime) type="{{ $lcpMime }}" @endif fetchpriority="high">
@endif

@include('partials.site-display-fonts')

@fonts('outfit')
@vite(['resources/css/site.css', 'resources/js/site.js'])
@vite(SiteScripts::deferEntries())
