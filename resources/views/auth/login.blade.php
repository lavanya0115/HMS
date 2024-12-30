<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ 'HMS' }}</title>
    <!-- CSS files -->
    <link href="{{ asset('/theme/css/tabler.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/theme/css/tabler-vendors.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/theme/css/demo.min.css') }}" rel="stylesheet" />
    @livewireStyles
</head>

<body style="background: linear-gradient(135deg, #ece1db, #fdb376);">
    <div class="d-flex flex-column">
        <div class="page page-center">
            <div class="container container-normal mt-5">
                <div class="row align-items-center">
                    <div class="col-lg">
                        <div class="container-tight ">
                            <div class="card card-md" >
                                {{-- Image Logo --}}
                                <div class="text-center mt-3">
                                    {{-- <img src="{{ asset('images/login.jpg') }}" class="avata11r"
                                        alt="HMS" height="100" width="100"> --}}
                                </div>
                                <h1 class="text-center p-3">Hotel Management System</h1>
                                <h3 class="text-center">Login to your account</h3>
                                <div class="card-body" >
                                    @include('includes.alerts')
                                    <form method="POST" action="{{ route('login') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Mobile</label>
                                            <input type="text" class="form-control" name="email"
                                                placeholder="Enter mobile no" value="{{ $mobileNo ?? '' }}">
                                        </div>

                                        <div class="mb-2 " id="field__password">
                                            <label class="form-label">
                                                Password
                                            </label>
                                            <div class="input-group input-group-flat" id="field__password">

                                                <input type="password" name="password" class="form-control"
                                                    id="password" placeholder="Your password" autocomplete="off">

                                                <span class="input-group-text">
                                                    <a href="#"
                                                        class="link-secondary password--visibility-icons password--show"
                                                        data-target="password--hide" data-action="show"
                                                        title="Show password" data-bs-toggle="tooltip">
                                                        @include('icons.eye')
                                                    </a>
                                                    <a class="link-secondary d-none password--visibility-icons password--hide"
                                                        data-target="password--show" data-action="hide"
                                                        title="Hide password" data-bs-toggle="tooltip">
                                                        @include('icons.eye-off')
                                                    </a>
                                                </span>
                                            </div>
                                        </div>

                                        <div class=" mt-3 float-end">
                                            <a href="#"
                                                class="me-3 text-decoration-none text text-secondary fw-bold">New
                                                Member?
                                            </a>
                                            <button type="submit" class="btn btn-warning">Login</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg d-none d-lg-block">
                        <img src="{{ asset('theme/images/chef.png') }}" height="300" class="d-block mx-auto"
                            alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="{{ asset('theme/js/tabler.min.js') }}" defer></script>
    <script src="{{ asset('theme/js/demo-theme.min.js') }}"></script>
    @livewireScripts

</body>

</html>
