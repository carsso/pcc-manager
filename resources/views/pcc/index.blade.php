@extends('layouts.app-with-navbar')

@section('content')
<div class="container">
    <pccs-page
        :pcc-names='@json($pccNames)'
        ovhapi-route="{{ route('ovhapi') }}">
    </pccs-page>
</div>
@endsection
