<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\OvhApi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\RequestException;

class OvhApiController extends Controller
{
    public function loginReadOnly(String $endpoint)
    {
        return $this->login($endpoint, 'read-only');
    }

    public function login(String $endpoint, String $rightsType = 'read-write')
    {
        $ovhApi = new OvhApi($endpoint);
        $loginUrl = $ovhApi->prepareLogin($rightsType == 'read-only' ? true : false);
        session(['state' => $ovhApi->getState()]);
        return redirect($loginUrl);
    }

    public function redirect(Request $request, String $endpoint)
    {
        $code = $request->get('code');
        if($state = $request->get('state')) {
            if(!$code) {
                $error = $request->get('error');
                return redirect()->route('home')->withFlashError($error ? 'A login error has occured: ' . $error : 'A login error has occured')->withInput();
            }
            return $this->tryLogin($endpoint, $code, $state);
        } elseif($state = session('state')) {
            return $this->tryLogin($endpoint, $code, $state);
        }
        return redirect()->route('pcc');
    }

    public function token(String $endpoint, String $token)
    {
        OvhApi::checkEndpoint($endpoint);

        session()->invalidate();
        session()->regenerateToken();
        
        if (Auth::attempt(['token' => $token, 'endpoint' => $endpoint])) {
            return redirect()->route('pcc');
        }

        return $this->backWithError('Invalid credentials');
    }

    private function tryLogin(String $endpoint, ?String $code = null, ?String $state = null)
    {
        OvhApi::checkEndpoint($endpoint);

        session()->invalidate();
        session()->regenerateToken();
        
        if (Auth::attempt(['code' => $code, 'state' => $state, 'endpoint' => $endpoint])) {
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

        $method = $request->getMethod();
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
