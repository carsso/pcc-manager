<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $demoAccounts = config('ovh.demo_accounts');
        return view('home', compact('demoAccounts'));
    }
}
