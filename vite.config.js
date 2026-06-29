import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/site.css',
                'resources/js/app.js',
                'resources/js/site.js',
                'resources/js/defer-reveal.js',
                'resources/js/defer-carousel.js',
                'resources/js/defer-catalog.js',
                'resources/js/defer-cart.js',
                'resources/js/defer-checkout.js',
                'resources/js/defer-order-tracking.js',
                'resources/js/defer-order-push.js',
                'resources/js/defer-ordering-availability.js',
                'resources/js/passkeys.js',
                'resources/js/dashboard.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                    preload: [{ weight: 400 }],
                }),
                bunny('Outfit', {
                    weights: [400, 500, 600, 700],
                    preload: [{ weight: 400 }, { weight: 500 }],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
