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
            <form method="POST" action="{{ route('exhibitor.store') }}">
                @csrf
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row row-cards">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title mx-auto">Exhibitor Registration</h3>
                                    </div>
                                    <div class="card-body mt-2">
                                        <div class="row row-cards">
                                            <div class="row mt-3">
                                                <div class="col-md-6 mb-3">
                                                    <label for="eventId" class="form-label required">Event</label>
                                                    <select class="form-select @error('eventId') is-invalid @enderror" name="eventId">
                                                        <option value="">Select Event</option>
                                                        @foreach ($events as $eventID => $eventTitle)
                                                            <option value="{{ $eventID }}" {{ old('eventId') == $eventID ? 'selected' : '' }}>
                                                                {{ $eventTitle }}
                                                            </option>
                                                        @endforeach
                                                    </select>                                                    
                                                    @error('eventId')
                                                    <div class="error text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="name" class="form-label required">Contact Person Name</label>
                                                    <div class="input-group">
                                                        <div class="mb-3 col-md-3" style="padding-right: 4px">
                                                            <select
                                                                class="form-select @error('salutation') is-invalid @enderror"
                                                                name="salutation" value="{{ old('salutation')  }}">
                                                                <option value="Dr">Dr</option>
                                                                <option value="Mr" selected>Mr</option>
                                                                <option value="Ms">Ms</option>
                                                                <option value="Mrs">Mrs</option>
                                                            </select>
                                                            @error('salutation')
                                                            <div class="error text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="mb-3 col-md-9">
                                                            <input type="text" placeholder="Enter your name" value="{{ old('name')  }}"
                                                                class="form-control @error('name') is-invalid @enderror" name="name">
                                                            @error('name')
                                                            <div class="error text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="mobileNumber" class="form-label required">Contact No.</label>
                                                        <input type="text" placeholder="Enter your contact number" value="{{ old('mobileNumber')  }}"
                                                            class="form-control @error('mobileNumber') is-invalid @enderror"
                                                            name="mobileNumber">
                                                        @error('mobileNumber')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="designation"
                                                            class="form-label required">Designation</label>
                                                        <input type="text" placeholder="Enter your designation" value="{{ old('designation')  }}"
                                                            class="form-control @error('designation') is-invalid @enderror"
                                                            name="designation">
                                                        @error('designation')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="companyName" class="form-label required">Company
                                                            Name</label>
                                                        <input type="text" placeholder="Enter company name"
                                                            class="form-control @error('companyName') is-invalid @enderror"
                                                            name="companyName" value="{{ old('companyName')  }}">
                                                        @error('companyName')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="categoryId" class="form-label required">Business
                                                            Type</label>
                                                            <select class="form-select @error('categoryId') is-invalid @enderror" name="categoryId">
                                                                <option value="">Select Business Type</option>
                                                                @foreach ($categories as $category)
                                                                    <option value="{{ $category->id }}" {{ old('categoryId') == $category->id ? 'selected' : '' }}>
                                                                        {{ $category->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>                                                            
                                                        @error('categoryId')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="username" class="form-label required">Profile
                                                            Name</label>
                                                        <input type="text" placeholder="Enter company profile name"
                                                            id="username"
                                                            class="form-control @error('username') is-invalid @enderror"
                                                            name="username" value="{{ old('username')  }}">
                                                            @error('username')
                                                            <div class="error text-danger">{{ $message }}</div>
                                                            @enderror

                                                        {{-- @if (!$username_exists && $exhibitor['username'] !== '')
                                                        <div class="text-success">Username is available</div>
                                                        @endif --}}

                                                        {{-- @error('username')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                        @if ($exhibitor['company_name'] !== '')
                                                        <p class="text-success cursor-pointer"
                                                            wire:click="setSuggestedValue">Suggested:
                                                            {{ $suggestedValue }}</p>
                                                        @endif
                                                        @enderror --}}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="email" class="form-label required">Email</label>
                                                        <input type="email" placeholder="Enter email"
                                                            class="form-control @error('email') is-invalid @enderror"
                                                            name="email" value="{{ old('email')  }}">
                                                        @error('email')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="contactNumber" class="form-label required">Phone
                                                            No.</label>
                                                        <input type="text" placeholder="Enter company phone number."
                                                            class="form-control @error('contactNumber') is-invalid @enderror"
                                                            name="contactNumber" value="{{ old('contactNumber')  }}">
                                                        @error('contactNumber')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3" id="ts">
                                                        <label class="form-label required">Products</label>
                                                        <div wire:ignore>
                                                            <select id="products" class="form-select @error('products') is-invalid @enderror" name="products[]" placeholder="Select Products" multiple>
                                                                @foreach ($products as $productId => $productName)
                                                                    <option value="{{ $productId }}" {{ in_array($productId, old('products', [])) ? 'selected' : '' }}>
                                                                        {{ $productName }}
                                                                    </option>
                                                                @endforeach
                                                            </select>                                                            
                                                        </div>
                                                        @error('products')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="knownSource" class="form-label required">How did you
                                                            come
                                                            to
                                                            know</label>
                                                        @php
                                                        $knowSources = getKnownSourceData();
                                                        @endphp
                                                        <select
                                                            class="form-select @error('knownSource') is-invalid @enderror"
                                                            name="knownSource">
                                                            <option>Select Known Source</option>
                                                            @foreach ($knowSources as $knowSourceKey => $knowSourceLabel)
                                                            <option value="{{ $knowSourceKey }}" {{ old('knownSource') == $knowSourceKey ? 'selected' : '' }}>
                                                                {{ $knowSourceLabel }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                        @error('knownSource')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">

                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="country" class="form-label required">Country</label>
                                                        <div wire:ignore id="ts1">
                                                            <select id="country"
                                                                class="form-select @error('country') is-invalid @enderror"
                                                                name="country" value="{{ old('country')  }}">
                                                                @foreach ($countries as $country)
                                                                <option value={{ $country }}>{{ $country }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        @error('country')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="pincode" class="form-label required">Pincode
                                                            {{-- {{ $exhibitor['country'] == 'India' ? 'Pincode' :
                                                            'Zipcode'}} --}}
                                                        </label>
                                                        <input type="text" id="pincode" {{--
                                                            placeholder="Enter {{ $exhibitor['country'] == 'India' ? 'Pincode' : 'Zipcode' }}"
                                                            --}}
                                                            class="form-control @error('pincode') is-invalid @enderror"
                                                            name="pincode" value="{{ old('pincode')  }}">
                                                        @error('pincode')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div {{-- @if ($exhibitor['country'] !='India' )
                                                        style="display: none;" @endif --}}>
                                                        <label for="city" class="form-label">City</label>
                                                        <div>
                                                            <input type="text" id="city" disabled
                                                                class="form-control @error('city') is-invalid @enderror"
                                                                name="city" value="{{ old('city')  }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div {{-- @if ($exhibitor['country'] !='India' )
                                                        style="display: none;" @endif --}}>
                                                        <label for="state" class="form-label">State</label>
                                                        <div>
                                                            <input type="text" id="state" disabled
                                                                class="form-control @error('state') is-invalid @enderror"
                                                                name="state" value="{{ old('state')  }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="address" class="form-label required">Address</label>
                                                    <textarea placeholder="Enter address" rows="3"
                                                        class="form-control @error('address') is-invalid @enderror"
                                                        name="address">{{ old('address')  }}</textarea>
                                                    @error('address')
                                                    <div class="error text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div>
                                                    <label class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="newsletter" style="border-color:rgb(134, 132, 132);" value="1" {{ old('newsletter') ? 'checked' : '' }}>
                                                        <span class="form-check-label"> Sign up for Newsletters,
                                                            Industry
                                                            and Show updates</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-end mt-3">
                                        <button type="reset" class="btn btn-primary">Reset</button>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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