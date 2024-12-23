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

<body>

    <div class=" d-flex flex-column">
        <div class="page page-center">
            <div class="container container-normal py-4">
                <div class="row align-items-center g-4">
                    <div class="col-lg">
                        <div class="container-tight ">
                            <div class="text-center mb-4">
                                <a href="#" class="navbar-brand navbar-brand-autodark"></a>
                            </div>
                            <div class="card card-md">
                                <div class="card-body">
                                    <h1 class="h1 text-center mb-2">Medicall Meet</h1>
                                    <h3 class="h3 text-center mb-4">Login to your account</h3>
                                    @include('includes.alerts')
                                    <form method="POST" action="{{ route('admin.login') }}">
                                        @csrf
                                        @php
                                            $requestedOtp =
                                                Session::has('requested_otp') && Session::get('requested_otp') == 'yes'
                                                    ? true
                                                    : false;

                                            $mobileNo = Session::has('mobile_no') ? Session::get('mobile_no') : null;
                                        @endphp
                                        <div class="mb-3">
                                            <label class="form-label">Email/Mobile</label>
                                            <input type="text" class="form-control" name="email"
                                                placeholder="Enter email or mobile number"
                                                value="{{ $mobileNo ?? '' }}">
                                        </div>

                                        <div class="mb-2 {{ $requestedOtp ? 'd-none' : '' }}" id="field__password">
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
                                        <div class="mb-2  {{ !$requestedOtp ? 'd-none' : '' }}" id="field__otp">
                                            <label class="form-label" id="label__otp">
                                                OTP
                                            </label>
                                            <div class="input-group input-group-flat" id="field__otp">
                                                <input type="password" name="otp" class="form-control"
                                                    id="otp" placeholder="******" autocomplete="off"
                                                    maxlength="6">

                                                <span class="input-group-text">
                                                    <a href="#"
                                                        class="link-secondary password--visibility-icons otp--show"
                                                        data-target="otp--hide" data-action="show" title="Show OTP"
                                                        data-bs-toggle="tooltip">
                                                        @include('icons.eye')
                                                    </a>
                                                    <a class="link-secondary d-none password--visibility-icons otp--hide"
                                                        data-target="otp--show" data-action="hide" title="Hide OTP"
                                                        data-bs-toggle="tooltip">
                                                        @include('icons.eye-off')
                                                    </a>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <a href="javascript:void(0);"
                                                class="sign-in-controls {{ $requestedOtp ? 'd-none' : '' }} }}"
                                                id="sign-in-use-otp" data-target="#sign-in-use-password"
                                                data-show-target-button="#request-otp"
                                                data-hide-target-button="#sign-in-form"
                                                data-target-input-field="#field__otp"
                                                data-taget-input-toggle-field="#field__password"><b>Sign in
                                                    using WhatsApp OTP</b></a>

                                            <a href="javascript:void(0);"
                                                class="sign-in-controls {{ !$requestedOtp ? 'd-none' : '' }} }}"
                                                id="sign-in-use-password" data-target="#sign-in-use-otp"
                                                data-show-target-button="#sign-in-form"
                                                data-hide-target-button="#request-otp"
                                                data-target-input-field="#field__password"
                                                data-taget-input-toggle-field="#field__otp"><b>Sign in
                                                    using Password</b></a>

                                        </div>

                                        <div class="form-footer mt-3">
                                            <button type="submit" id="sign-in-form"
                                                class="btn btn-primary w-100 {{ $requestedOtp && !session('show_sign_in_button') ? 'd-none' : '' }}">Sign
                                                in</button>
                                            <button type="button" id="request-otp"
                                                class="btn btn-outline-danger w-100 {{ !$requestedOtp || session('show_sign_in_button') ? 'd-none' : '' }}">Request
                                                OTP</button>
                                        </div>
                                    </form>

                                    <form class="d-none" id="otp-request-form" method="POST"
                                        action="{{ route('request-otp') }}">
                                        @csrf
                                        <input type="hidden" name="mobile_number" id="request-mobile-no"
                                            value="{{ old('email') }}">
                                    </form>

                                    {{-- <div class="text-center mx-auto  mt-3">
                                        <a href="https://crm.medicall.in/"><span>
                                                @include('icons.home')
                                                Home Page
                                            </span></a>
                                    </div> --}}

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
        jQuery(document).ready(function() {

            jQuery('.sign-in-controls').on("click", function() {
                const target = jQuery(this).attr('data-target');
                const showButtonId = jQuery(this).attr('data-show-target-button');
                const hideButtonId = jQuery(this).attr('data-hide-target-button');
                const targetInputField = jQuery(this).attr('data-target-input-field');
                const targetInputToggleField = jQuery(this).attr('data-taget-input-toggle-field');

                jQuery(target).removeClass('d-none');
                jQuery(this).addClass('d-none');
                jQuery(showButtonId).removeClass('d-none');
                jQuery(hideButtonId).addClass('d-none');
                jQuery(targetInputField).removeClass('d-none');
                jQuery(targetInputToggleField).addClass('d-none');

            });

            jQuery(".password--visibility-icons").on('click', function() {
                let target = jQuery(this).data('target');
                let name = (target === 'password--show' || target === 'password--hide') ? 'password' :
                    'otp';
                let action = jQuery(this).data('action');
                jQuery(this).toggleClass('d-none');
                jQuery('.' + target).toggleClass('d-none');
                if (action == 'show') {
                    jQuery('input[name="' + name + '"]').attr('type', 'text');
                } else {
                    jQuery('input[name="' + name + '"]').attr('type', 'password');
                }
            });

            jQuery("#request-otp").on('click', function() {

                let mobileNo = jQuery('input[name="email"]').val();
                if (mobileNo == '') {
                    alert('Please enter mobile no');
                    return false;
                }

                jQuery("#request-mobile-no").val(mobileNo);

                jQuery("#otp-request-form").submit();
            });


        });
    </script>

</body>

</html>
