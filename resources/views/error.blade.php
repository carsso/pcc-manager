@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center relative mt-6">
        <div class="p-4">
            <div class="my-4">
                {{ $code ?? null }} {{ $status ?? 'Error' }} : {{ $message ?? null }}
            </div>
        </div>
    </div>
</div>
@endsection
