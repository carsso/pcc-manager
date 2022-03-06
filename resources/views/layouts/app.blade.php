<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-color-scheme="{{ Cookie::get('darkmode') ? 'dark' : 'light' }}" class="{{ Cookie::get('darkmode') ? 'dark' : 'light' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @yield('meta')

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
    <body class="bg-gray-100 dark:bg-gray-900 dark:text-gray-300">
        <div id="app">
            @yield('navbar')
            <main class="py-4">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">@flash</div>
                @yield('content')
                <div class="text-center text-gray-500 mt-6">
                    <small>
                        {{ config('app.name') }}
                        - 
                        <a href="{{ route('legal') }}" class="underline hover:text-indigo-600">Legal notices</a>
                        - 
                        Not affiliated with OVHcloud
                        - 
                        <a href="https://github.com/carsso/pcc-manager" target="_blank" class="underline hover:text-indigo-600"><i class="fab fa-github"></i> Source code available on GitHub</a>
                    </small>
                </div>
            </main>
        </div>
        <!-- Scripts Footer -->
        <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
