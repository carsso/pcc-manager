@extends('layouts.app-with-navbar')

@section('content')
<div class="container">
    <h1 class="mt-4 mb-2">
        @if(strtoupper(config('app.env')) != 'PRODUCTION')
            <strong class="bg-danger text-white px-2">DEV</strong>
        @endif
        {{ config('app.name') }}
    </h1>
    <p>You are not logged-in. Please login first</p>

    <div class="row text-center">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-4">Login with your account</h3>
                    @foreach(config('ovh') as $endpoint => $config)
                        @if(config('ovh.'.$endpoint.'.application_secret'))
                            <p>
                                <a class="btn btn-primary btn-lg" href="{{ route('login', ['endpoint' => $endpoint]) }}">
                                    Login with your {{ config('ovh.'.$endpoint.'.name') }} account
                                </a>
                            </p>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        @if($hasDemoAccounts)
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h3 class="mb-3">Test with an demo account</h3>
                        @foreach(config('ovh') as $endpoint => $config)
                            @if(config('ovh.'.$endpoint.'.application_secret'))
                                @foreach(config('ovh.'.$endpoint.'.demo_accounts') as $demoAccount)
                                    <p>
                                        {{ $demoAccount['name'] }}<br />
                                        <small>{{ $demoAccount['description'] }}</small><br />
                                        <a class="btn btn-outline-warning btn-sm" href="{{ route('login.token', ['endpoint' => $endpoint, 'token' => $demoAccount['token']]) }}">
                                            Log-in with this account ({{ config('ovh.'.$endpoint.'.name') }})
                                        </a>
                                    </p>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
