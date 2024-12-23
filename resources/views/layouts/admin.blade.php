<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>HMS</title>
    <!-- CSS files -->
    <link href="{{ asset('/theme/css/tabler.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/theme/css/tabler-vendors.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/theme/css/demo.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('styles/main.css') }}" rel="stylesheet" />
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
    <style>
        body {
            font-family: "Poppins", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            /* font-size: 13px; */
            font-feature-settings: "cv03", "cv04", "cv11";
        }

        .nav-item:hover {
            background-color: #f78c45;
        }

        .nav-item.active,
        .nav-item.active::after {
            background-color: 0.3s ease;
            border-left: 2px solid #ed7b34 !important;
        }

        .nav-item.active .nav-link-icon,
        .nav-item.active .nav-link-title {
            color: #ed7b34;

        }

        .nav-item:hover .nav-link-icon,
        .nav-item:hover .nav-link-title {
            color: #ed7b34;

        }

        .dropdown-item:hover {
            background-color:  #f0efed;
            color: #ed7b34;

        }

        .dropdown-item:hover .nav-link-title,
        .dropdown-item:hover .nav-link-icon {
            color: #ed7b34;
        }

        .dropdown-item.active .nav-link-title,
        .dropdown-item.active .nav-link-icon {
            color: #ed7b34;
        }

        .dropdown-item.active,
        .dropdown-item.active::after {
            background-color: 0.3s ease;
            border-left: 2px solid #ed7b34 !important;
        }

        .nav-link.active .nav-link-title,
        .nav-link.active .nav-link-icon {
            color: #ed7b34;
        }

        .nav-link.active,
        .dropdown-item.active::after {
            background-color:  0.3s ease;
            border-left: 2px solid #ed7b34 !important;
        }

        .nav-link:hover {
            background-color: #f0efed;
            color: #ed7b34;

        }

        .nav-link:hover .nav-link-title,
        .nav-link:hover .nav-link-icon {
            color: #ed7b34;
        }

        .announcements {
            margin-top: 4.2%;
        }
    </style>

    @livewireStyles
    @stack('styles')
</head>

<body>
  
    @include('layouts.partials.admin-header')
    @include('layouts.partials.admin-sidebar')

    <div class="page">

        <div class="page-wrapper">

            @yield('content')

            @if (isset($slot))
                {{ $slot }}
            @endif

            {{-- @include('layouts.partials.admin-footer') --}}
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="{{ asset('theme/js/tabler.min.js') }}" defer></script>
    <script src="{{ asset('theme/js/demo.js') }}" defer></script>
    <script src="{{ asset('theme/js/demo-theme.min.js') }}"></script>
    @stack('modals')
    @livewireScripts
    @stack('scripts')
    <script>
        jQuery(document).ready(function() {
            jQuery('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>

</html>
