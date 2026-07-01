@php
    use App\Support\Favicon;
@endphp

<link rel="icon" href="{{ Favicon::url('favicon.ico') }}" sizes="48x48">
<link rel="icon" href="{{ Favicon::url('favicon.svg') }}" type="image/svg+xml">
<link rel="icon" type="image/png" sizes="32x32" href="{{ Favicon::url('favicon-32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ Favicon::url('favicon-16.png') }}">
<link rel="apple-touch-icon" href="{{ Favicon::url('apple-touch-icon.png') }}" sizes="180x180">
