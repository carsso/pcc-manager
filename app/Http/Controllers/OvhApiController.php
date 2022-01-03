<?php

namespace App\Http\Controllers;

use Exception;
use Ovh\Api as OvhApi;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Exception\RequestException;

class OvhApiController extends Controller
{
    public function login(Request $request)
    {
        $ovhApi = new OvhApi(
            config('ovh.application_key'),
            config('ovh.application_secret'),
            config('ovh.endpoint'),
        );

        /*
        $rights = [
            [
                'method'    => 'GET',
                'path'      => '/*'
            ],
            [
                'method'    => 'POST',
                'path'      => '/*'
            ],
            [
                'method'    => 'PUT',
                'path'      => '/*'
            ],
            [
                'method'    => 'DELETE',
                'path'      => '/*'
            ],
        ];
        */
        $rights = [
            [
                'method'    => 'GET',
                'path'      => '/dedicatedCloud*'
            ],
        ];
        $redirect = route('login.redirect');
        $credentials = $ovhApi->requestCredentials($rights, $redirect);
        session(['loginConsumerKey' => $credentials['consumerKey']]);

        return redirect($credentials['validationUrl']);
    }

    public function redirect()
    {
        if($consumerKey = session('loginConsumerKey'))
        {
            return $this->tryLogin($consumerKey);
        }
        return redirect()->route('pcc');
    }

    public function token(String $token)
    {
        return $this->tryLogin($token);
    }

    private function tryLogin(String $consumerKey)
    {
        session()->invalidate();
        session()->regenerateToken();
        
        if (Auth::attempt(['consumerKey' => $consumerKey])) {
            return redirect()->route('pcc');
        }

        return $this->backWithError('Invalid credentials');
    }

    public function logout(Request $request)
    {
        Cache::put('session_kill_'.session()->getId(), true, 7*24*3600);

        session()->invalidate();
        session()->regenerateToken();
        Auth::guard()->logout();

        return redirect()->route('home');
    }

    public function request(Request $request, ?string $uri = '/')
    {
        if($request->getMethod() === 'OPTIONS') {
            return response(['message' => 'OPTIONS request is always allowed'], 200);
        }

        $batch = null;
        if($request->header('HTTP_X_OVH_BATCH'))
        {
            $batch = $request->header('HTTP_X_OVH_BATCH');
        }

        if($request->has('batch')) {
            $batch = $request->query('batch');
        }

        if($request->getQueryString()) {
            $uri .= '?'.$request->getQueryString();
        }

        $content = null;
        $rawJson = $request->getContent();
        //$rawJson = file_get_contents("php://input");
        if($rawJson) {
            $json = preg_replace('/(\w+):/i', '"\1":', $rawJson);
            $content = json_decode($json, true);
        }

        $method = strtolower($request->getMethod());
        $headers = null;
        if($batch) {
            $headers = ['X-OVH-BATCH' => $batch];
        }

        $ovhApi = $request->user()->ovhApi;
        try {
            $result = $ovhApi->$method($uri, $content, $headers);
            return response($result, 200);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            if ($response != null) {
                $statusCode = $response->getStatusCode();
                $contentType = $response->getHeader('Content-Type');
                $body = $response->getBody()->__toString();
                return response($body, $statusCode, ['Content-Type' => $contentType]);
            } else {
                return response(['message' => $exception->getMessage()], 500);
            }
        }
    }
}
