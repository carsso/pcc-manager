@extends('layouts.app-with-navbar')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <pcc-page
        pcc-name='{{ $pccName }}'
        ovhapi-route="{{ route('ovhapi') }}"
        pcc-route="{{ route('pcc') }}">
    </pcc-page>
</div>
@endsection
