<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SEO i deljenje na društvenim mrežama
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'site_name' => 'Rispetto',
        'default_title' => 'Rispetto — italijanski restoran u Batajnici',
        'default_description' => 'Restoran Rispetto u Batajnici — autentična italijanska kuhinja, jelovnik, karta pića, proslave i dostava hrane.',
        'og_image' => 'images/site/og-image.jpg',
        'og_image_width' => 1200,
        'og_image_height' => 630,
        'locale' => 'sr_RS',
        'robots' => 'index, follow',
        'theme_color' => '#050505',
        'twitter_site' => null,

        'pages' => [
            'home' => [
                'title' => 'Početna',
                'description' => 'Rispetto — autentična italijanska kuhinja u Batajnici. Istražite jelovnik, kartu pića i rezervišite sto za nezaboravno iskustvo.',
            ],
            'menu' => [
                'title' => 'Jelovnik',
                'description' => 'Pogledajte kompletan jelovnik restorana Rispetto — paste, pice, domaća jela i sezonski specijaliteti pripremljeni od svežih sastojaka.',
            ],
            'drinks' => [
                'title' => 'Karta pića',
                'description' => 'Karta pića restorana Rispetto — domaća vina, kokteli i osvežavajući napici savršeni uz jelo.',
            ],
            'celebrations' => [
                'title' => 'Proslave',
                'description' => 'Organizujte rođendane, porodične proslave i poslovne događaje u restoranu Rispetto u Batajnici.',
            ],
            'delivery' => [
                'title' => 'Dostava',
                'description' => 'Online poručivanje, lično preuzimanje i dostava hrane u restoranu Rispetto u Batajnici.',
            ],
            'about' => [
                'title' => 'O nama',
                'description' => 'Upoznajte restoran Rispetto — našu priču, vrednosti i tim koji svakog dana priprema jela sa pažnjom i ljubavlju.',
            ],
        ],

        'sitemap' => [
            ['route' => 'home', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['route' => 'menu', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['route' => 'drinks', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['route' => 'celebrations', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['route' => 'delivery', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['route' => 'about', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ],
    ],

    'phone' => '063/72-88-155',
    'phone_href' => 'tel:+381637288155',

    /*
    |--------------------------------------------------------------------------
    | Jelovnik — kategorija pice i veličine
    |--------------------------------------------------------------------------
    */
    'menu' => [
        'pizza_category_slug' => 'pizza',
        'pizza_sizes' => [
            ['key' => '25', 'label' => '25 cm'],
            ['key' => '35', 'label' => '35 cm'],
            ['key' => '50', 'label' => '50 cm'],
        ],
        'category_hours_note' => [
            'dorucak' => 'do 13 h',
            'sendvici' => 'do 13 h',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Responsive slike (storage + statične)
    |--------------------------------------------------------------------------
    */
    'images' => [
        'responsive_widths' => [640, 1024, 1280],
        'webp_quality' => 82,
        'jpeg_quality' => 85,
        'placeholders' => [
            'food' => 'images/site/placeholder-food.jpg',
            'drinks' => 'images/site/placeholder-drinks.jpg',
        ],
    ],

    'brand_logo' => 'images/site/rispetto-logo.svg',

    'contact' => [
        'name' => 'Rispetto Ristorante',
        'email' => 'rispettoristorante@office.com',
        'address' => 'Stevana Dubajića 5, Batajnica',
        'maps_link' => 'https://maps.google.com/?q=Stevana+Dubajića+5,+Batajnica',
        'maps_embed_url' => 'https://maps.google.com/maps?q=Stevana+Dubajića+5,+Batajnica,+Beograd&hl=sr&z=16&output=embed',
    ],

    'opening_hours' => [
        ['label' => 'Pon - Pet', 'hours' => '08:00 - 23:00'],
        ['label' => 'Subota', 'hours' => '08:00 - 23:00'],
        ['label' => 'Nedelja', 'hours' => 'Neradan dan', 'closed' => true],
    ],

    'footer' => [
        'tagline' => 'Najbolji trenuci su uz dobru klopu i još bolje društvo. Zato je Rispetto mesto gde možete da se opustite, usporite i uživate u svom danu.',
    ],

    'social' => [
        ['label' => 'Facebook', 'icon' => 'facebook', 'url' => env('RISPETTO_FACEBOOK_URL', 'https://www.facebook.com/people/Rispetto-Batajnica/61561537636784/')],
        ['label' => 'Instagram', 'icon' => 'instagram', 'url' => env('RISPETTO_INSTAGRAM_URL', 'https://www.instagram.com/ristorante.rispetto')],
        ['label' => 'TikTok', 'icon' => 'tiktok', 'url' => env('RISPETTO_TIKTOK_URL', '#')],
    ],

    /*
    |--------------------------------------------------------------------------
    | QR kodovi (Filament admin — preuzimanje SVG)
    |--------------------------------------------------------------------------
    */
    'qr' => [
        'size' => 400,
        'margin' => 1,
        'colors' => [
            'background' => [255, 255, 255],
            'foreground' => [0, 0, 0],
        ],
    ],

    'partner' => [
        'name' => 'Alideda',
        'logo' => 'images/site/partner-alideda.svg',
        'url' => 'https://alideda.rs',
    ],

    'credits' => [
        'label' => 'Frontrow Digital',
        'url' => 'https://frdigitalagency.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Glavna navigacija (header)
    |--------------------------------------------------------------------------
    */
    'navigation' => [
        'left' => [
            ['label' => 'Jelovnik', 'route' => 'menu'],
            ['label' => 'Karta pića', 'route' => 'drinks'],
            ['label' => 'Proslave', 'route' => 'celebrations'],
        ],
        'right' => [
            ['label' => 'Dostava', 'route' => 'delivery'],
            ['label' => 'O nama', 'route' => 'about'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Footer navigacija
    |--------------------------------------------------------------------------
    */
    'footer_navigation' => [
        ['label' => 'Jelovnik', 'route' => 'menu'],
        ['label' => 'Karta pića', 'route' => 'drinks'],
        ['label' => 'Proslave', 'route' => 'celebrations'],
        ['label' => 'Dostava', 'route' => 'delivery'],
        ['label' => 'O nama', 'route' => 'about'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sekcije na početnoj stranici (redosled = redosled prikaza)
    |--------------------------------------------------------------------------
    */
    'features' => [
        [
            'icon' => 'tradition',
            'title' => 'Ukusi tradicije',
            'description' => 'Jela pripremamo po starim recepturama. Držimo se proverenih načina pripreme koji daju onaj pravi, domaći ukus.',
        ],
        [
            'icon' => 'ingredients',
            'title' => 'Sveži sastojci',
            'description' => 'Biramo isključivo najsvežije namirnice koje diktiraju karakter naše kuhinje jer verujemo da se prava razlika u kvalitetu oseti odmah.',
        ],
        [
            'icon' => 'service',
            'title' => 'Vrhunska usluga',
            'description' => 'Vaš ugođaj je naš prioritet. Svaki detalj je osmišljen da vaš boravak učini da se kod nas osećate potpuno opušteno, kao kod kuće.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google recenzije (prikaz na početnoj)
    |--------------------------------------------------------------------------
    | Ručno održavanje ili kasnije sync preko Google Places API → bazu.
    */
    'testimonials' => [
        [
            'name' => 'Jasmina Veličković',
            'initial' => 'J',
            'avatar_color' => 'bg-violet-600',
            'text' => 'Prelepo iskustvo! Hrana je fenomenalna, a osoblje je neverovatno ljubazno. Definitivno se vraćamo.',
        ],
        [
            'name' => 'Ivana Soldić',
            'initial' => 'I',
            'avatar_color' => 'bg-sky-600',
            'text' => 'Odličan ambijent i vrhunska usluga. Svaki detalj je osmišljen da se osećate posebno. Topla preporuka!',
        ],
        [
            'name' => 'Sanja Peović',
            'initial' => 'S',
            'avatar_color' => 'bg-amber-700',
            'text' => 'Restoran u kome zaista možete osetiti pažnju posvećenu gostu i ljubav prema kuvanju. Hrana je savršena.',
        ],
        [
            'name' => 'Marko Jovanović',
            'initial' => 'M',
            'avatar_color' => 'bg-emerald-700',
            'text' => 'Najbolja italijanska kuhinja u okolini. Pasta je kao u Italiji, a karta pića odlično prati jela.',
        ],
        [
            'name' => 'Ana Nikolić',
            'initial' => 'A',
            'avatar_color' => 'bg-rose-700',
            'text' => 'Proslavili smo rođendan i sve je bilo besprekorno — od menija do organizacije. Hvala celom timu!',
        ],
    ],

    'home_sections' => [
        'home.hero',
        'home.features',
        'shared.featured-menu',
        'home.drinks-preview',
        'home.celebrations-preview',
        'shared.testimonials',
        'shared.visit',
    ],

];
