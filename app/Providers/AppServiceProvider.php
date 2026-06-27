<?php

namespace App\Providers;

use App\Events\Ordering\OrderStatusUpdated;
use App\Listeners\SendOrderCustomerNotifications;
use App\Models\Category;
use App\Models\Drink;
use App\Models\MenuItem;
use App\Models\Tag;
use App\Observers\SitePageCacheObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureBroadcasting();
        $this->configureFilamentEcho();
        $this->registerOrderEventListeners();
        $this->configureDefaults();
        $this->ensurePublicStorageLink();
        $this->registerSitePageCacheObserver();
    }

    protected function configureBroadcasting(): void
    {
        Broadcast::routes([
            'middleware' => ['web', 'auth'],
        ]);
    }

    protected function configureFilamentEcho(): void
    {
        $connection = (string) config('broadcasting.default', 'null');

        if (! in_array($connection, ['reverb', 'pusher'], true)) {
            return;
        }

        $scheme = (string) env('VITE_REVERB_SCHEME', env('REVERB_SCHEME', 'http'));
        $port = (int) env('VITE_REVERB_PORT', env('REVERB_PORT', $scheme === 'https' ? 443 : 8080));

        config([
            'filament.broadcasting.echo' => [
                'broadcaster' => $connection,
                'key' => env('VITE_REVERB_APP_KEY', env('REVERB_APP_KEY')),
                'wsHost' => env('VITE_REVERB_HOST', env('REVERB_HOST', 'localhost')),
                'wsPort' => $port,
                'wssPort' => $port,
                'forceTLS' => $scheme === 'https',
                'enabledTransports' => ['ws', 'wss'],
                'authEndpoint' => '/broadcasting/auth',
            ],
        ]);
    }

    protected function registerOrderEventListeners(): void
    {
        Event::listen(
            OrderStatusUpdated::class,
            SendOrderCustomerNotifications::class,
        );
    }

    protected function registerSitePageCacheObserver(): void
    {
        $observer = SitePageCacheObserver::class;

        Category::observe($observer);
        MenuItem::observe($observer);
        Drink::observe($observer);
        Tag::observe($observer);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Filament uploadi idu u storage/app/public — javni URL /storage/ zahteva symlink.
     */
    protected function ensurePublicStorageLink(): void
    {
        $link = public_path('storage');

        if (is_link($link)) {
            return;
        }

        if (file_exists($link)) {
            return;
        }

        if (! is_dir(storage_path('app/public'))) {
            return;
        }

        try {
            Artisan::call('storage:link');
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
