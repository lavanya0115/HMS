<div class="page-header">
    <div class="container">
        <sapn>
            @include('includes.alerts')
        </sapn>
        <div class="d-flex justify-content-between">
            <div>
                <h4 class="text">{{ isset($followUpId) ? 'Edit Follow Up' : 'Follow Up' }}</h4>
            </div>
            <div class="d-flex">
                <div class="mb-2 ms-2">
                    <a class="btn btn-primary" href="{{ route('followup-summary', ['potentialId' => $potentialId]) }}">
                        Activity History </a>
                </div>
                <div class="mb-2 ms-2">
                    <a class="btn btn-secondary" href="{{ route('potential-summary') }}"> Back To Potential List </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                {{-- <form wire:submit.prevent="createFollowUp" id="followUpForm"> --}}
                <form id="followUpForm">
                    @csrf
                    <div class="row row-cards">

                        <div class="col-md-3">
                            <label class="form-label " for="event">Event</label>
                            <input type="text" class="form-control" id="event" disabled
                                wire:model="potential.event_name">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label " for="lead_name">Lead</label>
                            <input type="text" class="form-control" id="lead_name" disabled
                                wire:model="potential.lead_name">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label " for="lead-id">Lead Id</label>
                            <input id="lead-id" type="text" @class(['form-control'])
                                wire:model="potential.lead_id" disabled>

                        </div>

                        <div class="col-md-3">
                            <label class="form-label " for="category">Lead Category</label>
                            <input type="text" class="form-control" id="category" name="potential.lead_category"
                                wire:model="potential.lead_category" disabled>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label " for="assigned_to">Assigned To</label>
                            <input type="text" class="form-control" id="assigned_to" disabled
                                wire:model="potential.assigned_id">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label " for="contact">Primary Contact</label>
                            <input type="text" class="form-control" id="contact" disabled
                                wire:model="potential.primary_contact">
                        </div>

                        <div class=" col-md-6">
                            <label class="form-label " for="address">Street Address</label>
                            <input id="address" type="text" @class(['form-control'])
                                wire:model="potential.address" disabled>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-2" id="ts">
                                <label class="form-label required" for="status">New Status</label>
                                <select id="status" type="select" @class([
                                    'form-control',
                                    'is-invalid' => $errors->has('potential_status') ? true : false,
                                ])
                                    wire:model.live="potential_status">
                                    <option value="">Select Status</option>
                                    <option value="warm">Warm</option>
                                    <option value="cold">Cold</option>
                                    <option value="hot">Hot</option>
                                    <option value="closed-won">Closed Won</option>
                                    <option value="closed-lost">Closed Lost</option>
                                </select>
                            </div>
                            @error('potential_status')
                                <div class="text-danger text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <div class="mb-2" id="ts">
                                <label class="form-label required" for="tomselect-sales-person">Activity Type</label>
                                <select id="tomselect-sales-person" type="select" @class([
                                    'form-control',
                                    'is-invalid' => $errors->has('activity_type') ? true : false,
                                ])
                                    wire:model.live="activity_type">
                                    <option value="">Select Activity Type</option>
                                    <option value="call activity">Call Activity</option>
                                    <option value="negotiation">Negotiation</option>
                                </select>
                            </div>
                            @error('activity_type')
                                <div class="text-danger text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <div class="mb-2" id="ts">
                                <label class="form-label required" for="tomselect-sales-person">Contact Mode</label>
                                <select id="tomselect-sales-person" type="select" @class([
                                    'form-control',
                                    'is-invalid' => $errors->has('contact_mode') ? true : false,
                                ])
                                    wire:model.live="contact_mode">
                                    <option value="">Select Contact Mode</option>
                                    <option value="direct">Direct</option>
                                    <option value="email">Email</option>
                                    <option value="phone">Phone</option>
                                </select>
                            </div>
                            @error('contact_mode')
                                <div class="text-danger text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class=" col-md-6">
                            <label class="form-label " for="remarks">Remarks</label>
                            <textarea id="remarks" type="text" @class([
                                'form-control',
                                'is-invalid' => $errors->has('remarks') ? true : false,
                            ]) wire:model="remarks"></textarea>
                            @error('remarks')
                                <div class="text-danger text">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </form>
            </div>
            <div class="card-footer text-end">
                <a href="{{ route('potential-follow-up', ['potentialId' => $potentialId]) }}"
                    class="btn btn-danger">Cancel</a>
                <button type ="submit"
                    wire:click.prevent={{ isset($followUpId) ? 'updateFollowUp' : 'createFollowUp' }}
                    class="btn btn-primary">{{ isset($followUpId) ? 'Update' : 'Create' }}</button>
            </div>
        </div>
    </div>

    {{-- Edit lead Details Model --}}
    <div wire:ignore.self class="modal modal-blur fade " id="staticModal" role="dialog" aria-hidden="true"
        data-bs-backdrop='static' tabindex="-1" aria-labelledby="staticModalLabel">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticModalLabel">Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row row-cards">
                            <div class="col-12">
                                <div class="card-body mt-2">
                                    <div class="row row-cards">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="name" class="form-label required">Contact Person
                                                    Name</label>
                                                <div class="input-group">
                                                    <div class="mb-3 col-md-3" style="padding-right: 4px">

                                                        <select
                                                            class="form-select @error('lead.salutation') is-invalid @enderror"
                                                            wire:model.live="lead.salutation">
                                                            <option value="">Select Option</option>
                                                            <option value="Dr">Dr</option>
                                                            <option value="Mr">Mr</option>
                                                            <option value="Ms">Ms</option>
                                                            <option value="Mrs">Mrs</option>
                                                        </select>
                                                        @error('lead.salutation')
                                                            <div class="error text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="mb-3 col-md-9">
                                                        <input type="text" placeholder="Enter your name"
                                                            class="form-control @error('lead.name') is-invalid @enderror"
                                                            wire:model="lead.name">
                                                        @error('lead.name')
                                                            <div class="error text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="mobileNumber" class="form-label required">Contact
                                                        No.</label>
                                                    <input type="text" placeholder="Enter your contact number"
                                                        class="form-control @error('lead.contact_number') is-invalid @enderror"
                                                        wire:model="lead.contact_number">
                                                    @error('lead.contact_number')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="designation"
                                                        class="form-label required">Designation</label>
                                                    <input type="text" placeholder="Enter your designation"
                                                        class="form-control @error('lead.designation') is-invalid @enderror"
                                                        wire:model="lead.designation">
                                                    @error('lead.designation')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="email" class="form-label required">Contact
                                                        Person Email</label>
                                                    <input type="text" placeholder="Enter email"
                                                        class="form-control @error('lead.contact_email') is-invalid @enderror"
                                                        wire:model.live="lead.contact_email">
                                                    @error('lead.contact_email')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="companyName" class="form-label required">Company
                                                        Name</label>
                                                    <input type="text" placeholder="Enter company name"
                                                        class="form-control @error('lead.company_name') is-invalid @enderror"
                                                        wire:model.lazy="lead.company_name">
                                                    @error('lead.company_name')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="categoryId" class="form-label required">Business
                                                        Type</label>
                                                    <select
                                                        class="form-select @error('lead.category_id') is-invalid @enderror"
                                                        wire:model="lead.category_id">
                                                        <option value="">Select Business Type</option>
                                                        @if (!empty($categories))
                                                            @foreach ($categories as $category)
                                                                <option value={{ $category->id }}>
                                                                    {{ $category->name }}
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    @error('lead.category_id')
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
                                                        class="form-control @error('lead.user_name') is-invalid @enderror"
                                                        wire:model.live="lead.user_name">

                                                    @error('lead.user_name')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                    @enderror

                                                </div>
                                            </div>
                                            <div class="col-md-6">

                                                <label for="email" class="form-label required">Email</label>
                                                <input type="email" placeholder="Enter email"
                                                    class="form-control @error('lead.email') is-invalid @enderror"
                                                    wire:model="lead.email">
                                                @error('lead.email')
                                                    <div class="error text-danger">{{ $message }}</div>
                                                @enderror

                                            </div>

                                            <div class="col-md-6">

                                                <label for="contactNumber" class="form-label required">Phone
                                                    No.</label>
                                                <input type="text" placeholder="Enter company phone number."
                                                    class="form-control @error('lead.mobile_number') is-invalid @enderror"
                                                    wire:model="lead.mobile_number">
                                                @error('lead.mobile_number')
                                                    <div class="error text-danger">{{ $message }}</div>
                                                @enderror

                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="knownSource" class="form-label required">How did
                                                        you come
                                                        to
                                                        know</label>
                                                    @php
                                                        $knowSources = getKnownSourceData();
                                                    @endphp
                                                    <select
                                                        class="form-select @error('lead.known_source') is-invalid @enderror"
                                                        wire:model="lead.known_source">
                                                        <option>Select Known Source</option>
                                                        @foreach ($knowSources as $knowSourceKey => $knowSourceLabel)
                                                            <option value="{{ $knowSourceKey }}">
                                                                {{ $knowSourceLabel }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('lead.known_source')
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
                                                            class="form-select @error('lead.country') is-invalid @enderror"
                                                            wire:model.live="lead.country"
                                                            wire:change='clearLocationFields()'>
                                                            @if (!empty($countries))
                                                                @foreach ($countries as $country)
                                                                    <option value={{ $country }}>
                                                                        {{ $country }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                    @error('lead.country')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="pincode" class="form-label required">
                                                        {{ isset($lead['country']) && $lead['country'] == 'India' ? 'Pincode' : 'Zipcode' }}
                                                    </label>
                                                    <input type="text" id="pincode"
                                                        placeholder="Enter 
                                                        {{ isset($lead['country']) && $lead['country'] == 'India' ? 'Pincode' : 'Zipcode' }}
                                                         "
                                                        class="form-control @error('lead.pincode') is-invalid @enderror"
                                                        wire:model="lead.pincode" wire:blur='pincode()'>
                                                    @error('lead.pincode')
                                                        <div class="error text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div @if (isset($lead['country']) && $lead['country'] != 'India') style="display: none;" @endif>
                                                    <label for="city" class="form-label">City</label>
                                                    <div>
                                                        <input type="text" id="city" disabled
                                                            class="form-control @error('lead.city') is-invalid @enderror"
                                                            wire:model="lead.city">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div @if (isset($lead['country']) && $lead['country'] != 'India') style="display: none;" @endif>
                                                    <label for="state" class="form-label">State</label>
                                                    <div>
                                                        <input type="text" id="state" disabled
                                                            class="form-control @error('lead.state') is-invalid @enderror"
                                                            wire:model="lead.state">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="address" class="form-label required">Address</label>
                                                <textarea placeholder="Enter address" rows="3"
                                                    class="form-control @error('lead.address') is-invalid @enderror" wire:model="lead.address"></textarea>
                                                @error('lead.address')
                                                    <div class="error text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div>
                                                <label class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        wire:model.live="lead.news_letter"
                                                        style="border-color:rgb(134, 132, 132);">
                                                    <span class="form-check-label"> Sign up for Newsletters,
                                                        Industry
                                                        and Show updates</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" wire:click="resetFields()" class="text-danger me-2">Reset</a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" wire:click.prevent="editLeadDetails">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function() {
            console.log('kl');
            Livewire.on('confirmEditExhibitor', () => {
                if (confirm('Do you want to edit lead details?')) {
                    const modal = new bootstrap.Modal(document.getElementById('staticModal'));
                    modal.show();
                    console.log('emited');
                }
            });
        });
    </script>
@endpush
