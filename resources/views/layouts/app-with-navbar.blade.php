@extends('layouts.app')

@section('navbar')
    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand py-0" href="{{ url('/') }}">
                @if(strtoupper(config('app.env')) != 'PRODUCTION')
                    <strong class="bg-danger text-white px-2">DEV</strong>
                @endif
                {{ config('app.name') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-md-0">
                    @auth
                        @php
                            $menuLinks = [
                                'pcc' => 'Home',
                            ];
                        @endphp
                    @else
                        @php
                            $menuLinks = [
                                'home' => 'Home',
                            ];
                        @endphp
                    @endauth
                    @foreach($menuLinks as $routeName => $text)
                        <li class="nav-item">
                            <a class="nav-link @route($routeName) text-secondary @else text-white @endroute" href="{{ route($routeName) }}">{{ $text }}</a>
                        </li>
                    @endforeach
                    @auth 
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarPccsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                PCCs
                            </a>
                            <pccs-submenu
                                ovhapi-route="{{ route('ovhapi') }}"
                                pcc-route="{{ route('pcc') }}">
                            </pccs-submenu>
                        </li>
                    @endauth
                </ul>

                <div class="d-flex">
                    @auth
                        <a class="btn btn-outline-danger me-2 mb-2 mb-md-0" href="{{ route('logout') }}">
                            Logout
                        </a>
                    @endauth
                </div>
                <div class="d-flex">
                    <darkmode-toggler
                        :darkmode="{{ Cookie::get('darkmode') ? 'true' : 'false' }}"
                        route="{{ route('home') }}">
                    </darkmode-toggler>
                </div>
            </div>
        </div>
    </nav>
@endsection
