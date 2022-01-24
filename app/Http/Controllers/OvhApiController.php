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
    public function loginReadOnly(Request $request, String $endpoint)
    {
        return $this->login($request, $endpoint, 'read-only');
    }

    private function _rightsDefinitions(Array $paths, Array $methods) {
        $rights = [];
        foreach ($paths as $path) {
            foreach ($methods as $method) {
                $rights[] = [
                    'method' => $method,
                    'path'   => $path,
                ];
            }
        }
        return $rights;
    }

    public function login(Request $request, String $endpoint, String $rightsType = 'read-write')
    {
        $this->checkEndpoint($endpoint);

        $ovhApi = new OvhApi(
            config('ovh.'.$endpoint.'.application_key'),
            config('ovh.'.$endpoint.'.application_secret'),
            $endpoint,
        );

        $paths = [
            '/dedicatedCloud*',
            '/me*',
            '/order*',
            '/service*',
            '/services*',
            '/vrack*',
        ];

        $readOnly = $this->_rightsDefinitions($paths, ['GET']);
        $readOnly[] = [ // Exception for metrics token
            'method' => 'POST',
            'path'   => '/dedicatedCloud/*/user/*/metricsToken',
        ];

        $readWrite = $this->_rightsDefinitions($paths, ['GET', 'POST', 'PUT', 'DELETE']);

        $rightsDefinitions = [
            'read-only' => $readOnly,
            'read-write' => $readOnly, // For now force read-only
        ];
        if(!isset($rightsDefinitions[$rightsType]) || !is_array($rightsDefinitions[$rightsType])) {
            abort(404, 'Unknown rights type: '.$rightsType);
        }

        $rights = $rightsDefinitions[$rightsType];
        $redirect = route('login.redirect', ['endpoint' => $endpoint]);
        $credentials = $ovhApi->requestCredentials($rights, $redirect);
        session(['loginConsumerKey' => $credentials['consumerKey']]);

        return redirect($credentials['validationUrl']);
    }

    protected function checkEndpoint(String $endpoint)
    {
        if(!config('ovh.'.$endpoint)) {
            abort(404, 'Endpoint '.$endpoint.' not found');
        }
        if(!config('ovh.'.$endpoint.'.application_key')) {
            abort(404, 'Endpoint '.$endpoint.' application key not found');
        }
        if(!config('ovh.'.$endpoint.'.application_secret')) {
            abort(404, 'Endpoint '.$endpoint.' application secret not found');
        }

        return true;
    }

    public function redirect(String $endpoint)
    {
        if($consumerKey = session('loginConsumerKey'))
        {
            return $this->tryLogin($endpoint, $consumerKey);
        }
        return redirect()->route('pcc');
    }

    public function token(String $endpoint, String $token)
    {
        return $this->tryLogin($endpoint, $token);
    }

    private function tryLogin(String $endpoint, String $consumerKey)
    {
        $this->checkEndpoint($endpoint);

        session()->invalidate();
        session()->regenerateToken();
        
        if (Auth::attempt(['consumerKey' => $consumerKey, 'endpoint' => $endpoint])) {
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
