<?php

namespace App;

use Ovh\Api as OvhApi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class OvhApiUserProvider implements UserProvider
{
    public function apiLogin($consumerKey)
    {
        if (Cache::has('session_kill_'.session()->getId())) {
            return null;
        }

        $client = new OvhApi(
            config('ovh.application_key'),
            config('ovh.application_secret'),
            config('ovh.endpoint'),
            $consumerKey,
        );

        try {
            $client->get('/auth/currentCredential');
        } catch (RequestException $e) {
            return null;
        }

        return new OvhApiUser([
            'id' => $consumerKey,
            'consumerKey' => $consumerKey,
            'remember_token' => Str::random(60),
            'ovhApi' => $client,
        ]);
    }

    public function retrieveById($identifier)
    {
        return $this->apiLogin($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (! array_key_exists('consumerKey', $credentials)) {
            return null;
        }
        return $this->apiLogin($credentials['consumerKey']);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return true;
    }
}