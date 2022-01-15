@extends('layouts.app-with-navbar')

@section('content')
<div class="container">
    <pccs-page
        :pcc-names='@json($pccNames)'
        ovhapi-route="{{ route('ovhapi') }}"
        pcc-route="{{ route('pcc') }}">
    </pccs-page>
    @if(strtoupper(config('app.env')) != 'PRODUCTION')
        <div class="alert alert-danger">
            <strong>DEV logged-in user session info</strong><br />
            Endpoint: {{ Auth::user()->endpoint }}<br />
            Consumer key: {{ Auth::user()->consumerKey }}<br />
        </div>
    @endif
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
</div>
@endsection
