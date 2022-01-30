<?php

return [
    env('OVH_EU_ENDPOINT', 'ovh-eu') => [
        'name' => 'OVHcloud Europe',
        'application_key' => env('OVH_EU_APPLICATION_KEY'),
        'application_secret' => env('OVH_EU_APPLICATION_SECRET'),
        'client_id' => env('OVH_EU_OAUTH_CLIENT_ID'),
        'client_secret' => env('OVH_EU_OAUTH_CLIENT_SECRET'),
        'endpoint' => env('OVH_EU_ENDPOINT', 'ovh-eu'),
        'flag' => 'eu',
        'domain' => 'ovhcloud.com',
    ],
    env('OVH_CA_ENDPOINT', 'ovh-ca') => [
        'name' => 'OVHcloud Canada',
        'application_key' => env('OVH_CA_APPLICATION_KEY'),
        'application_secret' => env('OVH_CA_APPLICATION_SECRET'),
        'client_id' => env('OVH_CA_OAUTH_CLIENT_ID'),
        'client_secret' => env('OVH_CA_OAUTH_CLIENT_SECRET'),
        'endpoint' => env('OVH_CA_ENDPOINT', 'ovh-ca'),
        'flag' => 'ca',
        'domain' => 'ovhcloud.ca',
    ],
    env('OVH_US_ENDPOINT', 'ovh-us') => [
        'name' => 'OVHcloud US',
        'application_key' => env('OVH_US_APPLICATION_KEY'),
        'application_secret' => env('OVH_US_APPLICATION_SECRET'),
        'client_id' => env('OVH_US_OAUTH_CLIENT_ID'),
        'client_secret' => env('OVH_US_OAUTH_CLIENT_SECRET'),
        'endpoint' => env('OVH_US_ENDPOINT', 'ovh-us'),
        'flag' => 'us',
        'domain' => 'us.ovhcloud.com',
    ],
];