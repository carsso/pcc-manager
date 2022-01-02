<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $demoAccounts = json_decode(env('OVH_DEMO_ACCOUNTS', '{}'), true);
        return view('home', compact('demoAccounts'));
    }
}
