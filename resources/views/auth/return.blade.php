@extends('layouts.login')
@section('content')
    <div class="text-center">
        <div class="mb-4">
            <span class="avatar avatar-xl mb-3" style="background-image: url('{{ $deviceCode->getAvatarUrl() }}')"></span>
            @if($deviceCode->nickname !== null)
                <h3>{{ $deviceCode->nickname }}</h3>
            @endif
        </div>
        <p>
            Thank you for logging in, please return to your browser.
        </p>
    </div>
@endsection
