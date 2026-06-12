<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::controller(PageController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
    Route::get('/jelovnik', 'menu')->name('menu');
    Route::get('/karta-pica', 'drinks')->name('drinks');
    Route::get('/proslave', 'celebrations')->name('celebrations');
    Route::get('/o-nama', 'about')->name('about');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
