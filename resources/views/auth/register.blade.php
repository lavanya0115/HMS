{{-- <x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required />

                            <div class="ml-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Terms of Service').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Privacy Policy').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ml-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout> --}}

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
    <style>
        body {
            background-image: linear-gradient(rgba(235, 235, 235, 0.8), rgba(228, 228, 228, 0.9)), url('{{ asset('theme/images/login-bg.webp') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            transition: background 1.5s ease-in-out;
        }
    </style>
</head>

<body>
    <div class="d-flex flex-column">
        <div class="page page-center">
            <div class="container container-normal mt-5">
                <div class="row align-items-center">
                    <div class="col-lg">
                        <div class="container-tight">
                            <div class="card card-md">
                                {{-- Image Logo --}}
                                <div class="text-center mt-3">
                                    {{-- <img src="{{ asset('images/login.jpg') }}" class="avatar"
                    alt="HMS" height="100" width="100"> --}}
                                </div>
                                <h1 class="text-center p-3">Hotel Management System</h1>
                                <h3 class="text-center">Create your account</h3>

                                <div class="card-body">
                                    @include('includes.alerts')
                                    <form method="POST" action="{{ route('register') }}">
                                        @csrf

                                        <div class="row col-md-12">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Full Name <span class="text fw-bold text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control"
                                                    placeholder="Enter your name">
                                                @error('name')
                                                    <span class="text fw-bold text-danger">{{$message}}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Mobile <span class="text fw-bold text-danger">*</span></label>
                                                <input type="text" name="mobile" class="form-control"
                                                    placeholder="Enter mobile number">
                                                @error('mobile')
                                                    <span class="text fw-bold text-danger">{{$message}}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row col-md-12">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Password <span class="text fw-bold text-danger">*</span></label>
                                                <div class="input-group input-group-flat">
                                                    <input type="password" name="password" class="form-control"
                                                        id="password" placeholder="Password" autocomplete="off">
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
                                                     @error('password')
                                                        <span class="text fw-bold text-danger">{{$message}}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Confirm Password</label>
                                                <input type="password" name="password_confirmation" class="form-control"
                                                    placeholder="Re-enter password" autocomplete="off">
                                                @error('password_confirmation')
                                                    <span class="text fw-bold text-danger">{{$message}}</span>
                                                @enderror
                                            </div>
                                        </div>


                                        <div class="mt-3 text-end">
                                            <a href="{{ route('login') }}"
                                                class="me-3 text-decoration-none text-secondary fw-bold">Already have an
                                                account?</a>
                                            <button type="submit" class="btn btn-success">Register</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const showIcons = document.querySelectorAll(".password--show");
            const hideIcons = document.querySelectorAll(".password--hide");

            showIcons.forEach(icon => {
                icon.addEventListener("click", function(e) {
                    e.preventDefault();
                    const passwordInput = document.getElementById("password");
                    passwordInput.type = "text";

                    // Toggle icon visibility
                    icon.classList.add("d-none");
                    const hideIcon = icon.parentElement.querySelector(".password--hide");
                    hideIcon.classList.remove("d-none");
                });
            });

            hideIcons.forEach(icon => {
                icon.addEventListener("click", function(e) {
                    e.preventDefault();
                    const passwordInput = document.getElementById("password");
                    passwordInput.type = "password";

                    // Toggle icon visibility
                    icon.classList.add("d-none");
                    const showIcon = icon.parentElement.querySelector(".password--show");
                    showIcon.classList.remove("d-none");
                });
            });
        });
    </script>


</body>

</html>
