<?php

namespace App;

use App\OvhApi;
use Ovh\Api as OvhApiLegacy;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use League\OAuth2\Client\Token\AccessToken;

class OvhApiUserProvider implements UserProvider
{
    public function apiLogin(String $endpoint, ?String $code = null, ?String $state = null)
    {
        if (Cache::has('session_kill_'.session()->getId())) {
            return null;
        }
        if(!OvhApi::checkEndpoint($endpoint, false)) {
            return null;
        }

        $ovhApi = new OvhApi($endpoint, session('state'));
        if(!$ovhApi->doLogin($code, $state)) {
            return null;
        }

        session(['endpoint' => $endpoint]);
        session(['token' => $ovhApi->getToken()]);
        return $this->prepareUser($endpoint, $ovhApi->getToken());
    }

    public function prepareUser(String $endpoint, $token)
    {
        if (Cache::has('session_kill_'.session()->getId())) {
            return null;
        }
        if(!OvhApi::checkEndpoint($endpoint, false)) {
            return null;
        }

        $ovhApi = new OvhApi($endpoint, $token);
        session(['endpoint' => $endpoint]);
        session(['token' => $ovhApi->getToken()]);
        
        $currentCredential = null;
        try {
            $currentCredential = $ovhApi->get('/v1/auth/currentCredential');
        } catch (RequestException $e) {
        }

        try {
            $userinfo = $ovhApi->get('/v1/me');
        } catch (RequestException $e) {
            return null;
        }

        return new OvhApiUser([
            'id' => Str::random(60),
            'currentCredential' => $currentCredential,
            'userinfo' => $userinfo,
            'endpoint' => $endpoint,
            'remember_token' => Str::random(60),
            'ovhApi' => $ovhApi,
        ]);
    }

    public function retrieveById($identifier)
    {
        return $this->prepareUser(session('endpoint'), session('token'));
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

        if(array_key_exists('token', $credentials)) {
            return $this->prepareUser($credentials['endpoint'], $credentials['token']);
        }

        if (! array_key_exists('code', $credentials)) {
            return null;
        }
        if (! array_key_exists('state', $credentials)) {
            return null;
        }
        return $this->apiLogin($credentials['endpoint'], $credentials['code'], $credentials['state']);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return true;
    }
}