<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Drink;
use App\Models\MenuItem;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'sectionData' => [
                'featured-menu' => [
                    'items' => MenuItem::query()
                        ->where('is_active', true)
                        ->where('featured', true)
                        ->with('category')
                        ->orderBy('sort_order')
                        ->limit(4)
                        ->get(),
                ],
            ],
        ]);
    }

    public function menu(): View
    {
        $categories = Category::query()
            ->where('type', ProductType::Food)
            ->with(['menuItems' => function ($query): void {
                $query->where('is_active', true)
                    ->with('tags')
                    ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return view('pages.menu', compact('categories'));
    }

    public function drinks(): View
    {
        $categories = Category::query()
            ->where('type', ProductType::Drink)
            ->with(['drinks' => function ($query): void {
                $query->where('is_active', true)
                    ->with('tags')
                    ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        $uncategorizedDrinks = Drink::query()
            ->where('is_active', true)
            ->whereNull('category_id')
            ->with('tags')
            ->orderBy('sort_order')
            ->get();

        return view('pages.drinks', compact('categories', 'uncategorizedDrinks'));
    }

    public function celebrations(): View
    {
        return view('pages.celebrations');
    }

    public function about(): View
    {
        return view('pages.about');
    }
}
