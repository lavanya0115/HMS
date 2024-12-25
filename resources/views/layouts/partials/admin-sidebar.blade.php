@php
    $currentRouteName = Route::currentRouteName();
    $masterMenuIsActive = in_array($currentRouteName, [
        'employees.index',
        'category',
        'products',
        'roles',
        'permissions',
        'seminars',
        'menu.items.create',
        'menu.items.list',
    ]);
@endphp

<aside class="navbar navbar-vertical navbar-expand-lg navbar-dark border-end border-5">
    <div class="container-fluid">

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
            aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('dashboard') }}">
                <img src="{{ asset('images/login.webp') }}" alt="HMS" width="100" height="100">
            </a>
        </h1>

        <div class=" d-lg-none d-flex justify-content-evenly">
            <div class="nav-item dropdown ">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0 text-secondary" data-bs-toggle="dropdown"
                    aria-label="Open user menu">
                    <span>
                        @include('icons.user-circle')
                    </span>
                    <div class="d-xl-block ps-2">
                        @isset(getAuthData()->name)
                            <div>{{ getAuthData()->name ?? '' }}</div>
                        @endisset
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow ">
                    <a href="{{ route('user.profile') }}" class="dropdown-item">Account Info</a>
                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <a href="javascript:void(0);" onclick="document.getElementById('logout-form').submit()"
                            class="text-danger text-decoration-none p-2">
                            <span class="text-danger">
                                @include('icons.logout')
                            </span>
                            Logout
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <div class="collapse navbar-collapse" id="sidebar-menu">
            <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch mt-2">
                <ul class="navbar-nav">

                    <li class="nav-item {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">@include('icons.dashboard')</span>
                            <span class="nav-link-title">
                                Dashboard
                            </span>
                        </a>
                    </li>

                    {{-- Masters --}}

                    <li class="dropdown {{ $masterMenuIsActive ? 'active' : '' }}">
                        <a class="nav-link dropdown-toggle " href="#navbar-help" data-bs-toggle="dropdown"
                            data-bs-auto-close="outside" role="button"
                            aria-expanded="{{ $masterMenuIsActive ? 'true' : 'false' }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                @include('icons.settings')
                            </span>
                            <span class="nav-link-title">
                                Masters
                            </span>
                        </a>

                        <div class="dropdown-menu {{ $masterMenuIsActive ? 'show' : '' }}">
                            <div class="dropdown-menu-columns">
                                <div class="dropdown-menu-column">

                                    <a class="dropdown-item {{ $currentRouteName == 'employees.index' ? 'active' : '' }}"
                                        href="{{ route('employees.index') }}">
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            @include('icons.user')
                                        </span>
                                        <span class="nav-link-title">
                                            Users
                                        </span>
                                    </a>


                                    <a class="dropdown-item {{ in_array($currentRouteName, ['menu.items.create', 'menu.items.list']) ? 'active' : '' }}"
                                        href="{{ route('menu.items.list') }}">
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            @include('icons.basket-filled')
                                        </span>
                                        <span class="nav-link-title">
                                            Menus
                                        </span>
                                    </a>


                                    <div class="dropend">
                                        <a class="dropdown-item dropdown-toggle" href="#"
                                            data-bs-toggle="dropdown" data-bs-auto-close="false" role="button"
                                            aria-expanded="true">
                                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                                @include('icons.category')
                                            </span>
                                            <span class="nav-link-title">
                                                Categories
                                            </span>
                                        </a>
                                    </div>

                                    {{-- <a class="dropdown-item {{ $currentRouteName == 'products' ? 'active' : '' }}"
                                            href='#'>
                                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                                @include('icons.basket-filled')
                                            </span>
                                            <span class="nav-link-title">
                                                Products
                                            </span>
                                        </a> --}}

                                    <a class="dropdown-item {{ $currentRouteName == 'roles' ? 'active' : '' }}"
                                        href='#'>
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            @include('icons.user-shield')
                                        </span>
                                        <span class="nav-link-title">
                                            Roles
                                        </span>
                                    </a>



                                    <a class="dropdown-item {{ $currentRouteName == 'permissions' ? 'active' : '' }}"
                                        href='#'>
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            @include('icons.user-check')
                                        </span>
                                        <span class="nav-link-title">
                                            Permissions
                                        </span>
                                    </a>

                                </div>
                            </div>
                        </div>
                    </li>

                </ul>
            </div>
        </div>
    </div>
</aside>
