<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Exhibitor registration</title>

    <!-- CSS files -->
    <link href="{{ asset('/theme/css/tabler.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/theme/css/tabler-vendors.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/theme/css/demo.min.css') }}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="page-wrapper p-3">
            <div class="row">@include('includes.alerts')</div>
            <form method="POST" action="{{ route('visitor.store') }}" class="card">
                @csrf
                <div class="card-header">
                    <h3 class="card-title mx-auto">Visitor Registration</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">

                            <div class="mb-3 row">
                                <label for="eventId" class="col-4 form-label required">Event</label>
                                <div class="col">
                                    <select class="form-select mb-2" name="eventId">
                                        <option value="">Select Event</option>
                                        @foreach ($events as $eventID => $eventTitle)
                                            <option value="{{ $eventID }}" {{ old('eventId') == $eventID ? 'selected' : '' }}>
                                                {{ $eventTitle }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('eventId')
                                        <span class="error text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>                            

                            <div class="mb-3 row">
                                <div class="col-4">
                                    <label for="event" class="form-label required">Name</label>
                                </div>
                                <div class="col">
                                    <div class="input-group mb-2">
                                        <span class="input-group-select">
                                            <select class="form-select" name="salutation" id="salutation">
                                                <option value="Dr" {{ old('salutation') == 'Dr' ? 'selected' : '' }}>Dr</option>
                                                <option value="Mr" {{ old('salutation') == 'Mr' ? 'selected' : '' }}>Mr</option>
                                                <option value="Ms" {{ old('salutation') == 'Ms' ? 'selected' : '' }}>Ms</option>
                                                <option value="Mrs" {{ old('salutation') == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                            </select>
                                        </span>
                                        <input type="text" class="form-control col-sm-10" name="name" id="name" value="{{ old('name') }}">
                                    </div>
                                </div>
                            </div>                            

                            <div class="mb-3 row">
                                <label for="mobileNumber" class="col-4 form-label required mb-0">Mobile Number</label>
                                <div class="col">
                                    <input type="text" class="form-control" name="mobileNumber" id="mobileNumber" value="{{ old('mobileNumber') }}">
                                    @error('mobileNumber')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="designation" class="col-4 form-label required">Designation</label>
                                <div class="col">
                                    <input type="text" class="form-control" name="designation" id="designation" value="{{ old('designation') }}">
                                    @error('designation')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="categoryId" class="col-4 form-label required">Nature of Business</label>
                                <div class="col">
                                    <select class="form-select" name="categoryId" id="categoryId">
                                        <option value="">Select a category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('categoryId') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('categoryId')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>                            

                            <div class="mb-2 row">
                                <label for="product_looking_for" class="col-4 form-label d-block">Product Looking for</label>
                                <div class="col">
                                    <select id="products" name="products[]" multiple>
                                        <option value="">Select products</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" {{ in_array($product->id, old('products', [])) ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>                            
                        </div>

                        <div class="col-md-6">
                            <!-- Name field -->
                            <div class="mb-3 row">
                                <label for="profile_name" class="col-4 form-label required mb-0">Profile Name</label>
                                <div class="col mb-3">
                                    <input type="text" class="form-control" name="username" id="username"
                                        pattern="^\S+$" value="{{ old('username') }}">

                                    {{-- @if (!$username_exists && $visitor['username'] !== '')
                                    <div class="text-success">Username is available</div>
                                    @endif --}}
                                    @error('username')
                                    <span class="text-danger ">{{ $message }}</span>
                                    {{-- @if ($visitor['name'] !== '')
                                    <p class="text-success cursor-pointer" wire:click="setSuggestedValue">
                                        Suggested:
                                        {{ $suggestedValue }}</p>
                                    @endif --}}
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="email" class="col-4 form-label required mb-0">Email</label>
                                <div class="col">
                                    <input type="text" class="form-control" name="email" id="email" value="{{ old('email') }}">
                                    @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="organization" class="col-4 form-label required mb-0">Organization</label>
                                <div class="col">
                                    <input type="text" class="form-control" name="organization" id="organization" value="{{ old('organization') }}">
                                    @error('organization')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="knownSource" class="col-4 form-label mb-0">Known Source</label>
                                @php
                                    $knowSources = getKnownSourceData();
                                    $oldKnownSource = old('knownSource');
                                @endphp
                                <div class="col">
                                    <select class="form-select" name="knownSource" id="knownSource">
                                        <option value="">Select known source</option>
                                        @foreach ($knowSources as $knowSourceKey => $knowSourceLabel)
                                            <option value="{{ $knowSourceKey }}" {{ $oldKnownSource == $knowSourceKey ? 'selected' : '' }}>
                                                {{ $knowSourceLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('knownSource')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>                            


                            <div class="mb-3 row">
                                <!-- Reason for Visit field -->
                                <div class="col-12">
                                    <label for="visitReason" class="form-label">Reason for Visit</label>
                                    <textarea class="form-control" rows="3" name="visitReason">{{ old('visitReason') }}</textarea>
                                </div>
                            </div>                            
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-3">
                            <label for="country" class="col-4 form-label">Country</label>
                            <select class="form-control" name="country" id="country">
                                @foreach ($countries as $country)
                                    <option value="{{ $country }}" {{ old('country') == $country ? 'selected' : '' }}>
                                        {{ $country }}
                                    </option>
                                @endforeach
                            </select>
                        </div>                        

                        <div class="col-md-3">
                            <label for="pincode" class="col-4 form-label required">Pincode
                                {{-- {{ $visitoraddress['country'] == 'India' ? 'Pincode' : 'Zipcode' }} --}}
                            </label>
                            <div>
                                <input type="text" class="form-control" name="pincode" id="pincode" value="{{ old('pincode') }}">
                                @error('pincode')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- @if ($visitoraddress['country'] === 'India') --}}
                        <div class="col-md-3">
                            <label for="city" class="col-4 form-label">City</label>
                            <div class="col">
                                <input type="text" class="form-control" name="city" id="city" value="{{ old('city') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label for="state" class="col-4 form-label">State</label>
                            <div class="col">
                                <input type="text" class="form-control" name="state" id="state" value="{{ old('state') }}">
                            </div>
                        </div>
                        {{-- @endif --}}
                    </div>

                    <div class="row mt-2">
                        <div class="col-12">
                            <label for="address" class="col-4 form-label required d-block">Address</label>
                            <div class="col">
                                <textarea class="form-control" rows="3" name="address">{{ old('address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-12">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter" style="border-color:rgb(134, 132, 132)"
                                {{ old('newsletter') ? 'checked' : '' }}>                            
                                <span class="form-check-label">Sign up for Newsletters, Industry and Show
                                    updates</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="reset" class="btn btn-primary">Reset</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="{{ asset('theme/js/tabler.min.js') }}" defer></script>
    <script src="{{ asset('theme/js/demo.js') }}" defer></script>
    <script src="{{ asset('theme/js/demo-theme.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        new TomSelect("#country",{
            plugins: ['dropdown_input'],
        });

        new TomSelect("#products",{
            plugins: ['dropdown_input', 'remove_button'],
            create: true,
            createOnBlur: true,
        });
    </script>
</body>

</html>