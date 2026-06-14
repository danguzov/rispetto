<?php

namespace App\Support;

use GdImage;
use Illuminate\Support\Facades\File;

final class StorageImageOptimizer
{
    /**
     * @return list<int>
     */
    public static function widths(): array
    {
        /** @var list<int> $widths */
        $widths = config('site.images.responsive_widths', [640, 1024, 1280]);

        return $widths;
    }

    public static function isVariantPath(string $path): bool
    {
        return (bool) preg_match('/-\d+w\./', $path);
    }

    public static function optimize(string $relativePath): void
    {
        if (blank($relativePath) || self::isVariantPath($relativePath)) {
            return;
        }

        if (str_starts_with($relativePath, 'http') || str_starts_with($relativePath, 'images/')) {
            return;
        }

        $sourcePath = SiteImage::publicPath($relativePath);

        if (! is_file($sourcePath)) {
            return;
        }

        $resource = self::loadImage($sourcePath);

        if ($resource === null) {
            return;
        }

        $sourceWidth = imagesx($resource);
        $sourceHeight = imagesy($resource);
        $quality = (int) config('site.images.webp_quality', 82);

        self::writeWebp(
            $resource,
            SiteImage::publicPath(SiteImage::replaceExtension($relativePath, 'webp')),
            $quality,
        );

        foreach (self::widths() as $width) {
            $variantRelative = SiteImage::variantPath($relativePath, $width);
            $resized = self::resizeToWidth($resource, $width, $sourceWidth, $sourceHeight);

            if ($resized === null) {
                continue;
            }

            self::writeRaster($resized, SiteImage::publicPath($variantRelative), $relativePath);
            self::writeWebp(
                $resized,
                SiteImage::publicPath(SiteImage::replaceExtension($variantRelative, 'webp')),
                $quality,
            );
            imagedestroy($resized);
        }

        imagedestroy($resource);
    }

    public static function deleteVariants(string $relativePath): void
    {
        if (blank($relativePath) || self::isVariantPath($relativePath)) {
            return;
        }

        $paths = [
            SiteImage::replaceExtension($relativePath, 'webp'),
        ];

        foreach (self::widths() as $width) {
            $variantRelative = SiteImage::variantPath($relativePath, $width);
            $paths[] = $variantRelative;
            $paths[] = SiteImage::replaceExtension($variantRelative, 'webp');
        }

        foreach ($paths as $path) {
            File::delete(SiteImage::publicPath($path));
        }
    }

    private static function loadImage(string $path): ?GdImage
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $info = @getimagesize($path);

        if ($info === false) {
            return null;
        }

        return match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path) ?: null,
            IMAGETYPE_PNG => @imagecreatefrompng($path) ?: null,
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($path) ?: null) : null,
            default => null,
        };
    }

    private static function resizeToWidth(GdImage $source, int $targetWidth, int $sourceWidth, int $sourceHeight): ?GdImage
    {
        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            return null;
        }

        $width = min($targetWidth, $sourceWidth);
        $height = (int) round($sourceHeight * ($width / $sourceWidth));

        if ($width <= 0 || $height <= 0) {
            return null;
        }

        $canvas = imagecreatetruecolor($width, $height);

        if ($canvas === false) {
            return null;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
        imagealphablending($canvas, true);

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $width,
            $height,
            $sourceWidth,
            $sourceHeight,
        );

        return $canvas;
    }

    private static function writeRaster(GdImage $resource, string $path, string $relativeSourcePath): void
    {
        File::ensureDirectoryExists(dirname($path));

        $extension = strtolower(pathinfo($relativeSourcePath, PATHINFO_EXTENSION));

        $saved = match ($extension) {
            'png' => imagepng($resource, $path),
            'webp' => function_exists('imagewebp')
                ? imagewebp($resource, $path, (int) config('site.images.webp_quality', 82))
                : false,
            default => imagejpeg($resource, $path, (int) config('site.images.jpeg_quality', 85)),
        };

        if ($saved === false) {
            return;
        }

        @chmod($path, 0644);
    }

    private static function writeWebp(GdImage $resource, string $path, int $quality): void
    {
        if (! function_exists('imagewebp')) {
            return;
        }

        File::ensureDirectoryExists(dirname($path));

        if (imagewebp($resource, $path, $quality) === false) {
            return;
        }

        @chmod($path, 0644);
    }
}
