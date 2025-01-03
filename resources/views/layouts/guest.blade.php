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
    @livewireStyles
    @stack('styles')
</head>

<body>
    <div class="page" style="height: 100vh; overflow: hidden;">
        <div class="page-wrapper" style="height: 100%; display: flex; flex-direction: column;">
            <div class="row g-0" style="height: 100%;">
                <!-- Left Section -->
                <div class="col-md-8" style="height: 100%; overflow-y: auto;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 400" preserveAspectRatio="none"
                        style="display: block; width: 100%; height: auto;">
                        <defs>
                            <linearGradient id="orangeGradient" x1="0%" y1="0%" x2="100%"
                                y2="0%">
                                <stop offset="0%" stop-color="#f7a94d" stop-opacity="0.9" />
                                <stop offset="100%" stop-color="#f7a94d" stop-opacity="0.4" />
                            </linearGradient>
                        </defs>
                        <path d="M0,150 C360,200 720,100 1080,250 C1320,330 1440,300 1440,300 L1440,0 L0,0 Z"
                            fill="url(#orangeGradient)" />
                        <path d="M0,200 C300,250 1140,50 1440,150 L1440,0 L0,0 Z" fill="#f5c377" opacity="0.6" />
                    </svg>
                    <h3 class="animated-text text-center" style="margin-top:-10%;">Magical Monday Menu</h3>
                    <div>
                        @yield('content')
                        @if (isset($slot))
                            {{ $slot }}
                        @endif
                    </div>
                </div>

                <!-- Right Section (Video) -->
                <div class="col-md-4  g-0" style="height: 100%; display: flex;">
                    <div class="video-section" style="position: relative; width: 120%; max-width: 400px; ">
                        <video autoplay muted loop playsinline
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                            <source src="{{ asset('videos/VID1.mp4') }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>


</html>
