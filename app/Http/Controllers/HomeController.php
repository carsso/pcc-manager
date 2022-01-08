<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        $hasDemoAccounts = false;
        foreach(config('ovh') as $endpoint => $config) {
            if(config('ovh.'.$endpoint.'.application_secret') && config('ovh.'.$endpoint.'.demo_accounts')) {
                $hasDemoAccounts = true;
            }
        }
        return view('home', compact('hasDemoAccounts'));
    }

    public function legal()
    {
        return view('legal');
    }
}
