<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

final class SiteImage
{
    /**
     * @return list<int>
     */
    public static function responsiveWidths(): array
    {
        /** @var list<int> $widths */
        $widths = config('site.images.responsive_widths', [640, 1024, 1280]);

        return $widths;
    }

    public static function isStoragePath(string $path): bool
    {
        return ! str_starts_with($path, 'http') && ! str_starts_with($path, 'images/');
    }

    public static function catalogImage(?string $path, string $type): string
    {
        if (filled($path) && self::exists($path)) {
            return $path;
        }

        return (string) config("site.images.placeholders.{$type}", 'images/site/placeholder-food.jpg');
    }

    /**
     * Filesystem path for reading/writing image files.
     */
    public static function diskPath(string $path): string
    {
        if (str_starts_with($path, 'images/')) {
            return public_path($path);
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return Storage::disk('public')->path($path);
    }

    public static function exists(string $path): bool
    {
        if (str_starts_with($path, 'http')) {
            return true;
        }

        return File::exists(self::diskPath($path));
    }

    /**
     * @param  list<int>  $widths
     */
    public static function srcset(string $path, ?array $widths = null): string
    {
        $widths ??= self::responsiveWidths();
        $entries = [];

        foreach ($widths as $width) {
            $variant = self::variantPath($path, $width);

            if (self::exists($variant)) {
                $entries[] = self::url($variant).' '.$width.'w';
            }
        }

        if ($entries === [] && self::exists($path)) {
            $entries[] = self::url($path).' 1x';
        }

        return implode(', ', $entries);
    }

    /**
     * @param  list<int>  $widths
     */
    public static function webpSrcset(string $path, ?array $widths = null): string
    {
        $widths ??= self::responsiveWidths();
        $entries = [];

        foreach ($widths as $width) {
            $webpVariant = self::replaceExtension(self::variantPath($path, $width), 'webp');

            if (self::exists($webpVariant)) {
                $entries[] = self::url($webpVariant).' '.$width.'w';
            }
        }

        if ($entries === []) {
            $baseWebp = self::replaceExtension($path, 'webp');

            if (self::exists($baseWebp)) {
                $entries[] = self::url($baseWebp).' 1x';
            }
        }

        return implode(', ', $entries);
    }

    public static function url(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Smallest available variant for use as img src fallback (prefers WebP).
     *
     * @param  list<int>|null  $widths
     */
    public static function fallbackSrc(string $path, ?array $widths = null): string
    {
        $widths ??= self::responsiveWidths();

        foreach ($widths as $width) {
            $webpVariant = self::replaceExtension(self::variantPath($path, $width), 'webp');

            if (self::exists($webpVariant)) {
                return self::url($webpVariant);
            }
        }

        $baseWebp = self::replaceExtension($path, 'webp');

        if (self::exists($baseWebp)) {
            return self::url($baseWebp);
        }

        foreach ($widths as $width) {
            $variant = self::variantPath($path, $width);

            if (self::exists($variant)) {
                return self::url($variant);
            }
        }

        return self::url($path);
    }

    public static function mimeTypeForUrl(string $url): ?string
    {
        return match (strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION))) {
            'webp' => 'image/webp',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => null,
        };
    }

    public static function webpUrl(string $path): ?string
    {
        if (str_starts_with($path, 'http')) {
            return null;
        }

        $webpPath = self::replaceExtension($path, 'webp');

        return self::exists($webpPath) ? self::url($webpPath) : null;
    }

    /**
     * @deprecated Use diskPath() for storage files.
     */
    public static function publicPath(string $path): string
    {
        return self::diskPath($path);
    }

    public static function variantPath(string $path, int $width): string
    {
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($directory === '.') {
            return "{$filename}-{$width}w.{$extension}";
        }

        return "{$directory}/{$filename}-{$width}w.{$extension}";
    }

    public static function replaceExtension(string $path, string $extension): string
    {
        return preg_replace('/\.\w+$/', '.'.$extension, $path) ?? $path;
    }
}
