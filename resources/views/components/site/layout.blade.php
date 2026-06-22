@props([
    'title' => null,
    'description' => null,
    'ogImage' => null,
    'robots' => null,
    'lcpImage' => null,
])

<!DOCTYPE html>
<html lang="sr">
    <head>
        @include('partials.site-head', [
            'title' => $title,
            'description' => $description,
            'ogImage' => $ogImage,
            'robots' => $robots,
            'lcpImage' => $lcpImage,
        ])
    </head>
    <body class="min-h-screen flex flex-col">
        <x-site.header />

        <main class="flex-1">
            {{ $slot }}
        </main>

        <x-site.footer />
    </body>
</html>
