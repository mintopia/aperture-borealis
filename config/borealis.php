<?php

return [
    'client' => [
        'defaults' => [
            'interval' => env('BOREALIS_CLIENT_DEFAULT_INTERVAL', 5),
            'expires_in' => env('BOREALIS_CLIENT_DEFAULT_EXPIRES_IN', 900),
        ],
    ],
    'usercode' => [
        'charset' => env('BOREALIS_USERCODE_CHARSET', 'ABCDEFGHJKLMNPWRSTUVWXYZ23456789'),
        'length' => env('BOREALIS_USERCODE_LENGTH', 4),
    ],
    'jwt' => [
        'key_directory' => storage_path('jwt'),
        'private_key' => storage_path('jwt/private.pem'),
        'public_key' => storage_path('jwt/public.pem'),
        'kid' => env('BOREALIS_JWT_KID', 'borealis-ipv6-1'),
    ],
];
