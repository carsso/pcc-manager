@extends('layouts.app')

@section('navbar')
    <navbar
        app-name="{{ config('app.name') }}"
        :is-dev="{{ strtoupper(config('app.env')) != 'PRODUCTION' ? 'true' : 'false' }}"
        home-route="{{ route('home') }}"
        pcc-route="{{ route('pcc') }}"
        logout-route="{{ route('logout') }}"
        :is-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
    </navbar>
@endsection
