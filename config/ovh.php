<?php

return [
    'application_key' => env('OVH_APPLICATION_KEY'),
    'application_secret' => env('OVH_APPLICATION_SECRET'),
    'endpoint' => env('OVH_ENDPOINT'),
    'demo_accounts' => json_decode(env('OVH_DEMO_ACCOUNTS', '{}'), true),
];