@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush
<div class="page-body">
    <div class="container">

        <div class="row row-cards">
            <div class="col-12">
                @include('includes.alerts')
                <form wire:submit="create" class="card" id="seminar-form">
                    <div class="card-header">
                        <h3 class="card-title mx-auto">Seminar/Workshop Registration</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">

                                <div class="mb-3 row">
                                    <label for="event" class="col-4 form-label required ">Event</label>
                                    <div class="col">
                                        <select class="form-select mb-2" wire:change="getCurrentEventSeminars"
                                            wire:model.live="delegate.event_id" {{ isset($eventId) ? 'disabled' : '' }}>
                                            <option value="">Select Event</option>
                                            @foreach ($events as $eventID => $eventTitle)
                                                <option value="{{ $eventID }}">{{ $eventTitle }}</option>
                                            @endforeach
                                        </select>
                                        @error('delegate.event_id')
                                            <span class="error text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <div class="col-4">
                                        <label for="event" class="form-label required ">Name</label>
                                    </div>
                                    <div class="col">
                                        <div class="row">
                                            <div class="col-md-3 pe-0">
                                                <select class="form-select" wire:model="delegate.salutation"
                                                    id="salutation">
                                                    <option value='Dr'>Dr</option>
                                                    <option value='Mr'>Mr</option>
                                                    <option value='Ms'>Ms</option>
                                                    <option value='Mrs'>Mrs</option>
                                                </select>
                                            </div>
                                            <div class="col-md-9 ps-0">
                                                <input type="text" class="form-control" wire:model="delegate.name"
                                                    id="name">
                                                @error('delegate.name')
                                                    <span class="text-danger ">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                    </div>
                                </div>



                                <div class="mb-3 row">
                                    <label for="mobile_number" class="col-4 form-label required mb-0">Mobile
                                        Number</label>
                                    <div class="col">
                                        <input type="text" class="form-control" wire:model="delegate.mobile_number"
                                            id="mobile_number">
                                        @error('delegate.mobile_number')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="designation" class="col-4 form-label required">Designation</label>
                                    <div class="col">
                                        <input type="text" class="form-control" wire:model="delegate.designation"
                                            id="designation">
                                        @error('delegate.designation')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>



                                <div class="mb-2 row">
                                    <label for="Seminar_to_attend"
                                        class="col-4 form-label d-block required">Seminar_to_attend</label>
                                    <div class="col" id="seminar">
                                        <div wire:ignore>
                                            <select id="seminars_to_attend"
                                                wire:model.live="delegate.seminars_to_attend" multiple>
                                                <option value="">Select seminars</option>
                                                @foreach ($currentEventSeminars as $seminar)
                                                    <option value="{{ $seminar->id }}">{{ $seminar->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('delegate.seminars_to_attend')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>


                            </div>

                            <div class="col-md-6">
                                <div class="mb-3 row">
                                    <label for="email" class="col-4 form-label required mb-0">Email</label>
                                    <div class="col">
                                        <input type="text" class="form-control" wire:model="delegate.email"
                                            id="email">
                                        @error('delegate.email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label for="organization"
                                        class="col-4 form-label required mb-0">Organization</label>
                                    <div class="col">
                                        <input type="text" class="form-control" wire:model="delegate.organization"
                                            id="organization">
                                        @error('delegate.organization')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label for="known_source" class="col-4 form-label mb-0">Known Source</label>
                                    @php
                                        $knowSources = getKnownSourceData();
                                    @endphp
                                    <div class="col">
                                        <select class="form-select" wire:model="delegate.known_source"
                                            id="known_source">
                                            <option>Select known source</option>
                                            @foreach ($knowSources as $knowSourceKey => $knowSourceLabel)
                                                <option value="{{ $knowSourceKey }}">{{ $knowSourceLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('delegate.known_source')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <div class="col-12">
                                        <label for="query" class="form-label">Ask you queries / Questions to the
                                            Panelist</label>
                                        <textarea class="form-control" rows="3" wire:model="delegate.query"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2 mb-3">
                            <div class="col-md-3">
                                <label for="country" class="col-4 form-label">Country</label>
                                <div>
                                    <select class="form-control" wire:model.live="delegate.country"
                                        wire:change="clearAddressFields" id="country">
                                        @foreach ($countries as $country)
                                            <option value={{ $country }}>{{ $country }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label for="pincode" class="col-4 form-label required">
                                    {{ $delegate['country'] == 'India' ? 'Pincode' : 'Zipcode' }}</label>
                                <div>
                                    <input type="text" class="form-control" wire:model.live="delegate.pincode"
                                        wire:blur='pincode()' id="pincode">
                                    @error('delegate.pincode')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            @if ($delegate['country'] === 'India')
                                <div class="col-md-3">
                                    <label for="city" class="col-4 form-label">City</label>
                                    <div class="col">
                                        <input type="text" class="form-control" wire:model.live="delegate.city"
                                            id="city">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="state" class="col-4 form-label">State</label>
                                    <div class="col">
                                        <input type="text" class="form-control" wire:model.live="delegate.state"
                                            id="state">
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-12 mb-3">
                            <label for="address" class="col-4 form-label required d-block">Address</label>
                            <div class="col">
                                <textarea class="form-control" rows="3" wire:model.live="delegate.address"></textarea>
                                @error('delegate.address')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3 row col-12">
                            <div class="col-md-4">
                                <label class="form-label" for="payment_option">Payment Option</label>
                                <select name="payment_option" wire:model="delegate.payment_option"
                                    class="form-control">
                                    <option value="register_and_pay">Register & Pay</option>
                                    <option value="register_and_pay_later">Register & Pay Later</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Amount</label>
                                <input type="text" class="form-control" wire:model="delegate.amount" disabled>
                            </div>
                            <div class="col-md-4">
                                <label for="coupon" class="form-label">If you have coupon, Enter the
                                    code</label>
                                <input type="text" class="form-control" wire:model="delegate.coupen_code"
                                    id="coupon">
                            </div>
                        </div>


                        <div class="row mt-2">
                            <div class="col-12">
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletter"
                                        wire:model="delegate.newsletter" style="border-color:rgb(134, 132, 132);">
                                    <span class="form-check-label">Sign up for Newsletters, Industry and Show
                                        updates</span>
                                    <br>
                                    <input class="form-check-input" type="checkbox" id="newsletter"
                                        style="border-color:rgb(134, 132, 132);">
                                    <span class="form-check-label">
                                        I hereby agree to terms and Conditions
                                        <span style="color: red;">*</span>
                                    </span>

                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <a href="{{ route('delegate-registration') }}" class="text-danger me-2">Reset</a>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', function() {
            var productSelect = new TomSelect('#seminars_to_attend', {
                plugins: ['dropdown_input', 'remove_button'],
                valueField: 'id',
                labelField: 'title',
                searchField: 'title',
                create: false,
                persist: false,
            });

            Livewire.on('changeEvent', function(seminars) {
                productSelect.clearOptions();
                productSelect.addOption(seminars);
            });
        });
    </script>
@endpush
