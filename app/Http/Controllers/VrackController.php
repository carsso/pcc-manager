<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VrackController extends Controller
{
    public function index(Request $request)
    {
        $ovhApi = $request->user()->ovhApi;
        $vrackNames = $ovhApi->get('/vrack');
        sort($vrackNames);
        return view('vrack.index', compact('vrackNames'));
    }
}
