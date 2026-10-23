<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

final class SiteSeo
{
    /**
     * @return array{
     *     full_title: string,
     *     description: string,
     *     canonical: string,
     *     og_image: string,
     *     og_image_width: int,
     *     og_image_height: int,
     *     site_name: string,
     *     locale: string,
     *     robots: string,
     *     twitter_site: ?string,
     *     theme_color: string,
     * }
     */
    public static function resolve(?string $title = null, ?string $description = null, ?string $ogImage = null, ?string $robots = null): array
    {
        $routeName = Route::currentRouteName();
        $pageSeo = $routeName ? config("site.seo.pages.{$routeName}", []) : [];

        $siteName = (string) config('site.seo.site_name');
        $pageTitle = $title ?? ($pageSeo['title'] ?? null);
        $pageDescription = $description
            ?? ($pageSeo['description'] ?? null)
            ?? (string) config('site.seo.default_description');

        $fullTitle = filled($pageTitle)
            ? "{$pageTitle} — {$siteName}"
            : (string) config('site.seo.default_title');

        $ogImagePath = $ogImage
            ?? ($pageSeo['og_image'] ?? null)
            ?? (string) config('site.seo.og_image');

        return [
            'full_title' => $fullTitle,
            'description' => $pageDescription,
            'canonical' => url()->current(),
            'og_image' => SiteImage::url($ogImagePath),
            'og_image_width' => (int) config('site.seo.og_image_width', 1200),
            'og_image_height' => (int) config('site.seo.og_image_height', 630),
            'site_name' => $siteName,
            'locale' => (string) config('site.seo.locale', 'sr_RS'),
            'robots' => $robots ?? (string) config('site.seo.robots', 'index, follow'),
            'twitter_site' => config('site.seo.twitter_site'),
            'theme_color' => (string) config('site.seo.theme_color', '#050505'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function restaurantSchema(): array
    {
        $contact = config('site.contact');
        $hours = collect(config('site.opening_hours'))
            ->map(fn (array $row): string => "{$row['label']}: {$row['hours']}")
            ->implode('; ');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => $contact['name'],
            'url' => url('/'),
            'image' => SiteImage::url((string) config('site.seo.og_image')),
            'telephone' => config('site.phone_href'),
            'email' => $contact['email'],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $contact['street_address'],
                'addressLocality' => $contact['locality'],
                'addressRegion' => $contact['region'],
                'addressCountry' => 'RS',
            ],
            'servesCuisine' => ['Italian', 'Serbian'],
            'openingHours' => $hours,
            'sameAs' => collect(config('site.social'))
                ->pluck('url')
                ->filter(fn (string $url): bool => filled($url) && $url !== '#')
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function webPageSchema(string $name, string $description): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $name,
            'description' => $description,
            'url' => url()->current(),
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => config('site.seo.site_name'),
                'url' => url('/'),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function sitemapUrls(): array
    {
        return collect(config('site.seo.sitemap', []))
            ->map(fn (array $entry): array => [
                'loc' => route($entry['route']),
                'changefreq' => $entry['changefreq'] ?? 'weekly',
                'priority' => $entry['priority'] ?? '0.8',
            ])
            ->all();
    }
}
