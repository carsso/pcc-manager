<?php

namespace App\Http\Controllers;

use Dotenv\Util\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\RequestException;
use PhpParser\Node\Expr\Cast\String_;

class PccController extends Controller
{
    public function index(Request $request)
    {
        $ovhApi = $request->user()->ovhApi;
        $pccNames = $ovhApi->get('/v1/dedicatedCloud');
        sort($pccNames);
        return view('pcc.index', compact('pccNames'));
    }

    public function pcc(Request $request, String $pccName)
    {
        $ovhApi = $request->user()->ovhApi;
        try {
            $ovhApi->get('/v1/dedicatedCloud/'.$pccName);
            $ovhApi->get('/v1/dedicatedCloud/'.$pccName.'/datacenter');
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            if ($response != null) {
                $statusCode = $response->getStatusCode();
                if($statusCode == 404) {
                    abort(404);
                }
                if($statusCode == 460) {
                    abort(403, json_decode($response->getBody(), true)['message']);
                }
            }
            throw $exception;
        }
        return view('pcc.pcc', compact('pccName'));
    }

    public function datacenter(Request $request, String $pccName, String $datacenterId)
    {
        $ovhApi = $request->user()->ovhApi;
        try {
            $ovhApi->get('/v1/dedicatedCloud/'.$pccName.'/datacenter/'.$datacenterId);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            if ($response != null) {
                $statusCode = $response->getStatusCode();
                if($statusCode == 404) {
                    abort(404);
                }
                if($statusCode == 460) {
                    abort(403, json_decode($response->getBody(), true)['message']);
                }
            }
            throw $exception;
        }
        return view('pcc.datacenter', compact('pccName', 'datacenterId'));
    }
}
