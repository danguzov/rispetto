<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Drink;
use App\Models\MenuItem;
use App\Models\Tag;
use App\Observers\SitePageCacheObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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
        $this->configureDefaults();
        $this->ensurePublicStorageLink();
        $this->registerSitePageCacheObserver();
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
