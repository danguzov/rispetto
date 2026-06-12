<x-site.layout title="Početna">
    @foreach (config('site.home_sections') as $section)
        @includeIf('sections.'.$section, $sectionData[$section] ?? [])
    @endforeach
</x-site.layout>
