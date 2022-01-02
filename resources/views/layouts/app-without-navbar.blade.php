@extends('layouts.app')

@section('main')
    <main class="py-4">
        <div class="container">@flash</div>

        @yield('content')
    </main>
    <div class="text-center text-muted">
        <small>
            Copyright © 2021-{{ date('Y') }} {{ config('app.name') }} - Not affiliated with OVHcloud
        </small>
    </div>
@endsection
