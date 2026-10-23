<?php

namespace App\Observers;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Drink;
use App\Models\MenuItem;
use App\Models\Tag;
use App\Support\SitePageCache;

class SitePageCacheObserver
{
    public function saved(Category|MenuItem|Drink|Tag $model): void
    {
        $this->flushFor($model);
    }

    public function deleted(Category|MenuItem|Drink|Tag $model): void
    {
        $this->flushFor($model);
    }

    private function flushFor(Category|MenuItem|Drink|Tag $model): void
    {
        if ($model instanceof Category) {
            match ($model->type) {
                ProductType::Food => SitePageCache::forgetMenu(),
                ProductType::Drink => SitePageCache::forgetDrinks(),
            };

            if ($model->type === ProductType::Food) {
                SitePageCache::forgetFeaturedMenu();
            }

            return;
        }

        if ($model instanceof MenuItem) {
            SitePageCache::forgetMenu();
            SitePageCache::forgetFeaturedMenu();

            return;
        }

        if ($model instanceof Drink) {
            SitePageCache::forgetDrinks();

            return;
        }

        SitePageCache::forgetAll();
    }
}
