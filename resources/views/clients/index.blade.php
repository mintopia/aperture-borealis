@extends('layouts.app', [
    'activenav' => 'clients',
])

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item active"><a href="{{ route('clients.index') }}">Clients</a></li>
@endsection

@section('content')
    <div class="row g-2 align-items-center mb-4">
        <div class="col page-header mt-2">
            <h1>Clients</h1>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <a href="{{ route('clients.create') }}" class="btn btn-primary d-inline-block">
                    <i class="icon ti ti-plus"></i>
                    Create Client
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover table-outline table-vcenter text-nowrap card-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Client ID</th>
                            <th>Enabled</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($clients as $client)
                            <tr>
                                <td class="text-muted">{{ $client->id }}</td>
                                <td>
                                    <a href="{{ route('clients.show', $client->id) }}">
                                        {{ $client->name }}
                                    </a>
                                </td>
                                <td>{{ $client->client_id }}</td>
                                <td>
                                    @if($client->enabled)
                                        <span class="text-success">Yes</span>
                                    @else
                                        <span class="text-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @include('partials._pagination', [
                    'page' => $clients
                ])
            </div>
        </div>
    </div>
@endsection
