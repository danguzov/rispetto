<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderPushSubscriptionController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::controller(PageController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
    Route::get('/jelovnik', 'menu')->name('menu');
    Route::get('/karta-pica', 'drinks')->name('drinks');
    Route::get('/proslave', 'celebrations')->name('celebrations');
    Route::get('/dostava', 'delivery')->name('delivery');
    Route::get('/o-nama', 'about')->name('about');
});

Route::controller(CartController::class)->group(function (): void {
    Route::get('/korpa', 'show')
        ->middleware('ordering.enabled')
        ->name('cart.show');
    Route::post('/korpa/dodaj', 'add')
        ->middleware('ordering.open')
        ->name('cart.add');
    Route::patch('/korpa/stavka/{key}', 'update')
        ->middleware('ordering.enabled')
        ->name('cart.update');
    Route::delete('/korpa/stavka/{key}', 'remove')
        ->middleware('ordering.enabled')
        ->name('cart.remove');
});

Route::get('/narucivanje', [CheckoutController::class, 'show'])
    ->middleware('ordering.enabled')
    ->name('checkout.show');
Route::post('/narucivanje', [CheckoutController::class, 'store'])
    ->middleware('ordering.open')
    ->name('checkout.store');

Route::get('/porudzbina/{token}', [OrderTrackingController::class, 'show'])->name('order.tracking');
Route::get('/porudzbina/{token}/status', [OrderTrackingController::class, 'status'])->name('order.tracking.status');
Route::post('/porudzbina/{token}/push', [OrderPushSubscriptionController::class, 'store'])->name('order.push.subscribe');

require __DIR__.'/dashboard.php';

require __DIR__.'/settings.php';
