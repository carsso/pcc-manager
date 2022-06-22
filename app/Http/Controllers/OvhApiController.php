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
            try {
                return $this->tryLogin($endpoint, $code, $state);
            } catch (RequestException $e) {
                $response = $e->getResponse();
                $error = null;
                if ($response != null) {
                    $json = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
                    $error = $json['message'].' ('.$json['errorCode'].')';
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

        $headers = [];

        if($request->header('HTTP_X_OVH_BATCH')) {
            $headers['X-OVH-BATCH'] = $request->header('HTTP_X_OVH_BATCH');
        }
        if($request->has('batch')) {
            $headers['X-OVH-BATCH'] = $request->query('batch');
        }

        if($request->has('pagination_sort')) {
            $headers['X-PAGINATION-MODE'] = 'CachedObjectList-Pages';
            $headers['X-PAGINATION-NUMBER'] = 1;
            $headers['X-PAGINATION-SIZE'] = 40;
            $headers['X-PAGINATION-SORT'] = $request->query('pagination_sort');
            $headers['X-PAGINATION-SORT-ORDER'] = 'ASC';
        }

        $paginationKeys = ['pagination_mode', 'pagination_number', 'pagination_size', 'pagination_sort', 'pagination_sort_order'];
        foreach ($paginationKeys as $key) {
            $keySnake = str_replace('_', '-', $key);
            if($request->header('HTTP_X_'.strtoupper($key))) {
                $headers['X-'.strtoupper($keySnake)] = $request->header('HTTP_X_'.strtoupper($key));
            }
            if($request->has($key)) {
                $headers['X-'.strtoupper($keySnake)] = $request->query($key);
            }
        }

        if(!empty(http_build_query($request->except($paginationKeys+['batch'])))) {
            $uri .= '?'.http_build_query($request->except($paginationKeys));
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
