<?php

namespace Tests\Support;

final class HtmlAssertions
{
    public static function title(string $html): string
    {
        self::assertMatch($html, '/<title>(.*?)<\/title>/s', 'title');

        return trim(html_entity_decode(strip_tags(self::match($html, '/<title>(.*?)<\/title>/s'))));
    }

    public static function metaName(string $html, string $name): string
    {
        self::assertMatch($html, '/<meta name="'.preg_quote($name, '/').'" content="([^"]*)"/', "meta name=\"{$name}\"");

        return html_entity_decode(self::match($html, '/<meta name="'.preg_quote($name, '/').'" content="([^"]*)"/'));
    }

    public static function metaProperty(string $html, string $property): string
    {
        self::assertMatch($html, '/<meta property="'.preg_quote($property, '/').'" content="([^"]*)"/', "meta property=\"{$property}\"");

        return html_entity_decode(self::match($html, '/<meta property="'.preg_quote($property, '/').'" content="([^"]*)"/'));
    }

    public static function linkRel(string $html, string $rel): string
    {
        self::assertMatch($html, '/<link rel="'.preg_quote($rel, '/').'" href="([^"]*)"/', "link rel=\"{$rel}\"");

        return self::match($html, '/<link rel="'.preg_quote($rel, '/').'" href="([^"]*)"/');
    }

    public static function htmlLang(string $html): string
    {
        self::assertMatch($html, '/<html lang="([^"]*)"/', 'html lang');

        return self::match($html, '/<html lang="([^"]*)"/');
    }

    public static function jsonLd(string $html): array
    {
        self::assertMatch($html, '/<script type="application\/ld\+json">(.*?)<\/script>/s', 'json-ld');

        return json_decode(
            self::match($html, '/<script type="application\/ld\+json">(.*?)<\/script>/s'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }

    public static function countMatches(string $html, string $pattern): int
    {
        preg_match_all($pattern, $html, $matches);

        return count($matches[0] ?? []);
    }

    private static function match(string $html, string $pattern): string
    {
        preg_match($pattern, $html, $matches);

        return $matches[1];
    }

    private static function assertMatch(string $html, string $pattern, string $label): void
    {
        expect($html)->toMatch($pattern, "Expected to find {$label} in HTML.");
    }
}
