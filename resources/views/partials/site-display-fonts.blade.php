@php
    $benguiatBook = public_path('fonts/benguiat/BenguiatStd-Book.woff2');
    $benguiatBold = public_path('fonts/benguiat/BenguiatStd-Bold.woff2');
@endphp

@if (file_exists($benguiatBook) || file_exists($benguiatBold))
<style>
@if (file_exists($benguiatBook))
@font-face {
    font-family: 'ITC Benguiat Std';
    src: url('/fonts/benguiat/BenguiatStd-Book.woff2') format('woff2');
    font-weight: 400;
    font-style: normal;
    font-display: swap;
}
@endif
@if (file_exists($benguiatBold))
@font-face {
    font-family: 'ITC Benguiat Std';
    src: url('/fonts/benguiat/BenguiatStd-Bold.woff2') format('woff2');
    font-weight: 700;
    font-style: normal;
    font-display: swap;
}
@endif
</style>
@endif
