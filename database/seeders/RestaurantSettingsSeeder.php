<?php

namespace Database\Seeders;

use App\Models\RestaurantSettings;
use Illuminate\Database\Seeder;

class RestaurantSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RestaurantSettings::query()->firstOrCreate([], [
            'accepting_orders' => true,
            'closed_message' => null,
            'delivery_enabled' => true,
            'pickup_enabled' => true,
            'min_order_amount' => null,
        ]);
    }
}
