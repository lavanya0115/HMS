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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- <style>
        body {
            background-color: #fdf2e9;
            font-family: 'Georgia', serif;
            color: #4a4a4a;
        }

        .menu-header {
            text-align: center;
            padding: 20px;
            background-color: #ff8c00;
            color: white;
            margin-bottom: 20px;
        }

        .menu-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: bold;
        }

        .menu-header p {
            margin: 0;
            font-size: 1.2rem;
        }

        .menu-section {
            margin: 20px 0;
        }

        .menu-title {
            text-align: center;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #ff8c00;
            border-bottom: 2px solid #ff8c00;
            display: inline-block;
        }

        .menu-list {
            list-style: none;
            padding: 0;
        }

        .menu-item {
            color: #0b4106e3;
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
            font-size: 1.1rem;
        }

        .menu-item:last-child {
            border-bottom: none;
        }

        .menu-item-name {
            font-weight: 500;
        }

        .menu-item-price {
            color: #ff8c00;
            font-weight: bold;
        }
    </style> --}}
    <style>
        body {
            background-image: url('public/theme/images/temple.png') ;
            background-size: cover;
            background-attachment: fixed;
            background-repeat: no-repeat;
            transition: background 0.5s ease-in-out;
            font-family: 'Georgia', serif;
            color: #4a4a4a;
        }

        .menu-header {
            text-align: center;
            padding: 20px;
            background-color: rgba(255, 140, 0, 0.8);
            color: white;
            margin-bottom: 20px;
        }

        .menu-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: bold;
        }

        .menu-header p {
            margin: 0;
            font-size: 1.2rem;
        }

        .menu-section {
            margin: 20px 0;
        }

        .menu-title {
            text-align: center;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #ff8c00;
            border-bottom: 2px solid #ff8c00;
            display: inline-block;
        }

        .menu-list {
            list-style: none;
            padding: 0;
        }

        .menu-item {
            color: #033b08e0;
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
            font-size: 1.1rem;
        }

        .menu-item:last-child {
            border-bottom: none;
        }

        .menu-item-name {
            font-weight: 500;
        }

        .menu-item-price {
            color: #ff8c00;
            font-weight: bold;
        }

        .logo {
            max-width: 150px;
            margin: 0 auto 20px;
            display: block;
        }
    </style>
    @livewireStyles
    @stack('styles')
</head>

<body>
    <div class="page">
        <div class="page-wrapper">

            @yield('content')

            @if (isset($slot))
                {{ $slot }}
            @endif

        </div>
        {{-- @include('layouts.partials.admin-footer') --}}
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
