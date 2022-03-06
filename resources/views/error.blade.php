@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="row text-center">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h3 class="my-4">{{ $code ?? null }} {{ $status ?? 'Error' }} : {{ $message ?? null }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
