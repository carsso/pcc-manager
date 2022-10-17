@extends('layouts.app')


@section('navbar')
    @php
        $publicRoutes = [
            [
                'name' => 'Home',
                'route' => route('home'),
                'active' => request()->routeIs('home.*') || request()->routeIs('home'),
            ],
        ];
        $authRoutes = [
            [
                'name' => 'PCCs',
                'route' => route('pcc'),
                'active' => request()->routeIs('pcc.*') || request()->routeIs('pcc'),
            ],
            [
                'name' => 'vRacks',
                'route' => route('vrack'),
                'active' => request()->routeIs('vrack.*') || request()->routeIs('vrack'),
            ],
        ];
    @endphp
    <navbar
        app-name="{{ config('app.name') }}"
        :is-dev="{{ strtoupper(config('app.env')) != 'PRODUCTION' ? 'true' : 'false' }}"
        vrack-route="{{ route('vrack') }}"
        home-route="{{ route('home') }}"
        logout-route="{{ route('logout') }}"
        :routes='@json(auth()->check() ? $authRoutes : $publicRoutes)'
        :is-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
    </navbar>
@endsection
