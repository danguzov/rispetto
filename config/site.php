<?php

return [

    'phone' => '063/72-88-155',
    'phone_href' => 'tel:+381637288155',

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
        ['label' => 'Facebook', 'icon' => 'facebook', 'url' => '#'],
        ['label' => 'Instagram', 'icon' => 'instagram', 'url' => '#'],
        ['label' => 'TikTok', 'icon' => 'tiktok', 'url' => '#'],
    ],

    'partner' => [
        'name' => 'Alideda',
        'logo' => 'images/site/partner-alideda.png',
    ],

    'credits' => [
        'label' => 'Frontrow Digital',
        'url' => 'https://frontrowdigital.rs',
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
            ['label' => 'Dostava', 'url' => '#dostava'],
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
        ['label' => 'Dostava', 'url' => '#dostava'],
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
        'hero',
        'features',
        'about-preview',
        'featured-menu',
        'drinks-preview',
        'celebrations-preview',
        'testimonials',
        'visit',
    ],

];
