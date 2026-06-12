<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

final class SiteImage
{
    /**
     * @param  list<int>  $widths
     */
    public static function srcset(string $path, array $widths): string
    {
        $entries = [];

        foreach ($widths as $width) {
            $variant = self::variantPath($path, $width);

            if (File::exists(self::publicPath($variant))) {
                $entries[] = self::url($variant).' '.$width.'w';
            }
        }

        if ($entries === [] && File::exists(self::publicPath($path))) {
            $entries[] = self::url($path).' 1x';
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

        return asset('storage/'.$path);
    }

    public static function webpUrl(string $path): ?string
    {
        if (str_starts_with($path, 'http')) {
            return null;
        }

        $webpPath = self::replaceExtension($path, 'webp');

        return File::exists(self::publicPath($webpPath)) ? self::url($webpPath) : null;
    }

    public static function publicPath(string $path): string
    {
        if (str_starts_with($path, 'images/')) {
            return public_path($path);
        }

        return public_path('storage/'.$path);
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
