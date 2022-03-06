@extends('layouts.app-with-navbar')

@section('meta')
<meta name="robots" content="noindex, follow">
@endsection

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="mt-4 mb-2">
        Legal notices
    </h1>
    <div class="d-flex">
        <div>
            <p>
                This website is not affiliated with OVHcloud.
            </p>
            <p>
                This website is provided "as is" without warranty of any kind, express or implied.
            </p>
            <p>
                Hosting provider : {{ config('legal.hosting_info') }}
            </p>
            <h2>
                Personal data
            </h2>
            <p>
                This website is taking data privacy seriously and will not share your personal data with third parties.
                Refusing data collection will prevent you from using the service.<br />
                You can ask for the access, correction or deletion of your personal data by asking through the following email address.<br />
                DPO : {{ config('legal.dpo') }}<br />
                If you think this website is violating your rights, you can submit a compaint to the CNIL on cnil.fr/plaintes.
            </p>
            <h3>Connection and navigation data :</h3>
            <p>
                This website is collecting connection and navigation data from users : connection date and hour, ip address, terminal, browser and operating system.<br />
                They are not intended to be stored in a permanent way on server side or used for any other purpose than providing access to this website and its services<br />
                Sometimes, they may be stored inside log files on the server when the website is accessed, for debugging or logging purpose (mainly along with a stacktrace when an error occurs), kept for a maximum time of {{ config('legal.logs_retention') }} months.
            </p>
            <h3>API tokens :</h3>
            <p>
                This website is collecting data identifying the user on the OVHcloud API, also known as API tokens.<br />
                These tokens are not intended to be stored in a permanent way on server side or used for any other purpose than to build this website interface.<br />
                They are stored inside {{ config('session.encrypt') ? 'an encrypted' : 'a'}} temporary session {{ config('session.driver') }} (feature provided by the Laravel framework), kept for a maximum time of {{ round(config("session.lifetime")/60/24+1) }} days.<br />
                Sometimes, they may also be stored inside log files on the server when the website is accessed, for debugging or logging purpose (mainly along with a stacktrace when an error occurs), kept for a maximum time of {{ config('legal.logs_retention') }} months.
            </p>
            <h3>Personal data retreived from the OVHcloud API</h3>
            <p>
                Some personal informations retreived from the OVHcloud API are collected and used to build this website interface.<br />
                They are not intended to be stored in a permanent way on server side or used for any other purpose than to build this website interface.<br />
                Although, sometimes, they may be stored inside log files on the server when the website is accessed, for debugging or logging purpose (mainly along with a stacktrace when an error occurs), kept for a maximum time of {{ config('legal.logs_retention') }} months.
            </p>
            <h2>
                Cookies
            </h2>
            <p>
                This website is using cookies only for technical purpose (to keep user connected while using the website and storing eventual preferences on this website).<br />
                No third party cookie is used (and no cookie related to ads, analytics nor social networks).
            </p>
            <h2>
                Source code
            </h2>
            <p>
                Source code of this app is available on <a href="https://github.com/carsso/pcc-manager" target="_blank"><i class="fab fa-github"></i> GitHub</a><br />
                The source code is licensed under the <a href="https://opensource.org/licenses/MIT" target="_blank">MIT license</a>.
            </p>
        </div>
    </div>
</div>
@endsection
