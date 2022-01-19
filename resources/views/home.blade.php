@extends('layouts.app-with-navbar')

@section('content')
<div class="container">
    <h1 class="mt-4 mb-2">
        @if(strtoupper(config('app.env')) != 'PRODUCTION')
            <strong class="bg-danger text-white px-2">DEV</strong>
        @endif
        {{ config('app.name') }}
    </h1>
    <div class="d-flex">
        <div>
            {{ config('app.name') }} is an OVHcloud PCC (Private Cloud / Hosted Private Cloud) realtime visualization web interface.
            <br />
            <small class="text-muted">
                Inspired by the great OVHcloud vScope
                <a href="https://www.ovhcloud.com/en/enterprise/products/hosted-private-cloud/vscope/" target="_blank" class="small">
                    <i class="fas fa-external-link-alt"></i>
                </a>
                interface.
                Not affiliated with OVHcloud.
            </small>
        </div>
    </div>

    <div class="row text-center">
        <div class="col-12 col-sm-6 col-md-5 col-lg-4">
            <div class="card my-3">
                <img src="https://user-images.githubusercontent.com/666182/148128946-e64ce228-5c82-45b0-ad0b-19d94fc5814d.png" class="card-img-top" alt="">
                <div class="card-body">
                    <p class="card-text">Visualize you virtual machines, hosts and datastores health & resource usage.</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-7 col-lg-8">
            <div>
                @include('includes.compatible-products')
            </div>
            <div class="card my-3">
                <div class="card-body">
                    <h3 class="mb-3">Login with your OVHcloud account</h3>
                    @foreach(config('ovh') as $endpoint => $config)
                        @if(config('ovh.'.$endpoint.'.application_secret'))
                            <a class="btn btn-outline-primary m-2 py-2" href="{{ route('login', ['endpoint' => $endpoint]) }}">
                                <span class="fi fi-{{ config('ovh.'.$endpoint.'.flag') }} display-6 mb-2"></span><br />
                                {{ config('ovh.'.$endpoint.'.name') }}<br />
                                <small>{{ config('ovh.'.$endpoint.'.domain') }}</small>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
            @if($hasDemoAccounts)
                <div class="card my-3">
                    <div class="card-body">
                        <h3 class="mb-3">Test with a demo account</h3>
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
            @endif
        </div>
    </div>
</div>
@endsection
