@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between">
            <div>
                <h4 class="text">{{ isset($eventId) ? 'Edit Event' : 'New Event' }}</h4>
            </div>
            <div class="d-flex">
                @if (isset($eventId))
                    <div class="mb-2">
                        <a class="btn btn-primary" href="{{ route('create-event') }}"> New Event </a>
                    </div>
                @endif
                <div class="mb-2 ms-2">
                    <a class="btn btn-secondary" href="{{ route('events') }}"> Back To Summary </a>
                </div>
            </div>
        </div>

        <div class="card">
            <form id='eventForm' wire:submit={{ isset($eventId) ? 'update' : 'create' }}>
                @csrf
                <div class="card-body">
                    <div class="row row-cards">
                        @if (isset($eventId))
                            <div class="col-md-12 text-center mb-3">
                                @php
                                    $eventImagePath =
                                        $this->event['thumbnail'] ?? 'thumbnail/2023/11/medicall-logo-min.png';
                                @endphp
                                <img src="{{ asset('storage/' . $eventImagePath) }}" class="rounded-circle avatar-xl"
                                    height="70" width="70" />
                                <input type="file" class="form-control d-none" id="updateImage" wire:model="photo" />
                                <button class="btn btn-outline-primary mt-3 ms-2"
                                    onclick="event.preventDefault(); document.getElementById('updateImage').click();">
                                    @include('icons.edit') Update Thumbnail
                                </button>
                                <span wire:loading wire:target="photo" class="text-info mt-2">Uploading...</span>
                            </div>

                            <div class="col-md-6 mb-3">
                                <input type="file" class="form-control d-none" id="updateFile"
                                    wire:model="hallLayout" />
                                <br>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Change Hall Layout</span>
                                    <button class="btn btn-outline-success"
                                        onclick="event.preventDefault(); document.getElementById('updateFile').click();">
                                        @include('icons.edit') Change
                                    </button>
                                </div>
                                <span wire:loading wire:target="hallLayout" class="text-info mt-2">
                                    Uploading...
                                </span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="file"class="form-control  d-none" id="updateExhibitorList"
                                    wire:model="exhibitorList" />
                                <br>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Change Exhibitor List</span>
                                    <button class="btn btn-outline-success"
                                        onclick="document.getElementById('updateExhibitorList').click(event.preventDefault())">
                                        @include('icons.edit')
                                        Change
                                    </button>
                                </div>
                                <span wire:loading wire:target="exhibitorList" class="text-info mt-2">
                                    Uploading...
                                </span>
                            </div>
                        @else
                            <div class="row mt-3">

                                <div class="col-md-4">
                                    <div class="mb-2">
                                        <div class="avatar-edit ">
                                            <label class="form-label" for="photo">Image Upload</label>
                                            <input type='file' class="form-control" wire:model="photo" id="photo"
                                                name="photo" />
                                            <span wire:loading wire:target="photo" class="text-info fw-bold mt-2 fs-5">
                                                Uploading...
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-2">
                                        <div class="avatar-edit ">
                                            <label class="form-label" for="fileUpload">Hall Layout</label>
                                            <input type='file' class="form-control" wire:model="hallLayout"
                                                id="fileUpload" />
                                            <span wire:loading wire:target="hallLayout"
                                                class="text-info fw-bold mt-2 fs-5">
                                                Uploading...
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-2">
                                        <div class="avatar-edit ">
                                            <label class="form-label" for="fileUpload">Exhibitor List</label>
                                            <input type='file' class="form-control" wire:model="exhibitorList"
                                                id="fileUpload" />
                                            <span wire:loading wire:target="exhibitorList"
                                                class="text-info fw-bold mt-2 fs-5">
                                                Uploading...
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endif

                        <div class="col-sm-3">
                            <div class="mb-2">
                                <label class="form-label required" for="edition">Edition</label>
                                <input id="edition" type="text" @class([
                                    'form-control',
                                    'is-invalid' => $errors->has('edition') ? true : false,
                                ])
                                    wire:model.live="edition" placeholder="Event Title" disabled>
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="mb-2">
                                <label class="form-label required" for="location">Location</label>
                                <select id="location" @class([
                                    'form-select',
                                    'is-invalid' => $errors->has('location') ? true : false,
                                ]) wire:model.live="location">
                                    <option value="">Select location</option>
                                    <option value="Chennai">Chennai</option>
                                    <option value="Delhi">Delhi</option>
                                    <option value="Hyderabad">Hyderabad</option>
                                    <option value="Mumbai">Mumbai</option>
                                    <option value="Kolkata">Kolkata</option>
                                </select>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label required" for="year_of_event">Year of the event</label>
                            <select id="year_of_event" @class([
                                'form-select',
                                'is-invalid' => $errors->has('year_of_event') ? true : false,
                            ]) wire:model.live="year_of_event">
                                <option value="">Select Event Year</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach

                            </select>
                            @error('year_of_event')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label required" for="description">Event Description</label>
                            <input id="description" type="text" @class([
                                'form-control',
                                'is-invalid' => $errors->has('event.description') ? true : false,
                            ])
                                wire:model="event.description" placeholder="Event description" disabled>
                            @error('event.description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label required" for="code">Event Code</label>
                            <input type="text" class="form-control" placeholder="Select a date" id="code"
                                name="event.code" wire:model="event.code" disabled>
                            {{-- @dump($event['code']) --}}
                            @error('event.code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label required" for="eventPeriod">Event Period</label>
                            <input type="text" class="form-control" placeholder="Select a date" id="eventPeriod"
                                name="event.eventPeriod" wire:model="event.eventPeriod">
                            @error('event.eventPeriod')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class=" col-md-3">
                            <label class="form-label required" for="title">Title</label>
                            <input id="title" type="text" @class([
                                'form-control',
                                'is-invalid' => $errors->has('event.title') ? true : false,
                            ])
                                wire:model="event.title" placeholder="Event Title">
                            @error('event.title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-3">
                            <div class="mb-2">
                                <label class="form-label required" for="startDate">Start Date</label>
                                <input id="startDate" type="date" @class([
                                    'form-control',
                                    'is-invalid' => $errors->has('event.startDate') ? true : false,
                                ])
                                    wire:model="event.startDate" placeholder="dd-mm-yyyy">
                                @error('event.startDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="mb-2">
                                <label class="form-label required" for="endDate">End Date</label>
                                <input id="endDate" type="date" @class([
                                    'form-control',
                                    'is-invalid' => $errors->has('event.endDate') ? true : false,
                                ])
                                    wire:model="event.endDate" placeholder="dd-mm-yyyy">
                                @error('event.endDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <label class="form-label required" for="invoice-title">Invoice Title</label>
                                <input id="invoice-title"
                                    class="form-control @error('event.invoiceTitle') is-invalid @enderror"
                                    wire:model="event.invoiceTitle" placeholder="Invoice Title"
                                    autocomplete="off"></input>
                                @error('event.invoiceTitle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <label class="form-label required" for="latitude">Latitude</label>
                                <input id="latitude"
                                    class="form-control @error('event.latitude') is-invalid @enderror"
                                    wire:model="event.latitude" placeholder="Latitude Value"
                                    autocomplete="off"></input>
                                @error('event.latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <label class="form-label required" for="longitude">Longitude</label>
                                <input id="longitude"
                                    class="form-control @error('event.longitude') is-invalid @enderror"
                                    wire:model="event.longitude" placeholder="Longitude Value"
                                    autocomplete="off"></input>
                                @error('event.longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2" id="ts">
                                <div wire:ignore>
                                    <label class="form-label required tomselect" for="country">Country</label>
                                    <select id="country" type="select" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('event.country') ? true : false,
                                    ])
                                        wire:model.live="event.country" wire:click.prevent="pincode"
                                        autocomplete="off">
                                        <option> </option>
                                        @foreach ($countries as $country)
                                            <option value={{ $country }}>{{ $country }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('event.country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <label class="form-label required"
                                    for="pincode">{{ $event['country'] == 'India' ? 'Pincode' : 'Zipcode' }}</label>
                                <input id="pincode" type="text" @class([
                                    'form-control',
                                    'is-invalid' => $errors->has('event.pincode') ? true : false,
                                ])
                                    wire:model="event.pincode" wire:blur="pincode"
                                    placeholder={{ $event['country'] == 'India' ? 'Postal Code' : 'Zip Code' }}>
                                @error('event.pincode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @if ($event['country'] == 'India')
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <label class="form-label" for="city">City</label>
                                    <input id="city" type="text" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('event.city') ? true : false,
                                    ])
                                        wire:model.live="event.city" placeholder="City" disabled>
                                    @error('event.city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <label class="form-label" for="state">State</label>
                                    <input id="state" type="text" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('event.state') ? true : false,
                                    ])
                                        wire:model.live="event.state" placeholder="State" disabled>
                                    @error('event.state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        <div class="col-md-12">
                            <div class="mb-2">
                                <label class="form-label required" for ="address">Address</label>
                                <textarea id="address" @class([
                                    'form-control',
                                    'is-invalid' => $errors->has('event.address') ? true : false,
                                ])wire:model="event.address" placeholder="Event Address"
                                    autocomplete="off"></textarea>
                                @error('event.address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>
                <div class="card-footer text-end">
                    @if ($eventId)
                        <a href={{ route('events') }} class="text-danger me-2"> Cancel </a>
                    @else
                        <a href=# wire:click.prevent ="resetFields" class="text-danger me-2"> Reset </a>
                    @endif
                    <button type ="submit"
                        class="btn btn-primary">{{ isset($eventId) ? 'Update Event' : 'Create Event' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script>
        document.addEventListener('livewire:initialized', function() {
            var countries = new TomSelect('#country', {
                plugins: ['dropdown_input'],
            });

            $('#eventPeriod').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'DD-MM-YYYY'
                },
                opens: 'left'
            });

            var startDate;
            var endDate;

            $('#eventPeriod').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
                    'DD-MM-YYYY'));
                startDate = picker.startDate.format('YYYY-MM-DD');
                endDate = picker.endDate.format('YYYY-MM-DD');
                @this.call('setEventPeriod', startDate,
                    endDate);
            });

            $('#eventPeriod').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                startDate = null;
                endDate = null;
                @this.call('setEventPeriod', startDate, endDate);
            });

        });
    </script>
@endpush
