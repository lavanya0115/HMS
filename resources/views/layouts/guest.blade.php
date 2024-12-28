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
            background-image: linear-gradient(rgba(255, 243, 205, 0.8), rgba(230, 255, 216, 0.9)), url('{{ asset('theme/logo/Food-03.png') }}');
            background-size: cover;
            background-attachment: fixed;
            background-repeat: no-repeat;
            transition: background 1.5s ease-in-out;    
            font-family: 'Georgia', serif;
            color: #7a0505;
            /* opacity: 0.9; */
        }


        .menu-header {
            text-align: center;
            color: white;
            background-color: #f7a94d;
            padding: 40px 20px 0;
            font-family: 'Georgia', serif;
            font-size: 2rem;
        }

        /* .animated-text {
            animation: fadeSlideUp 2s ease-in-out;
            font-size: 3rem;
        }

        @keyframes fadeSlideUp {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        } */

        /* .animated-text {
            font-size: 3rem;
            overflow: hidden;
            border-right: 2px solid white;
            white-space: nowrap;
            width: 0;
            animation: typing 3s steps(30, end), blink 0.5s step-end infinite;
        }

        @keyframes typing {
            from {
                width: 0;
            }

            to {
                width: 100%;
            }
        }

        @keyframes blink {
            from {
                border-right-color: white;
            }

            to {
                border-right-color: transparent;
            }
        } */

        .animated-text {
            background: linear-gradient(90deg, #144201ea, #9bcc6d, #1a4b04e5);
            background-size: 300% 100%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradientMove 5s infinite;
            /* animation: typing 5s steps(30, end),  1.5s step-end infinite; */
            font-size: 3rem;
        }

        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
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

        #borderimg {
            border: 10px solid transparent;
            padding: 15px;
            border-image: url("public\theme\logo\corner-design1.png") 30 stretch;
        }
    </style>

    {{-- <style>
        body {
            font-family: 'Georgia', serif;
            margin: 0;
            padding: 0;
            background-color: #faf3e4;
            color: #444;
        }

        .menu-card {
            max-width: 1200px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
        }

        .menu-header {
            background-color: #f7a94d;
            color: white;
            text-align: center;
            padding: 20px;
            width: 100%;
        }

        .menu-header h1 {
            margin: 0;
            font-size: 2.5rem;
        }

        .menu-section {
            flex: 1;
            padding: 20px;
        }

        .menu-section h2 {
            text-align: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #f7a94d;
        }

        .menu-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .menu-section ul li {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .menu-footer {
            background-color: #f7a94d;
            color: white;
            text-align: center;
            padding: 10px;
            width: 100%;
            font-size: 1rem;
        }


        @media (max-width: 768px) {
            .menu-section {
                flex: 1 1 100%;
            }
        }
    </style> --}}
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
