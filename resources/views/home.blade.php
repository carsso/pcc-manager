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
                    <div class="d-flex justify-content-center">
                        @foreach(config('ovh') as $endpoint => $config)
                            @if(config('ovh.'.$endpoint.'.application_secret') || config('ovh.'.$endpoint.'.client_secret'))
                                <div class="m-2">
                                    <a class="btn btn-outline-primary py-2 d-block" href="{{ route('login', ['endpoint' => $endpoint]) }}">
                                        <span class="fi fi-{{ config('ovh.'.$endpoint.'.flag') }} display-6 mb-2"></span><br />
                                        {{ config('ovh.'.$endpoint.'.name') }}<br />
                                        <small>{{ config('ovh.'.$endpoint.'.domain') }}</small>
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div>
                        <a href="#" class="small text-muted" data-bs-toggle="modal" data-bs-target="#loginModalRO">
                            <small>Login with read-only access</small>
                        </a>
                    </div>
                    <div class="modal fade" id="loginModalRO" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Login with your OVHcloud account (read-only access)</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Here you can login with your OVHcloud account, but we will request only <strong>read-only access</strong> to the OVHcloud API.
                                    <div class="alert alert-warning p-1 py-2 my-1">
                                        <i class="fas fa-exclamation-triangle"></i> Some features will be disabled or might not work as expected
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        @foreach(config('ovh') as $endpoint => $config)
                                            @if(config('ovh.'.$endpoint.'.application_secret') || config('ovh.'.$endpoint.'.client_secret'))
                                                <div class="m-2">
                                                    <a class="btn btn-outline-danger py-2 d-block" href="{{ route('login.read-only', ['endpoint' => $endpoint]) }}">
                                                        <span class="fi fi-{{ config('ovh.'.$endpoint.'.flag') }} display-6 mb-2"></span><br />
                                                        {{ config('ovh.'.$endpoint.'.name') }}<br />
                                                        <small>{{ config('ovh.'.$endpoint.'.domain') }}</small>
                                                    </a>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
