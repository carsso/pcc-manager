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
    // Forwarded as-is to the OVHcloud API, mainly to fetch expanded object lists (X-Pagination-Mode: CachedObjectList-Pages)
    private const PAGINATION_HEADERS = [
        'X-Pagination-Mode',
        'X-Pagination-Number',
        'X-Pagination-Size',
        'X-Pagination-Sort',
        'X-Pagination-Sort-Order',
    ];

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
            try {
                return $this->tryLogin($endpoint, $code, $state);
            } catch (RequestException $e) {
                $response = $e->getResponse();
                $error = null;
                if ($response != null) {
                    $json = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
                    $errorCode = isset($json['errorCode']) ? ' ('.$json['errorCode'].')' : '';
                    $error = $json['message'].$errorCode;
                }
                return redirect()->route('home')->withFlashError('A login error has occured : '.($error ? $error : $e->getMessage()))->withInput();
            }
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

        return $this->redirectWithErrror('home', 'Invalid credentials');
    }

    private function tryLogin(String $endpoint, ?String $code = null, ?String $state = null)
    {
        OvhApi::checkEndpoint($endpoint);

        session()->invalidate();
        session()->regenerateToken();
        
        if (Auth::attempt(['code' => $code, 'state' => $state, 'endpoint' => $endpoint])) {
            return redirect()->route('pcc');
        }

        return $this->redirectWithErrror('home', 'Invalid credentials');
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

        $headers = [];
        foreach (self::PAGINATION_HEADERS as $header) {
            if($value = $request->header($header)) {
                $headers[$header] = $value;
            }
        }

        if(!empty($query = http_build_query($request->query()))) {
            $uri .= '?'.$query;
        }

        $content = null;
        $rawJson = $request->getContent();
        //$rawJson = file_get_contents("php://input");
        if($rawJson) {
            $json = preg_replace('/(\w+):/i', '"\1":', $rawJson);
            $content = json_decode($json, true);
        }

        $method = $request->getMethod();

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
