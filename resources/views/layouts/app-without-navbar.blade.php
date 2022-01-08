@extends('layouts.app')

@section('main')
    <main class="py-4">
        <div class="container">@flash</div>

        @yield('content')
    </main>
    <div class="text-center text-muted">
        <small>
            {{ config('app.name') }}
            - 
            <a href="{{ route('legal') }}">Legal notices</a>
            - 
            Not affiliated with OVHcloud
            - 
            <a href="https://github.com/carsso/pcc-manager" target="_blank"><i class="fab fa-github"></i> Source code available on GitHub</a>
        </small>
    </div>
@endsection
