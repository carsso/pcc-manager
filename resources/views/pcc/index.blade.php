@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <pccs-page
        :pcc-names='@json($pccNames)'
        ovhapi-route="{{ route('ovhapi') }}"
        pcc-route="{{ route('pcc') }}">
    </pccs-page>
    @if(empty($pccNames))
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 text-center">
            <p class="text-2xl mb-4">
                No compatible product found on your OVHcloud account.
            </p>
            <p>
                <a href="{{ route('logout') }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Sign out
                </a>
            </p>
        </div>
        <div class="mt-5">
            @include('includes.compatible-products')
        </div>
    @endif
    @if(strtoupper(config('app.env')) != 'PRODUCTION')
        <div class="rounded-md bg-red-50 dark:bg-red-800 p-6 text-red-700 dark:text-red-100  mt-8">
            <div>
                <strong>DEV debug logged-in user session info - DO NOT SHARE WITH ANYBODY YOU DON'T TRUST</strong>
            </div>
            <div>
                Endpoint: {{ Auth::user()->endpoint }}
            </div>
            <div class="truncate">
                Consumer key: {{ Auth::user()->ovhApi->getToken() }}
            </div>
            @if(Auth::user()->ovhApi->getToken() != Auth::user()->ovhApi->getToken(true))
                <div class="whitespace-pre w-100 overflow-scroll text-red-900 dark:text-red-300">
                    <pre><code>@json(Auth::user()->ovhApi->getToken(true), JSON_PRETTY_PRINT)</code></pre>
                </div>
            @endif
            <div class="truncate">
                Auto-login link:
                <a class="text-red-800 hover:text-red-600 dark:text-red-400 dark:hover:text-red-600" href="{{ route('login.token', ['endpoint' => Auth::user()->endpoint, 'token' => Auth::user()->ovhApi->getToken()]) }}">
                    {{ route('login.token', ['endpoint' => Auth::user()->endpoint, 'token' => Auth::user()->ovhApi->getToken()]) }}
                </a>
            </div>
            <div class="mt-3">
                OVHcloud API credential:
            </div>
            <div class="whitespace-pre w-100 overflow-scroll text-red-900 dark:text-red-300">
                <pre><code>@json(Auth::user()->currentCredential, JSON_PRETTY_PRINT)</code></pre>
            </div>
            <div class="mt-3">
                User info:
            </div>
            <div class="whitespace-pre w-100 overflow-scroll text-red-900 dark:text-red-300">
                <pre><code>@json(Auth::user()->userinfo, JSON_PRETTY_PRINT)</code></pre>
            </div>
        </div>
    @endif
</div>
@endsection
