<?php

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Drink;
use App\Models\MenuItem;
use App\Models\RestaurantSettings;
use App\Services\Ordering\RestaurantSettingsService;
use App\Support\SitePageCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('hidden menu category and its dishes stay off the public menu', function (): void {
    Cache::flush();

    $visible = Category::query()->create([
        'name' => 'Vidljiva kategorija',
        'slug' => 'vidljiva-kategorija',
        'type' => ProductType::Food,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    MenuItem::query()->create([
        'category_id' => $visible->id,
        'name' => 'Vidljivo jelo',
        'price' => 900,
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $hidden = Category::query()->create([
        'name' => 'Skrivena kategorija',
        'slug' => 'skrivena-kategorija',
        'type' => ProductType::Food,
        'sort_order' => 2,
        'is_active' => false,
    ]);

    MenuItem::query()->create([
        'category_id' => $hidden->id,
        'name' => 'Jelo iz skrivene kategorije',
        'price' => 1100,
        'sort_order' => 0,
        'is_active' => true,
        'featured' => true,
    ]);

    $categories = SitePageCache::menuCategories();

    expect($categories)->toHaveCount(1)
        ->and($categories->first()->name)->toBe('Vidljiva kategorija')
        ->and(SitePageCache::featuredMenuItems())->toHaveCount(0);

    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('Vidljiva kategorija')
        ->assertSee('Vidljivo jelo')
        ->assertDontSee('Skrivena kategorija')
        ->assertDontSee('Jelo iz skrivene kategorije');
});

test('hidden dish stays off the menu while the rest of the category remains', function (): void {
    Cache::flush();

    $category = Category::query()->create([
        'name' => 'Paste',
        'slug' => 'paste-vidljivost',
        'type' => ProductType::Food,
        'sort_order' => 0,
        'is_active' => true,
    ]);

    MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Carbonara',
        'price' => 890,
        'sort_order' => 0,
        'is_active' => true,
    ]);

    MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Skrivena lazanja',
        'price' => 990,
        'sort_order' => 1,
        'is_active' => false,
    ]);

    $categories = SitePageCache::menuCategories();

    expect($categories)->toHaveCount(1)
        ->and($categories->first()->menuItems)->toHaveCount(1)
        ->and($categories->first()->menuItems->first()->name)->toBe('Carbonara');

    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('Carbonara')
        ->assertDontSee('Skrivena lazanja');
});

test('hidden drink category stays off the drinks page', function (): void {
    Cache::flush();

    $hidden = Category::query()->create([
        'name' => 'Skrivena vina',
        'slug' => 'skrivena-vina',
        'type' => ProductType::Drink,
        'sort_order' => 0,
        'is_active' => false,
    ]);

    Drink::query()->create([
        'category_id' => $hidden->id,
        'name' => 'Skriveni prosek',
        'price' => 450,
        'sort_order' => 0,
        'is_active' => true,
    ]);

    expect(SitePageCache::drinkCategories())->toHaveCount(0);

    $this->get(route('drinks'))
        ->assertOk()
        ->assertDontSee('Skrivena vina')
        ->assertDontSee('Skriveni prosek');
});

test('cart rejects a dish from a hidden category and a hidden dish', function (): void {
    config(['ordering.enabled' => true]);

    RestaurantSettings::query()->create([
        'accepting_orders' => true,
        'delivery_enabled' => true,
        'pickup_enabled' => true,
    ]);

    app(RestaurantSettingsService::class)->clearCache();

    $hiddenCategory = Category::query()->create([
        'name' => 'Skrivena',
        'slug' => 'skrivena-korpa',
        'type' => ProductType::Food,
        'sort_order' => 0,
        'is_active' => false,
    ]);

    $hiddenCategoryItem = MenuItem::query()->create([
        'category_id' => $hiddenCategory->id,
        'name' => 'Skriveno jelo',
        'price' => 700,
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $visibleCategory = Category::query()->create([
        'name' => 'Vidljiva',
        'slug' => 'vidljiva-korpa',
        'type' => ProductType::Food,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $hiddenItem = MenuItem::query()->create([
        'category_id' => $visibleCategory->id,
        'name' => 'Pojedinacno skriveno',
        'price' => 800,
        'sort_order' => 0,
        'is_active' => false,
    ]);

    $this->postJson(route('cart.add'), [
        'menu_item_id' => $hiddenCategoryItem->id,
        'quantity' => 1,
    ])->assertStatus(422)
        ->assertJsonValidationErrors('menu_item_id');

    $this->postJson(route('cart.add'), [
        'menu_item_id' => $hiddenItem->id,
        'quantity' => 1,
    ])->assertStatus(422)
        ->assertJsonValidationErrors('menu_item_id');
});
