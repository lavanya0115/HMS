<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sree Anandham</title>

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
            background-color: #F57C00
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
            color: #F57C00
            border-bottom: 2px solid #F57C00
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
            color: #F57C00 //#ff8c00 old
            font-weight: bold;
        }
    </style> --}}
    <style>
        body {
            background: rgb(255, 255, 255);
            background-image: linear-gradient(rgba(255, 255, 255, 255), rgba(255, 255, 255, 0.9)), url('{{ asset('theme/logo/Food-03.png') }}');
            background-size: cover;
            background-attachment: fixed;
            background-repeat: no-repeat;
            transition: background 1.5s ease-in-out;
            font-family: 'Poppins', sans-serif;
        }


        .menu-header {
            text-align: center;
            /* color: white; */
            background-color: #006400;
            padding: 30px 20px 0;
            font-family: 'Poppins', sans-serif;
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
            background: linear-gradient(90deg,#ffffff, #006400, #ffffff);
            background-size: 100% 100%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradientMove 5s infinite;
            /* animation: typing 5s steps(30, end),  1.5s step-end infinite; */
            font-size: 1.5rem;
            font-weight: bold;

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
            margin: 10px 0;
        }

        .menu-title {
            width: 100%;
            background: #F57C00;
            text-align: left;
            font-family: 'Poppins', sans-serif;
            font-size: medium;
            font-weight: bold;
            color: #ffffff;
            /* border-bottom: 1px solid  #D32F2F; */
            display: inline-block;
        }

        .menu-list {
            list-style: none;
            padding: 0;
        }

        .menu-item {
            font-family: 'Poppins', sans-serif;
            color:  #006400;
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 0.8rem;
            /* border-bottom: 1px solid #ddd; */
        }

        .menu-item:last-child {
            border-bottom: none;
        }

        .menu-item-name {
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-weight: bold;
        }

        .menu-item-price {
            /* color: #F57C00 */
            /* font-weight: 500; */
            font-family: 'Poppins', sans-serif;
            color:  #F57C00;
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
        .time-range {
            font-size: 12px; !important
            font-weight: 200;
            font-family: 'Poppins', sans-serif; !important
        }
    </style>
    @livewireStyles
    @stack('styles')
</head>

<body>
    @php
        use App\Models\Category;
        use App\Models\Video;
        $day = now()->format('l');
        $slogan = Category::where('is_active', 1)
            ->where('type', 'slogan')
            ->where('day', lcfirst($day))
            ->value('title');
           
        $videos = Video::select('title', 'path')->get();
        // ->map(function ($video) {
        //     $video->path = asset($video->path);
        //     // dd( $video->path);
        //     return $video;
        // });
        $videosPath = [];
        foreach ($videos as $key => $video) {
            $videosPath[$key] = asset($video->path);
        }
        // dd($videosPath);
    @endphp
    {{-- <div class="container-xxl"> --}}

    <div class="page ps-3" style="height: 100vh; overflow: hidden;">
        <div class="page-wrapper" style="height: 100%; display: flex; flex-direction: column;">
            <div class="row g-0" style="height: 100%;">
                <div class="col-md-9" style="height: 100%;">
                    <div class="row align-items-center mt-3">
                        <!-- Logo Section -->
                        <div class="col-md-2 text-center">
                            <img src="{{ asset('images/logo2.png') }}" alt="HMS" class="img-fluid"
                                style="width: 200px; height: 70%;"
                                >
                        </div>
                        <!-- Title Section -->
                        <div class="col-md-10 text-center">
                            {{-- <div class="text-center">
                                <img src="{{ asset('designs/bottom_border.png') }}" alt="Decorative Border"
                                    class="border-image" style="max-width: 20%; height: auto;">
                            </div> --}}
                            <h3 class="animated-text">{{ $slogan }}</h3>
                            <div class="text-center">
                                <img src="{{ asset('designs/solgan border design.png') }}" alt="Decorative Border"
                                    class="border-image " style="max-width: 30%; height: auto;">
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
                <div class="col-md-3" style=" position: relative; height: 100%; display: flex;">
                    <video id="videoPlayer" class="video-section" autoplay muted playsinline
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                        <source id="videoSource" src="" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>

                </div>

            </div>
        </div>
       <div class="rounded-pill" 
            style=" 
                    background: transparent;
                    /* background: #7B3F00;  */
                    border:none;
                    /* padding: 3px; */
                    color: #006400; 
                    text-align: center; 
                    font-weight: bold; 
                    animation: marquee 20s linear infinite; 
                    font-size: 1rem; 
                    font-family: 'Poppins', sans-serif !important;">
                
            FRESH, FAST, & FLAVORFUL 
            <img src="{{ asset('theme/images/hurryup2.png') }}" alt="order"
                style="max-width: 4%; vertical-align: middle;"> 
            FOR BULK ORDER!
            
            <span class="text fw-bold ps-3 fs-5 pe-2" 
                style="color: #F57C00; font-family: 'Poppins', sans-serif !important;">
                CONTACT: +91 99012 88017
            </span>
            
            OPEN EVERY DAY 7AM - 9PM
        </div>
    </div>
    @stack('modals')
    @livewireScripts
    @stack('scripts')
    <script>
        document.addEventListener('livewire:initialized', function() {
            const videos = @json($videosPath);
            let currentVideoIndex = 0;
            const videoPlayer = document.getElementById('videoPlayer');
            const videoSource = document.getElementById('videoSource');

            function playVideo(index) {
                currentVideoIndex = index % videos.length;
                console.log(currentVideoIndex);
                const video = videos[currentVideoIndex];
                if (video) {
                    videoSource.src = video;
                    videoPlayer.load();
                    videoPlayer.play();

                    videoPlayer.onended = () => {
                        playVideo(currentVideoIndex + 1);
                    };
                }

            }

            // if (videos.length > 0) {
            playVideo(currentVideoIndex);
            // }
        });
    </script>
</body>


</html>
