<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\HtmlAssertions;

uses(RefreshDatabase::class);

it('exposes complete seo meta tags on all public pages', function (): void {
    $siteName = (string) config('site.seo.site_name');

    foreach (config('site.seo.pages') as $routeName => $seo) {
        $uri = route($routeName, absolute: false);
        $fullTitle = "{$seo['title']} — {$siteName}";
        $description = $seo['description'];

        $html = $this->get($uri)->assertOk()->getContent();

        expect(HtmlAssertions::htmlLang($html))->toBe('sr');
        expect(HtmlAssertions::title($html))->toBe($fullTitle);
        expect(HtmlAssertions::metaName($html, 'description'))->toBe($description);
        expect(HtmlAssertions::metaName($html, 'robots'))->toBe('index, follow');
        expect(HtmlAssertions::metaName($html, 'theme-color'))->toBe('#050505');
        expect(HtmlAssertions::linkRel($html, 'canonical'))->toBe(url($uri));

        expect(HtmlAssertions::metaProperty($html, 'og:type'))->toBe('website');
        expect(HtmlAssertions::metaProperty($html, 'og:site_name'))->toBe('Rispetto');
        expect(HtmlAssertions::metaProperty($html, 'og:locale'))->toBe('sr_RS');
        expect(HtmlAssertions::metaProperty($html, 'og:title'))->toBe($fullTitle);
        expect(HtmlAssertions::metaProperty($html, 'og:description'))->toBe($description);
        expect(HtmlAssertions::metaProperty($html, 'og:url'))->toBe(url($uri));
        expect(HtmlAssertions::metaProperty($html, 'og:image'))->toContain('og-image.jpg');
        expect(HtmlAssertions::metaProperty($html, 'og:image:width'))->toBe('1200');
        expect(HtmlAssertions::metaProperty($html, 'og:image:height'))->toBe('630');
        expect(HtmlAssertions::metaProperty($html, 'og:image:alt'))->not->toBeEmpty();

        expect(HtmlAssertions::metaName($html, 'twitter:card'))->toBe('summary_large_image');
        expect(HtmlAssertions::metaName($html, 'twitter:title'))->toBe($fullTitle);
        expect(HtmlAssertions::metaName($html, 'twitter:description'))->toBe($description);
        expect(HtmlAssertions::metaName($html, 'twitter:image'))->toContain('og-image.jpg');
        expect(HtmlAssertions::metaName($html, 'twitter:image:alt'))->not->toBeEmpty();

        $jsonLd = HtmlAssertions::jsonLd($html);

        expect($jsonLd)->toBeArray()->toHaveCount(2);
        expect(collect($jsonLd)->pluck('@type')->all())->toContain('Restaurant', 'WebPage');

        $webPage = collect($jsonLd)->firstWhere('@type', 'WebPage');

        expect($webPage['name'])->toBe($fullTitle);
        expect($webPage['description'])->toBe($description);
        expect($webPage['url'])->toBe(url($uri));
    }
});

it('includes favicon and manifest links in the document head', function (): void {
    $html = $this->get('/')->assertOk()->getContent();

    expect(HtmlAssertions::countMatches($html, '/<link rel="icon"/'))->toBeGreaterThanOrEqual(2);
    expect(HtmlAssertions::countMatches($html, '/<link rel="apple-touch-icon"/'))->toBe(1);
    expect(HtmlAssertions::linkRel($html, 'manifest'))->toBe('/site.webmanifest');
});

it('renders sitemap xml with all configured public routes', function (): void {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $xml = simplexml_load_string($response->getContent());

    expect($xml)->not->toBeFalse();

    $xml->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    $locations = collect($xml->xpath('//s:url/s:loc'))
        ->map(fn ($node): string => (string) $node)
        ->all();

    foreach (config('site.seo.sitemap') as $entry) {
        expect($locations)->toContain(route($entry['route']));
    }
});
