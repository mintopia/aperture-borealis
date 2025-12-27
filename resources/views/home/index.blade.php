@extends('layouts.app', [
    'activenav' => 'home',
])

@section('breadcrumbs')
    <li class="breadcrumb-item active"><a href="{{ route('home') }}">Home</a></li>
@endsection

@section('content')
    <div class="page-header mt-0">
        <h1>Dashboard</h1>
    </div>
@endsection
