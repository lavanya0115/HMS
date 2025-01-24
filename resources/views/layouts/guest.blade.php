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
{{-- Animate.css --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

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
            font-size: 1.5rem;
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

        .item-text {
            animation: bounceIn 3.5s ease forwards;
        }

        .item-status {
            animation: bounceIn 4.5s ease-out 3s forwards;
        }


        @keyframes bounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }

            50% {
                transform: scale(1.1);
                opacity: 1;
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes bounceOut {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.5;
            }

            100% {
                transform: scale(0.3);
                opacity: 0;
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
            font-size: 1.0rem;
            font-weight: bold;
            margin-bottom: 15px;
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

        @keyframes marquee {
            from {
                transform: translateX(100%);
            }

            to {
                transform: translateX(-100%);
            }
        }

        footer {
            background: linear-gradient(rgba(225, 248, 212, 0.9), rgba(255, 228, 141, 0.8));
            color: #033b08e0;
            text-align: center;
            padding: 10px;
        }

        footer span {
            font-size: 1.2rem;
        }

        footer .order-image {
            max-width: 5%;
            vertical-align: middle;
        }
    </style>
    @livewireStyles
    @stack('styles')
</head>

<body>
    @php
        use App\Models\Category;
        $day = now()->format('l');
        $slogan = Category::where('is_active', 1)
            ->where('type', 'slogan')
            ->where('day', lcfirst($day))
            ->pluck('title')
            ->first();
    @endphp
    {{-- <div class="container-xxl"> --}}

    <div class="page" style="height: 100vh; overflow: hidden;">
        <div class="page-wrapper" style="height: 100%; display: flex; flex-direction: column;">
            <div class="row g-0" style="height: 100%;">
                <div class="col-md-9" style="height: 100%;">
                    <div class="row align-items-center ">
                        <!-- Logo Section -->
                        <div class="col-md-2 text-center">
                            <img src="{{ asset('images/logo.png') }}" alt="HMS" class="img-fluid"
                                style="max-width: 100px; height: 100%;">
                        </div>
                        <!-- Title Section -->
                        <div class="col-md-10 text-center">
                            <div class="text-center">
                                <img src="{{ asset('designs/Header_design.png') }}" alt="Decorative Border"
                                    class="border-image" style="max-width: 20%; height: auto;">
                            </div>
                            <h3 class="animated-text">{{ $slogan }}</h3>
                            <div class="text-center">
                                <img src="{{ asset('designs/Header_design-02.png') }}" alt="Decorative Border"
                                    class="border-image" style="max-width: 20%; height: auto;">
                            </div>
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div>
                        @yield('content')
                        @if (isset($slot))
                            {{ $slot }}
                        @endif
                    </div>
                </div>


                <!-- Right Section (Video) -->
                <div class="col-md-3 border" style=" position: relative; height: 100%; display: flex;">
                    <video class="video-section" autoplay muted loop playsinline
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                        <source src="{{ asset('videos/VID1.mp4') }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>

            </div>
            <footer>
                <div style="overflow: hidden; white-space: nowrap;">
                    <div style="display: inline-block; animation: marquee 18s linear infinite; font-size: 1.2rem;">
                        Fresh, fast, and flavorful <img src="{{ asset('theme/images/hurryup2.png') }}" alt="order"
                            class="order-image"> place your order today!
                        <span class="text fw-bold " style="color:#ff8c00">Contact: +91 99012 88017</span>
                    </div>


                </div>
            </footer>
        </div>
    </div>
    @stack('modals')
    @livewireScripts
    @stack('scripts')
</body>


</html>
