<?php

namespace App\Support;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Drink;
use App\Models\MenuItem;
use App\Models\Tag;
use BackedEnum;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use JsonException;
use Throwable;

final class SitePageCache
{
    private const PAYLOAD_VERSION = 1;

    public const MENU_KEY = 'site.catalog.menu';

    public const DRINKS_CATEGORIES_KEY = 'site.catalog.drinks.categories';

    public const DRINKS_UNCATEGORIZED_KEY = 'site.catalog.drinks.uncategorized';

    public const FEATURED_MENU_KEY = 'site.featured-menu';

    /**
     * @return EloquentCollection<int, Category>
     */
    public static function menuCategories(): EloquentCollection
    {
        return self::remember(self::MENU_KEY, function (): EloquentCollection {
            return Category::query()
                ->where('type', ProductType::Food)
                ->where('is_active', true)
                ->with(['menuItems' => function ($query): void {
                    $query->where('is_active', true)
                        ->with('tags')
                        ->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->get();
        }, fn (EloquentCollection $categories) => self::encodeCategoryCatalog($categories), fn (array $payload) => self::decodeCategoryCatalog($payload));
    }

    /**
     * @return EloquentCollection<int, Category>
     */
    public static function drinkCategories(): EloquentCollection
    {
        return self::remember(self::DRINKS_CATEGORIES_KEY, function (): EloquentCollection {
            return Category::query()
                ->where('type', ProductType::Drink)
                ->where('is_active', true)
                ->with(['drinks' => function ($query): void {
                    $query->where('is_active', true)
                        ->with('tags')
                        ->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->get();
        }, fn (EloquentCollection $categories) => self::encodeDrinkCategoryCatalog($categories), fn (array $payload) => self::decodeDrinkCategoryCatalog($payload));
    }

    /**
     * @return EloquentCollection<int, Drink>
     */
    public static function uncategorizedDrinks(): EloquentCollection
    {
        return self::remember(self::DRINKS_UNCATEGORIZED_KEY, function (): EloquentCollection {
            return Drink::query()
                ->where('is_active', true)
                ->whereNull('category_id')
                ->with('tags')
                ->orderBy('sort_order')
                ->get();
        }, fn (EloquentCollection $drinks) => self::encodeDrinkList($drinks), fn (array $payload) => self::decodeDrinkList($payload));
    }

    /**
     * @return EloquentCollection<int, MenuItem>
     */
    public static function featuredMenuItems(int $limit = 4): EloquentCollection
    {
        return self::remember(self::FEATURED_MENU_KEY, function () use ($limit): EloquentCollection {
            return MenuItem::query()
                ->where('is_active', true)
                ->where('featured', true)
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->with('category')
                ->orderBy('sort_order')
                ->limit($limit)
                ->get();
        }, fn (EloquentCollection $items) => self::encodeFeaturedMenuItems($items), fn (array $payload) => self::decodeFeaturedMenuItems($payload));
    }

    public static function forgetMenu(): void
    {
        Cache::forget(self::MENU_KEY);
    }

    public static function forgetDrinks(): void
    {
        Cache::forget(self::DRINKS_CATEGORIES_KEY);
        Cache::forget(self::DRINKS_UNCATEGORIZED_KEY);
    }

    public static function forgetFeaturedMenu(): void
    {
        Cache::forget(self::FEATURED_MENU_KEY);
    }

    public static function forgetAll(): void
    {
        self::forgetMenu();
        self::forgetDrinks();
        self::forgetFeaturedMenu();
    }

    /**
     * @template TModel of Model
     *
     * @param  callable(): EloquentCollection<int, TModel>  $loader
     * @param  callable(EloquentCollection<int, TModel>): array{v: int, data: mixed}  $encoder
     * @param  callable(array{v: int, data: mixed}): EloquentCollection<int, TModel>  $decoder
     * @return EloquentCollection<int, TModel>
     */
    private static function remember(string $key, callable $loader, callable $encoder, callable $decoder): EloquentCollection
    {
        $cached = Cache::get($key);

        if (is_array($cached)) {
            try {
                $collection = $decoder($cached);

                if ($collection instanceof EloquentCollection) {
                    return $collection;
                }
            } catch (Throwable) {
                // Stale or corrupt cache entry — rebuild below.
            }

            Cache::forget($key);
        } elseif ($cached !== null) {
            Cache::forget($key);
        }

        $fresh = $loader();
        Cache::forever($key, $encoder($fresh));

        return $fresh;
    }

    /**
     * @param  EloquentCollection<int, Category>  $categories
     * @return array{v: int, data: list<array{category: array<string, mixed>, menu_items: list<array{item: array<string, mixed>, tags: list<array<string, mixed>>}>}>}
     */
    private static function encodeCategoryCatalog(EloquentCollection $categories): array
    {
        return [
            'v' => self::PAYLOAD_VERSION,
            'data' => $categories->map(fn (Category $category): array => [
                'category' => self::exportAttributes($category),
                'menu_items' => $category->menuItems->map(fn (MenuItem $item): array => [
                    'item' => self::exportAttributes($item),
                    'tags' => $item->tags->map(fn (Tag $tag): array => self::exportAttributes($tag))->values()->all(),
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    /**
     * @param  array{v?: int, data?: mixed}  $payload
     * @return EloquentCollection<int, Category>
     */
    private static function decodeCategoryCatalog(array $payload): EloquentCollection
    {
        self::assertPayloadVersion($payload);

        /** @var list<array{category: array<string, mixed>, menu_items: list<array{item: array<string, mixed>, tags: list<array<string, mixed>>}>}> $rows */
        $rows = $payload['data'];

        return new EloquentCollection(collect($rows)->map(function (array $row): Category {
            $category = self::makeModel(Category::class, $row['category']);

            $menuItems = new EloquentCollection(collect($row['menu_items'])->map(function (array $itemRow): MenuItem {
                $item = self::makeModel(MenuItem::class, $itemRow['item']);
                $tags = new EloquentCollection(collect($itemRow['tags'])->map(
                    fn (array $tagRow): Tag => self::makeModel(Tag::class, $tagRow),
                ));

                $item->setRelation('tags', $tags);

                return $item;
            }));

            $category->setRelation('menuItems', $menuItems);

            return $category;
        }));
    }

    /**
     * @param  EloquentCollection<int, Category>  $categories
     * @return array{v: int, data: list<array{category: array<string, mixed>, drinks: list<array{item: array<string, mixed>, tags: list<array<string, mixed>>}>}>}
     */
    private static function encodeDrinkCategoryCatalog(EloquentCollection $categories): array
    {
        return [
            'v' => self::PAYLOAD_VERSION,
            'data' => $categories->map(fn (Category $category): array => [
                'category' => self::exportAttributes($category),
                'drinks' => $category->drinks->map(fn (Drink $drink): array => [
                    'item' => self::exportAttributes($drink),
                    'tags' => $drink->tags->map(fn (Tag $tag): array => self::exportAttributes($tag))->values()->all(),
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    /**
     * @param  array{v?: int, data?: mixed}  $payload
     * @return EloquentCollection<int, Category>
     */
    private static function decodeDrinkCategoryCatalog(array $payload): EloquentCollection
    {
        self::assertPayloadVersion($payload);

        /** @var list<array{category: array<string, mixed>, drinks: list<array{item: array<string, mixed>, tags: list<array<string, mixed>>}>}> $rows */
        $rows = $payload['data'];

        return new EloquentCollection(collect($rows)->map(function (array $row): Category {
            $category = self::makeModel(Category::class, $row['category']);

            $drinks = new EloquentCollection(collect($row['drinks'])->map(function (array $drinkRow): Drink {
                $drink = self::makeModel(Drink::class, $drinkRow['item']);
                $tags = new EloquentCollection(collect($drinkRow['tags'])->map(
                    fn (array $tagRow): Tag => self::makeModel(Tag::class, $tagRow),
                ));

                $drink->setRelation('tags', $tags);

                return $drink;
            }));

            $category->setRelation('drinks', $drinks);

            return $category;
        }));
    }

    /**
     * @param  EloquentCollection<int, Drink>  $drinks
     * @return array{v: int, data: list<array{item: array<string, mixed>, tags: list<array<string, mixed>>}>}
     */
    private static function encodeDrinkList(EloquentCollection $drinks): array
    {
        return [
            'v' => self::PAYLOAD_VERSION,
            'data' => $drinks->map(fn (Drink $drink): array => [
                'item' => self::exportAttributes($drink),
                'tags' => $drink->tags->map(fn (Tag $tag): array => self::exportAttributes($tag))->values()->all(),
            ])->values()->all(),
        ];
    }

    /**
     * @param  array{v?: int, data?: mixed}  $payload
     * @return EloquentCollection<int, Drink>
     */
    private static function decodeDrinkList(array $payload): EloquentCollection
    {
        self::assertPayloadVersion($payload);

        /** @var list<array{item: array<string, mixed>, tags: list<array<string, mixed>>}> $rows */
        $rows = $payload['data'];

        return new EloquentCollection(collect($rows)->map(function (array $row): Drink {
            $drink = self::makeModel(Drink::class, $row['item']);
            $tags = new EloquentCollection(collect($row['tags'])->map(
                fn (array $tagRow): Tag => self::makeModel(Tag::class, $tagRow),
            ));

            $drink->setRelation('tags', $tags);

            return $drink;
        }));
    }

    /**
     * @param  EloquentCollection<int, MenuItem>  $items
     * @return array{v: int, data: list<array{item: array<string, mixed>, category: array<string, mixed>|null}>}
     */
    private static function encodeFeaturedMenuItems(EloquentCollection $items): array
    {
        return [
            'v' => self::PAYLOAD_VERSION,
            'data' => $items->map(fn (MenuItem $item): array => [
                'item' => self::exportAttributes($item),
                'category' => $item->category ? self::exportAttributes($item->category) : null,
            ])->values()->all(),
        ];
    }

    /**
     * @param  array{v?: int, data?: mixed}  $payload
     * @return EloquentCollection<int, MenuItem>
     */
    private static function decodeFeaturedMenuItems(array $payload): EloquentCollection
    {
        self::assertPayloadVersion($payload);

        /** @var list<array{item: array<string, mixed>, category: array<string, mixed>|null}> $rows */
        $rows = $payload['data'];

        return new EloquentCollection(collect($rows)->map(function (array $row): MenuItem {
            $item = self::makeModel(MenuItem::class, $row['item']);

            if ($row['category'] !== null) {
                $item->setRelation('category', self::makeModel(Category::class, $row['category']));
            }

            return $item;
        }));
    }

    /**
     * @param  array{v?: int, data?: mixed}  $payload
     */
    private static function assertPayloadVersion(array $payload): void
    {
        if (($payload['v'] ?? null) !== self::PAYLOAD_VERSION || ! isset($payload['data']) || ! is_array($payload['data'])) {
            throw new \RuntimeException('Invalid site page cache payload.');
        }
    }

    /**
     * @param  class-string<TModel>  $modelClass
     * @param  array<string, mixed>  $attributes
     * @return TModel
     *
     * @template TModel of Model
     */
    private static function makeModel(string $modelClass, array $attributes): Model
    {
        /** @var TModel $model */
        $model = new $modelClass;
        $model->setRawAttributes(self::normalizeAttributesForHydration($model, $attributes), true);
        $model->exists = true;

        return $model;
    }

    /**
     * @return array<string, mixed>
     */
    private static function exportAttributes(Model $model): array
    {
        $exported = [];

        foreach (array_keys($model->getAttributes()) as $key) {
            $exported[$key] = self::exportAttributeValue($model, $key);
        }

        return $exported;
    }

    private static function exportAttributeValue(Model $model, string $key): mixed
    {
        $value = $model->getAttribute($key);

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if (self::isJsonCast($model->getCasts()[$key] ?? null) && (is_array($value) || is_object($value))) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private static function normalizeAttributesForHydration(Model $model, array $attributes): array
    {
        foreach ($model->getCasts() as $key => $cast) {
            if (! array_key_exists($key, $attributes)) {
                continue;
            }

            $value = $attributes[$key];

            if ($value instanceof BackedEnum) {
                $attributes[$key] = $value->value;

                continue;
            }

            if (self::isJsonCast($cast) && is_array($value)) {
                try {
                    $attributes[$key] = json_encode($value, JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    unset($attributes[$key]);
                }
            }
        }

        return $attributes;
    }

    private static function isJsonCast(mixed $cast): bool
    {
        if (! is_string($cast)) {
            return false;
        }

        return in_array($cast, ['array', 'json', 'object', 'collection', 'encrypted:array', 'encrypted:json', 'encrypted:object', 'encrypted:collection'], true)
            || str_starts_with($cast, 'json:')
            || str_starts_with($cast, 'array:');
    }
}
