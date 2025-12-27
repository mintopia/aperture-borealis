<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @setting('name')
    </title>
    @if(App\Models\Setting::fetch('favicon'))
        <link rel="shortcut icon" href="@setting('favicon')"/>
    @endif
    @stack('head')
    @include('partials._theme')
</head>
<body @if($darkMode) data-bs-theme="dark" @endif>
<div class="page">
    <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
                    aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h1 class="navbar-brand navbar-brand-autodark mt-1 px-2">
                <a href="{{ route('home') }}">
                    @if(App\Models\Setting::fetch('logo-light'))
                        <img src="@setting('logo-light')" alt="@setting('name')" style="max-height: 40px;" class="d-inline d-lg-none">
                        <img src="@setting('logo-light')" alt="@setting('name')" class="d-none d-lg-inline">
                    @else
                        @setting('name')
                    @endif
                </a>
            </h1>


            <div class="collapse navbar-collapse" id="sidebar-menu">
                <ul class="navbar-nav pt-lg-3">
                    <li class="nav-item @if(($activenav ?? null) === 'home') active @endif">
                        <a class="nav-link" href="{{ route('home') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="icon ti ti-home"></i>
                            </span>
                            <span class="nav-link-title">
                                Home
                            </span>
                        </a>
                    </li>
                    <li class="nav-item @if(($activenav ?? null) === 'clients') active @endif">
                        <a class="nav-link" href="{{ route('clients.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="icon ti ti-apps"></i>
                            </span>
                            <span class="nav-link-title">
                                Clients
                            </span>
                        </a>
                    </li>
                    <li class="nav-item @if(($activenav ?? null) === 'providers') active @endif">
                        <a class="nav-link" href="{{ route('socialproviders.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="icon ti ti-circles-relation"></i>
                            </span>
                            <span class="nav-link-title">
                                Social Providers
                            </span>
                        </a>
                    </li>
                    <li class="nav-item @if(($activenav ?? null) === 'users') active @endif">
                        <a class="nav-link" href="{{ route('users.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="icon ti ti-users-group"></i>
                            </span>
                            <span class="nav-link-title">
                                Users
                            </span>
                        </a>
                    </li>
                    <li class="nav-item @if(($activenav ?? null) === 'themes') active @endif">
                        <a class="nav-link" href="{{ route('themes.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="icon ti ti-paint"></i>
                            </span>
                            <span class="nav-link-title">
                                Themes
                            </span>
                        </a>
                    </li>
                    <li class="nav-item @if(($activenav ?? null) === 'settings') active @endif">
                        <a class="nav-link" href="{{ route('settings.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-adjustments-cog"></i>
                            </span>
                            <span class="nav-link-title">
                                Settings
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('logout') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="icon ti ti-logout"></i>
                        </span>
                            <span class="nav-link-title">
                            Logout
                        </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </aside>

    <div class="page-wrapper">
        <header class="navbar d-print-none">
            <div class="container-xl">
                <div class="navbar-nav flex-row">
                    <div class="d-flex py-2 px-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            @yield('breadcrumbs')
                        </ol>
                    </div>
                </div>
            </div>
        </header>
        @yield('header')
        <div class="page-body">
            <div class="container-xl">
                @if (session('successMessage'))
                    <div class="alert alert-success alert-important alert-dismissible" role="alert">
                        {{ session('successMessage') }}
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif
                @if (session('errorMessage'))
                    <div class="alert alert-danger alert-important alert-dismissible" role="alert">
                        {{ session('errorMessage') }}
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif
                @if (session('infoMessage'))
                    <div class="alert alert-info alert-important alert-dismissible" role="alert">
                        {{ session('infoMessage') }}
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif
                @if (session('warningMessage'))
                    <div class="alert alert-warning alert-important alert-dismissible" role="alert">
                        {{ session('warningMessage') }}
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
        <footer class="footer footer-transparent d-print-none">
            <div class="container-xl">
                <div class="row text-center align-items-center flex-row-reverse">
                    <div class="col-lg-auto ms-lg-auto">
                        <ul class="list-inline list-inline-dots mb-0">
                            @if(\App\Models\Setting::fetch('terms'))
                                <li class="list-inline-item">
                                    <a href="@setting('terms')" target="_blank" class="link-secondary">Terms and Conditions</a>
                                </li>
                            @endif
                            @if(\App\Models\Setting::fetch('privacypolicy'))
                                <li class="list-inline-item">
                                    <a href="@setting('privacypolicy')" target="_blank" class="link-secondary">Privacy Policy</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                        <ul class="list-inline list-inline-dots mb-0">
                            <li class="list-inline-item">
                                Copyright &copy; {{ date('Y') }}
                                <a href="{{ route('home') }}" class="link-secondary">@setting('name')</a>.
                                All rights reserved.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>
@stack('footer')
</body>
</html>
