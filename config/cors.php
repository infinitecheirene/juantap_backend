<?php

return [
    'paths' => ['api/*', 'register'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'https://juantap-profile.vercel.app',
    ],

    'allowed_headers' => ['*'],

    'supports_credentials' => false,
];

