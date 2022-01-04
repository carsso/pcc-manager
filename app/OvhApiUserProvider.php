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
    public function apiLogin(String $endpoint, String $consumerKey)
    {
        if (Cache::has('session_kill_'.session()->getId())) {
            return null;
        }
        if(!config('ovh.'.$endpoint)) {
            return null;
        }
        if(!config('ovh.'.$endpoint.'.application_key')) {
            return null;
        }
        if(!config('ovh.'.$endpoint.'.application_secret')) {
            return null;
        }

        $client = new OvhApi(
            config('ovh.'.$endpoint.'.application_key'),
            config('ovh.'.$endpoint.'.application_secret'),
            $endpoint,
            $consumerKey,
        );

        try {
            $client->get('/auth/currentCredential');
        } catch (RequestException $e) {
            return null;
        }

        session(['endpoint' => $endpoint]);

        return new OvhApiUser([
            'id' => $consumerKey,
            'consumerKey' => $consumerKey,
            'endpoint' => $endpoint,
            'remember_token' => Str::random(60),
            'ovhApi' => $client,
        ]);
    }

    public function retrieveById($identifier)
    {
        return $this->apiLogin(session('endpoint'), $identifier);
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
        if (! array_key_exists('endpoint', $credentials)) {
            return null;
        }
        if (! array_key_exists('consumerKey', $credentials)) {
            return null;
        }
        return $this->apiLogin($credentials['endpoint'], $credentials['consumerKey']);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return true;
    }
}