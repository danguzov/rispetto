<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Predjela', 'slug' => 'predjela', 'sort_order' => 1],
            ['name' => 'Glavna jela', 'slug' => 'glavna-jela', 'sort_order' => 2],
            ['name' => 'Dezerti', 'slug' => 'dezerti', 'sort_order' => 3],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'type' => ProductType::Food,
                    'sort_order' => $category['sort_order'],
                ],
            );
        }
    }
}
