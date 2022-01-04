<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $hasDemoAccounts = false;
        foreach(config('ovh') as $endpoint => $config) {
            if(config('ovh.'.$endpoint.'.application_secret') && config('ovh.'.$endpoint.'.demo_accounts')) {
                $hasDemoAccounts = true;
            }
        }
        return view('home', compact('hasDemoAccounts'));
    }
}
