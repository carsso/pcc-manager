@extends('layouts.app-without-navbar')

@section('navbar')
    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand py-0" href="{{ url('/') }}">
                @if(strtoupper(config('app.env')) != 'PRODUCTION')
                    <strong class="bg-danger text-white px-2">DEV</strong>
                @endif
                {{ config('app.name') }}
            </a>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
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
                        <li>
                            <a class="nav-link @route($routeName) text-secondary @else text-white @endroute" href="{{ route($routeName) }}">{{ $text }}</a>
                        </li>
                    @endforeach
                </ul>

                <div class="text-end">
                    @auth
                        <a class="btn btn-outline-danger" href="{{ route('logout') }}">
                            Logout
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
@endsection
