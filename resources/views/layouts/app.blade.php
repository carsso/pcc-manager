<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <link href="{{ mix('css/app.css') }}" rel="stylesheet">

        @if(strtoupper(config('app.env')) != 'PRODUCTION')
            <style>
                body {
                    background-image: url('{{ URL::asset('img/bg_dev.png') }}');
                }
            </style>
        @endif
    </head>
    <body>
        <div id="app" class="h-100">
            @yield('navbar')

            @yield('main')
        </div>
        <!-- Scripts Footer -->
        <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
