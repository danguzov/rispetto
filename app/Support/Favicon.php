<?php

namespace App\Support;

class Favicon
{
    public static function url(string $path): string
    {
        $normalized = '/'.ltrim($path, '/');
        $fullPath = public_path(ltrim($normalized, '/'));

        if (! is_file($fullPath)) {
            return asset($normalized);
        }

        return asset($normalized).'?v='.filemtime($fullPath);
    }
}
