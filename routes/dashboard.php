<?php

use App\Livewire\Auth\Login;
use App\Livewire\Dashboard\OrderHistoryIndex;
use App\Livewire\Dashboard\OrderIndex;
use App\Livewire\Dashboard\OrderShow;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')
    ->name('dashboard.')
    ->group(function (): void {
        Route::middleware('guest')->group(function (): void {
            Route::get('login', Login::class)->name('login');
        });

        Route::middleware(['auth', 'verified'])->group(function (): void {
            Route::redirect('/', '/dashboard/porudzbine')->name('home');

            Route::get('porudzbine', OrderIndex::class)->name('orders.index');
            Route::get('porudzbine/istorija', OrderHistoryIndex::class)->name('orders.history');
            Route::get('porudzbine/{order}', OrderShow::class)->name('orders.show');
        });
    });
