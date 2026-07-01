<?php

return [

    'enabled' => env('ORDERING_ENABLED', true),

    'cart' => [
        'session_key' => 'cart',
        'max_quantity_per_item' => 20,
    ],

    'order_number' => [
        'prefix' => 'R',
    ],

    'closed_message' => 'Trenutno ne primamo online porudžbine. Pokušajte ponovo kasnije.',

    'default_eta_minutes' => 45,

    'eta_presets' => [15, 30, 45, 60, 90],

    'delivery_area' => 'batajnica',

    'delivery_areas' => [
        'batajnica' => [
            'label' => 'Batajnica',
            'fee' => 250,
        ],
    ],

    'delivery_free_above' => 3000,

    'delivery_minimum_order_amount' => 700,

    'auto_status' => [
        'enabled' => env('ORDERING_AUTO_STATUS', true),
        'preparing_after_percent' => 15,
        'preparing_min_seconds' => 60,
        'complete_after_ready_minutes' => 2,
    ],

    'webpush' => [
        'vapid_subject' => env('VAPID_SUBJECT', 'mailto:rispettoristorante@office.com'),
        'vapid_public_key' => env('VAPID_PUBLIC_KEY'),
        'vapid_private_key' => env('VAPID_PRIVATE_KEY'),
    ],

];
