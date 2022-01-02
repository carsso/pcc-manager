@php
    $name = !empty($name) ? $name . '_' : 'flash_';
@endphp

@if(session($name . 'success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session($name . 'success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session($name . 'error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session($name . 'error') }}

        @if(session($name . 'error_exception'))
            <br>
            {{ session($name . 'error_exception') }}
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
