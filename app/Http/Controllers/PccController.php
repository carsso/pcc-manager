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
        $pccNames = $ovhApi->get('/dedicatedCloud');
        sort($pccNames);
        return view('pcc.index', compact('pccNames'));
    }

    public function pcc(Request $request, String $pccName)
    {
        $ovhApi = $request->user()->ovhApi;
        $ovhApi->get('/dedicatedCloud/'.$pccName);
        return view('pcc.pcc', compact('pccName'));
    }

    public function datacenter(Request $request, String $pccName, String $datacenterId)
    {
        $ovhApi = $request->user()->ovhApi;
        $ovhApi->get('/dedicatedCloud/'.$pccName.'/datacenter/'.$datacenterId);
        return view('pcc.datacenter', compact('pccName', 'datacenterId'));
    }

    public function graphs(Request $request, String $pccName, String $datacenterId, String $entityType, String $entityId)
    {
        $ovhApi = $request->user()->ovhApi;
        try {
            $entity = $ovhApi->get('/dedicatedCloud/'.$pccName.'/datacenter/'.$datacenterId.'/'.$entityType.'/'.$entityId);
        } catch (RequestException $e) {
            try {
                # trying in global if not found in datacenter
                $entity = $ovhApi->get('/dedicatedCloud/'.$pccName.'/'.$entityType.'/'.$entityId);
            } catch (RequestException $e2) {
                throw $e;
            }
        }
        return view('pcc.graphs', compact('pccName', 'datacenterId', 'entityType', 'entityId', 'entity'));
    }
}
