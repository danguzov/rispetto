<x-site.layout lcp-image="images/site/hero.jpg">
    @foreach (config('site.home_sections') as $section)
        @includeIf('sections.'.$section, $sectionData[$section] ?? [])
    @endforeach
</x-site.layout>
