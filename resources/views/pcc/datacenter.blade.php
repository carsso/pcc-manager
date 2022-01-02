@extends('layouts.app-with-navbar')

@section('content')
<div class="container-fluid">
    <pcc-datacenter-page
        pcc-name='{{ $pccName }}'
        datacenter-id='{{ $datacenterId }}'
        ovhapi-route="{{ route('ovhapi') }}">
    </pcc-datacenter-page>
</div>
@endsection
