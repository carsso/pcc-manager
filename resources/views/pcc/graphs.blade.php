@extends('layouts.app-with-navbar')

@section('content')
<div class="container-fluid">
    <pcc-graphs-page
        pcc-name='{{ $pccName }}'
        datacenter-id='{{ $datacenterId }}'
        entity-type='{{ $entityType }}'
        entity-id='{{ $entityId }}'
        :entity='@json($entity)'
        ovhapi-route="{{ route('ovhapi') }}"
        pcc-route="{{ route('pcc') }}">
    </pcc-graphs-page>
</div>
@endsection
