<?php

return [
    env('OVH_EU_ENDPOINT', 'ovh-eu') => [
        'name' => 'OVHcloud Europe',
        'application_key' => env('OVH_EU_APPLICATION_KEY'),
        'application_secret' => env('OVH_EU_APPLICATION_SECRET'),
        'endpoint' => env('OVH_EU_ENDPOINT', 'ovh-eu'),
        'demo_accounts' => json_decode(env('OVH_EU_DEMO_ACCOUNTS', '{}'), true),
        'flag' => 'eu',
        'domain' => 'ovhcloud.com',
    ],
    env('OVH_CA_ENDPOINT', 'ovh-ca') => [
        'name' => 'OVHcloud Canada',
        'application_key' => env('OVH_CA_APPLICATION_KEY'),
        'application_secret' => env('OVH_CA_APPLICATION_SECRET'),
        'endpoint' => env('OVH_CA_ENDPOINT', 'ovh-ca'),
        'demo_accounts' => json_decode(env('OVH_CA_DEMO_ACCOUNTS', '{}'), true),
        'flag' => 'ca',
        'domain' => 'ovhcloud.ca',
    ],
    env('OVH_US_ENDPOINT', 'ovh-us') => [
        'name' => 'OVHcloud US',
        'application_key' => env('OVH_US_APPLICATION_KEY'),
        'application_secret' => env('OVH_US_APPLICATION_SECRET'),
        'endpoint' => env('OVH_US_ENDPOINT', 'ovh-us'),
        'demo_accounts' => json_decode(env('OVH_US_DEMO_ACCOUNTS', '{}'), true),
        'flag' => 'us',
        'domain' => 'us.ovhcloud.com',
    ],
];