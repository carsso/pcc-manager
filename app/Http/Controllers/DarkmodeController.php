<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class DarkmodeController extends Controller
{
    public function json(Request $request, String $enable)
    {
        $darkmode = $enable ? 1 : 0;
        Cookie::queue('darkmode', $darkmode, 60*24*365*10);
        return ['darkmode' => $darkmode, 'previous' => Cookie::get('darkmode') ? 1 : 0];
    }
}
