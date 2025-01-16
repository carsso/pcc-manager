@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="mt-4 mb-2 text-2xl">
        @if(strtoupper(config('app.env')) != 'PRODUCTION')
            <strong class="bg-red-500 text-white px-2 mr-2">DEV</strong>
        @endif
        {{ config('app.name') }}
    </h1>
    <div class="flex">
        <div>
            {{ config('app.name') }} is an OVHcloud PCC (Private Cloud / Hosted Private Cloud) realtime visualization web interface.
            <br />
            <small class="text-gray-500">
                Inspired by the great
                <a href="https://help.ovhcloud.com/csm/en-vmware-using-vscope?id=kb_article_view&sysparm_article=KB0045667" target="_blank" class="small hover:text-indigo-600">
                    OVHcloud vScope
                    <i class="fas fa-external-link-alt"></i>
                </a>
                interface.
                Not affiliated with OVHcloud.
            </small>
        </div>
    </div>

    <div class="md:flex text-center gap-6 py-4">
        <div class="md:w-1/2 lg:w-1/3 mb-6">
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center dark:border dark:border-gray-700">
                <img src="https://user-images.githubusercontent.com/666182/156908567-49930926-796d-47bf-b026-5e1347432626.png" class="rounded-t-lg dark:hidden" alt="">
                <img src="https://user-images.githubusercontent.com/666182/156908568-f45b3c47-c28f-4aba-84e2-8c83e110097e.png" class="rounded-t-lg hidden dark:block" alt="">
                <div class="p-4">
                    <p class="text-sm">Visualize you virtual machines, hosts and datastores health & resource usage.</p>
                </div>
            </div>
        </div>
        <div class="md:w-1/2 lg:w-2/3 mb-6">
            @include('includes.compatible-products')
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 mt-6">
                <h3 class="mb-3">Login with your OVHcloud account</h3>
                <div class="flex justify-center">
                    @foreach(config('ovh') as $endpoint => $config)
                        @if(config('ovh.'.$endpoint.'.application_secret') || config('ovh.'.$endpoint.'.client_secret'))
                            <div class="m-2">
                                <a href="{{ route('login', ['endpoint' => $endpoint]) }}" class="block items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 dark:text-white bg-indigo-100 dark:bg-indigo-600 hover:bg-indigo-200 dark:hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span class="fi fi-{{ config('ovh.'.$endpoint.'.flag') }} mb-2 text-4xl"></span><br />
                                    {{ config('ovh.'.$endpoint.'.name') }}<br />
                                    <small>{{ config('ovh.'.$endpoint.'.domain') }}</small>
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
