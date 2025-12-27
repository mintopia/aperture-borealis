@extends('layouts.app', [
    'activenav' => 'clients',
])

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
    <li class="breadcrumb-item active"><a href="{{ route('clients.create') }}">Create Client</a>
@endsection

@section('content')
    <div class="page-header mt-0">
        <h1>Create Client</h1>
    </div>

    <div class="col-md-6 offset-md-3">
        <form action="{{ route('clients.store') }}" method="post" class="card">
            {{ csrf_field() }}
            @include('clients._form')
            <div class="card-footer text-end">
                <div class="d-flex">
                    <a href="{{ route('clients.index') }}" class="btn btn-link">Cancel</a>
                    <button type="submit" class="btn btn-primary ms-auto">Save</button>
                </div>
            </div>
        </form>
    </div>
@endsection
