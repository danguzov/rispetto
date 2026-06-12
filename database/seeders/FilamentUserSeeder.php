<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class FilamentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => env('FILAMENT_ADMIN_EMAIL', 'admin@rispetto.rs')],
            [
                'name' => env('FILAMENT_ADMIN_NAME', 'Admin'),
                'password' => env('FILAMENT_ADMIN_PASSWORD', 'password'),
                'email_verified_at' => now(),
            ],
        );
    }
}
