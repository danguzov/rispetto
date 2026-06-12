<x-site.layout title="Dostava" description="Dostava hrane i lično preuzimanje u restoranu Rispetto.">
    @include('sections.delivery.hero')
    @include('sections.delivery.options')
    @include('sections.shared.featured-menu', ['items' => $featuredMenuItems])
    @include('sections.shared.testimonials')
    @include('sections.shared.visit')
</x-site.layout>
