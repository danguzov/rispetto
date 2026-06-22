<?php

namespace App\Http\Controllers;

use App\Support\SitePageCache;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'sectionData' => [
                'shared.featured-menu' => [
                    'items' => SitePageCache::featuredMenuItems(),
                ],
            ],
        ]);
    }

    public function menu(): View
    {
        return view('pages.menu', [
            'categories' => SitePageCache::menuCategories(),
        ]);
    }

    public function drinks(): View
    {
        return view('pages.drinks', [
            'categories' => SitePageCache::drinkCategories(),
            'uncategorizedDrinks' => SitePageCache::uncategorizedDrinks(),
        ]);
    }

    public function celebrations(): View
    {
        return view('pages.celebrations');
    }

    public function delivery(): View
    {
        return view('pages.delivery', [
            'featuredMenuItems' => SitePageCache::featuredMenuItems(),
        ]);
    }

    public function about(): View
    {
        return view('pages.about');
    }
}
