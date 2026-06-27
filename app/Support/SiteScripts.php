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

        if (request()->routeIs('home', 'delivery', 'menu', 'cart.show')) {
            $scripts[] = 'resources/js/defer-cart.js';
        }

        if (request()->routeIs('home', 'delivery', 'menu', 'cart.show', 'checkout.show')) {
            $scripts[] = 'resources/js/defer-ordering-availability.js';
        }

        if (request()->routeIs('checkout.show')) {
            $scripts[] = 'resources/js/defer-checkout.js';
        }

        if (request()->routeIs('order.tracking')) {
            $scripts[] = 'resources/js/defer-order-tracking.js';
            $scripts[] = 'resources/js/defer-order-push.js';
        }

        if (request()->routeIs('menu', 'drinks')) {
            $scripts[] = 'resources/js/defer-catalog.js';
        }

        return $scripts;
    }
}
