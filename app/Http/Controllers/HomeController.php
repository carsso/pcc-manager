<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function legal()
    {
        return view('legal');
    }

    public function sentry(Request $request)
    {
        if (!config('sentry.dsn')) {
            return response('Sentry is not configured', 500);
        }

        $envelope = $request->getContent();
        $pieces = explode("\n", $envelope, 2);
        $header = json_decode($pieces[0], true);

        if (empty($header['dsn']) || $header['dsn'] !== config('sentry.dsn')) {
            return response('Invalid DSN', 500);
        }

        $dsn = parse_url(config('sentry.dsn'));
        $project_id = intval(trim($dsn['path'], '/'));

        return Http::withBody($envelope, 'application/x-sentry-envelope')
            ->withHeaders([
                'X-Forwarded-For' => $request->ip(),
            ])
            ->post('https://'.$dsn['host'].'/api/'.$project_id.'/envelope/');
    }
}
