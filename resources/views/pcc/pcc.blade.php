@extends('layouts.app-with-navbar')

@section('content')
<div class="container-fluid">
    <pcc-page
        pcc-name='{{ $pccName }}'
        ovhapi-route="{{ route('ovhapi') }}">
    </pcc-page>
</div>
@endsection
