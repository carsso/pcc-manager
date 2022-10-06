@extends('layouts.app-with-navbar')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <pcc-graphs-page
        pcc-name='{{ $pccName }}'
        datacenter-id='{{ $datacenterId }}'
        entity-type='{{ $entityType }}'
        entity-id='{{ $entityId }}'
        :entity='@json($entity)'
        ovhapi-route="{{ route('ovhapi') }}"
        home-route="{{ route('home') }}"
        pcc-route="{{ route('pcc') }}">
    </pcc-graphs-page>
</div>
@endsection
