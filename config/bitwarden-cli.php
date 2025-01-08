<?php

// config for Aleex1848/LaravelBitwardenCli
return [
    'default_identifier' => 'name',
    'password' => env('BITWARDEN_PASSWORD'),
    'url' => env('BITWARDEN_URL'),
    'lock_after_request' => env('BITWARDEN_LOCK_AFTER_REQUEST',true),
    'cache' => [
        'enabled' => env('BITWARDEN_CACHE_ENABLED',true),
        'ttl_seconds' => env('BITWARDEN_CACHE_TTL',3600),
    ],
    'tests' => [
        'count_test_items' => 20,
        'collection_id' => env('BITWARDEN_TEST_COLLECTION_ID','b7b95c48-f138-4171-9869-71fd1c48bf3d'),
        'organization_id' => env('BITWARDEN_TEST_ORGANIZATION_ID','28c97e07-1b75-4ef4-8f41-8f739649a5d2'),
    ]
];
