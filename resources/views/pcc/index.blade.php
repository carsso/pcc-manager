@extends('layouts.app-with-navbar')

@section('content')
<div class="container">
    <pccs-page
        :pcc-names='@json($pccNames)'
        ovhapi-route="{{ route('ovhapi') }}"
        pcc-route="{{ route('pcc') }}">
    </pccs-page>
    @if(empty($pccNames))
        <div class="card my-3">
            <div class="card-body py-5">
                <p class="card-text text-center h2 mb-4">
                    No compatible product found on your OVHcloud account.
                </p>
                <p class="card-text text-center">
                    <a class="btn btn-outline-danger btn-lg" href="{{ route('logout') }}">
                        Logout
                    </a>
                </p>
            </div>
        </div>
        <div class="mt-5">
            @include('includes.compatible-products')
        </div>
    @endif
    @if(strtoupper(config('app.env')) != 'PRODUCTION')
        <div class="alert alert-danger">
            <div>
                <strong>DEV debug logged-in user session info - DO NOT SHARE WITH ANYBODY YOU DON'T TRUST</strong>
            </div>
            <div>
                Endpoint: {{ Auth::user()->endpoint }}
            </div>
            <div class="text-truncate">
                Consumer key: {{ Auth::user()->ovhApi->getToken() }}
            </div>
            @if(Auth::user()->ovhApi->getToken() != Auth::user()->ovhApi->getToken(true))
                <div>
                    <pre><code>@json(Auth::user()->ovhApi->getToken(true), JSON_PRETTY_PRINT)</code></pre>
                </div>
            @endif
            <div class="text-truncate">
                Auto-login link:
                <a class="text-danger" href="{{ route('login.token', ['endpoint' => Auth::user()->endpoint, 'token' => Auth::user()->ovhApi->getToken()]) }}">
                    {{ route('login.token', ['endpoint' => Auth::user()->endpoint, 'token' => Auth::user()->ovhApi->getToken()]) }}
                </a>
            </div>
            <div class="mt-3">
                OVHcloud API credential:
            </div>
            <div>
                <pre><code>@json(Auth::user()->currentCredential, JSON_PRETTY_PRINT)</code></pre>
            </div>
            <div class="mt-3">
                User info:
            </div>
            <div>
                <pre><code>@json(Auth::user()->userinfo, JSON_PRETTY_PRINT)</code></pre>
            </div>
        </div>
    @endif
</div>
@endsection
