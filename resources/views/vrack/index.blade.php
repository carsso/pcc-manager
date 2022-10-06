@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <vracks-page
        :vrack-names='@json($vrackNames)'
        ovhapi-route="{{ route('ovhapi') }}"
        home-route="{{ route('home') }}"
        pcc-route="{{ route('pcc') }}">
    </vracks-page>
    @if(empty($vrackNames))
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 text-center">
            <p class="text-2xl mb-4">
                No vRack found on your OVHcloud account.
            </p>
        </div>
    @endif
</div>
@endsection
