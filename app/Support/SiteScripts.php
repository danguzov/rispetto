<?php

namespace App\Support;

final class SiteScripts
{
    /**
     * @return list<string>
     */
    public static function deferEntries(): array
    {
        $scripts = ['resources/js/defer-reveal.js'];

        if (request()->routeIs('home', 'delivery')) {
            $scripts[] = 'resources/js/defer-carousel.js';
        }

        if (request()->routeIs('menu', 'drinks')) {
            $scripts[] = 'resources/js/defer-catalog.js';
        }

        return $scripts;
    }
}
