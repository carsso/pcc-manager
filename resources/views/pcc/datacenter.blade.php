@extends('layouts.app-with-navbar')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <pcc-datacenter-page
        pcc-name='{{ $pccName }}'
        datacenter-id='{{ $datacenterId }}'
        ovhapi-route="{{ route('ovhapi') }}"
        home-route="{{ route('home') }}"
        pcc-route="{{ route('pcc') }}">
    </pcc-datacenter-page>
</div>
@endsection
