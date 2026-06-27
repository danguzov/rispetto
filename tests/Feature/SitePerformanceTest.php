<?php

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\MenuItem;
use App\Support\SitePageCache;
use App\Support\SiteScripts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('site page cache stores and clears menu catalog', function (): void {
    Cache::flush();

    $category = Category::query()->create([
        'name' => 'Paste',
        'slug' => 'paste',
        'type' => ProductType::Food,
        'sort_order' => 0,
    ]);

    MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Carbonara',
        'price' => 890,
        'sort_order' => 0,
        'is_active' => true,
    ]);

    expect(Cache::has(SitePageCache::MENU_KEY))->toBeFalse();

    $categories = SitePageCache::menuCategories();

    expect(Cache::has(SitePageCache::MENU_KEY))->toBeTrue()
        ->and($categories)->toHaveCount(1)
        ->and($categories->first())->toBeInstanceOf(Category::class)
        ->and($categories->first()->menuItems)->toHaveCount(1);

    MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Amatriciana',
        'price' => 920,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    expect(Cache::has(SitePageCache::MENU_KEY))->toBeFalse()
        ->and(SitePageCache::menuCategories()->first()->menuItems)->toHaveCount(2);
});

test('site page cache rebuilds when legacy serialized payload is stored', function (): void {
    Cache::flush();

    Cache::forever(SitePageCache::MENU_KEY, serialize(new stdClass));

    $category = Category::query()->create([
        'name' => 'Pizza',
        'slug' => 'pizza',
        'type' => ProductType::Food,
        'sort_order' => 0,
    ]);

    MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Margherita',
        'price' => 790,
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $categories = SitePageCache::menuCategories();

    expect($categories)->toHaveCount(1)
        ->and($categories->first()->menuItems)->toHaveCount(1)
        ->and(Cache::get(SitePageCache::MENU_KEY))->toBeArray();
});

test('featured menu cache round-trips price variants for delivery page', function (): void {
    Cache::flush();

    $category = Category::query()->create([
        'name' => 'Pizza',
        'slug' => 'pizza-cache',
        'type' => ProductType::Food,
        'sort_order' => 0,
    ]);

    MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Capricciosa',
        'price' => null,
        'price_variants' => [
            ['label' => '25 cm', 'price' => 590],
            ['label' => '35 cm', 'price' => 790],
        ],
        'sort_order' => 0,
        'is_active' => true,
        'featured' => true,
    ]);

    SitePageCache::featuredMenuItems();

    $cached = SitePageCache::featuredMenuItems();

    expect($cached)->toHaveCount(1)
        ->and($cached->first()->price_variants)->toBe([
            ['label' => '25 cm', 'price' => 590],
            ['label' => '35 cm', 'price' => 790],
        ]);

    $this->get(route('delivery'))->assertOk();
});

test('featured menu cache hydrates legacy array json attributes', function (): void {
    Cache::flush();

    Cache::forever(SitePageCache::FEATURED_MENU_KEY, [
        'v' => 1,
        'data' => [[
            'item' => [
                'id' => 1,
                'category_id' => 1,
                'name' => 'Legacy Pizza',
                'description' => null,
                'weight' => null,
                'price' => null,
                'price_variants' => [
                    ['label' => '25 cm', 'price' => 500],
                ],
                'image' => null,
                'featured' => true,
                'contents' => null,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ],
            'category' => [
                'id' => 1,
                'name' => 'Pizza',
                'slug' => 'pizza',
                'type' => 'food',
                'sort_order' => 0,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ],
        ]],
    ]);

    $items = SitePageCache::featuredMenuItems();

    expect($items->first()->name)->toBe('Legacy Pizza')
        ->and($items->first()->price_variants)->toBe([
            ['label' => '25 cm', 'price' => 500],
        ]);
});

test('site scripts defer entries are scoped per route', function (): void {
    $this->get(route('home'))->assertOk();

    expect(SiteScripts::deferEntries())->toBe([
        'resources/js/defer-reveal.js',
        'resources/js/defer-carousel.js',
        'resources/js/defer-cart.js',
        'resources/js/defer-ordering-availability.js',
    ]);

    $this->get(route('menu'))->assertOk();

    expect(SiteScripts::deferEntries())->toBe([
        'resources/js/defer-reveal.js',
        'resources/js/defer-cart.js',
        'resources/js/defer-ordering-availability.js',
        'resources/js/defer-catalog.js',
    ]);

    $this->get(route('about'))->assertOk();

    expect(SiteScripts::deferEntries())->toBe([
        'resources/js/defer-reveal.js',
    ]);
});

test('home page loads only route scoped defer scripts', function (): void {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('defer-reveal', false)
        ->assertSee('defer-carousel', false)
        ->assertSee('defer-cart', false)
        ->assertDontSee('defer-catalog', false);
});

test('menu page loads catalog and cart defer scripts', function (): void {
    $response = $this->get(route('menu'));

    $response->assertOk()
        ->assertSee('defer-reveal', false)
        ->assertSee('defer-catalog', false)
        ->assertSee('defer-cart', false)
        ->assertDontSee('defer-carousel', false);
});
