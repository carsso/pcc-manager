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
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            if ($response != null) {
                $statusCode = $response->getStatusCode();
                if($statusCode == 404) {
                    abort(404);
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
            }
            throw $exception;
        }
        return view('pcc.datacenter', compact('pccName', 'datacenterId'));
    }

    public function graphs(Request $request, String $pccName, String $datacenterId, String $entityType, String $entityId)
    {
        $ovhApi = $request->user()->ovhApi;
        try {
            $entity = $ovhApi->get('/v1/dedicatedCloud/'.$pccName.'/datacenter/'.$datacenterId.'/'.$entityType.'/'.$entityId);
        } catch (RequestException $e) {
            try {
                # trying in global if not found in datacenter
                $entity = $ovhApi->get('/v1/dedicatedCloud/'.$pccName.'/'.$entityType.'/'.$entityId);
            } catch (RequestException $e2) {
                $response = $e->getResponse();
                if ($response != null) {
                    $statusCode = $response->getStatusCode();
                    if($statusCode == 404) {
                        abort(404);
                    }
                }
                throw $e;
            }
        }
        return view('pcc.graphs', compact('pccName', 'datacenterId', 'entityType', 'entityId', 'entity'));
    }
}
