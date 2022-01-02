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
        return view('pcc.pcc', compact('pccName'));
    }

    public function datacenter(Request $request, String $pccName, String $datacenterId)
    {
        return view('pcc.datacenter', compact('pccName', 'datacenterId'));
    }
}
