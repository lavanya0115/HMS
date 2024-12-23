@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush
<div class="page-body">
    <div wire:ignore.self class="modal modal-blur fade" id="otherBillingAddress" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Other Billing Address</h5>
                    <button type="button" class="btn-close" aria-label="Close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="address_type" class="form-label">Address
                                    Type</label>
                                <input type="text"
                                    class="form-control @error('otherAddress.address_type') is-invalid @enderror"
                                    wire:model="otherAddress.address_type">
                                @error('otherAddress.address_type')
                                    <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="otheraddress_state" class="form-label">State</label>
                                <select class="form-select" wire:model="otherAddress.state"
                                    wire:change="changeState('other')">
                                    <option value="">Select State</option>
                                    @if ($lead['type'] == 'domestic')
                                        @foreach ($states as $state)
                                            <option value="{{ $state }}">{{ $state }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="otheraddress_city" class="form-label">City</label>
                                <select class="form-select" wire:model="otherAddress.city">
                                    <option value="">Select City</option>
                                    @foreach ($otherAddressCities as $city)
                                        <option value="{{ $city }}"
                                            {{ $city == $otherAddress['city'] ? 'selected' : '' }}>{{ $city }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="contact_person_name" class="form-label">Contact Person</label>
                                <input type="text" class="form-control"
                                    wire:model="otherContactPerson.contact_person">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="contactno" class="form-label">Contact
                                    No.</label>
                                <input type="text"
                                    class="form-control @error('otherContactPerson.contact_no') is-invalid @enderror"
                                    wire:model="otherContactPerson.contact_no">
                                @error('otherContactPerson.contact_no')
                                    <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="contact_email" class="form-label">Email</label>
                                <input type="text"
                                    class="form-control @error('otherContactPerson.email') is-invalid @enderror"
                                    wire:model="otherContactPerson.email">
                                @error('otherContactPerson.email')
                                    <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="contact_person_deg" class="form-label">Designation</label>
                                <input type="text" class="form-control" wire:model="otherContactPerson.designation">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="otheraddress_gst" class="form-label">GST No.</label>
                                <input type="text"
                                    class="form-control @error('otherAddress.gst') is-invalid @enderror"
                                    wire:model="otherAddress.gst">
                                @error('otherAddress.gst')
                                    <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="otheraddress_pan" class="form-label">PAN No.</label>
                                <input type="text"
                                    class="form-control @error('otherAddress.pan') is-invalid @enderror"
                                    wire:model="otherAddress.pan">
                                @error('otherAddress.pan')
                                    <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="otheraddress_landline" class="form-label">Landline
                                    No.</label>
                                <input type="text"
                                    class="form-control @error('otherAddress.landline_no') is-invalid @enderror"
                                    wire:model="otherAddress.landline_no">
                                @error('otherAddress.landline_no')
                                    <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-2">
                                <label for="street_address" class="form-label">Street Address</label>
                                <textarea placeholder="Enter address" rows="3" class="form-control" wire:model="otherAddress.address"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" wire:click="addOtherBillingAddress" class="btn btn-primary ms-auto">
                        Add
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="container xl">
        @include('includes.alerts')
        @if (
            $errors->has('primaryContact.contact_person') ||
                $errors->has('primaryContact.contact_no') ||
                $errors->has('primaryContact.email'))
            <div class="alert alert-danger">
                <ul>
                    @foreach (['primaryContact.contact_person', 'primaryContact.contact_no', 'primaryContact.email'] as $field)
                        @if ($errors->has($field))
                            <li>{{ $errors->first($field) }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif
        <h4>{{ isset($leadId) ? 'Edit Lead' : 'Create Lead' }}</h4>
        <form wire:submit={{ isset($leadId) ? 'updateLead' : 'createLead' }}>
            <div class="card">
                <div class="card-body row">
                    <div class="col-md-2">
                        <div class="mb-2">
                            <label for="lead_no" class="form-label">Lead No</label>
                            <input type="text" class="form-control" placeholder="Auto" wire:model="lead.lead_no"
                                disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-2">
                            <label for="lead_name" class="form-label required">Lead Name</label>
                            <input type="text" class="form-control @error('lead.name') is-invalid @enderror"
                                wire:model="lead.name" wire:change="checkLeadName">
                            @error('lead.name')
                                <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-2">
                            <label for="alias_name" class="form-label">Alias Name</label>
                            <input type="text" class="form-control" wire:model="lead.alias_name">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-2">
                            <label for="lead_type" class="form-label required">Lead Type</label>
                            <select class="form-select" wire:model="lead.type" wire:change="changeLeadType">
                                <option value="domestic">Domestic</option>
                                <option value="international">International</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-2">
                            <label for="lead_category" class="form-label required">Lead Category</label>
                            <select class="form-select" wire:model="lead.category">
                                <option value="direct">Direct</option>
                                <option value="agent">Agent</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <label for="lead_source" class="form-label required">Lead Source</label>
                            <select class="form-select @error('lead.source') is-invalid @enderror"
                                wire:model="lead.source">
                                <option value="">Select Source</option>
                                @foreach ($leadSources as $leadSource)
                                    <option value="{{ $leadSource->id }}">{{ $leadSource->name }}</option>
                                @endforeach
                            </select>
                            @error('lead.source')
                                <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <label for="country" class="form-label required">Country</label>
                            <div wire:ignore>
                                <select id="country"
                                    class="form-select @error('primaryAddress.country') is-invalid @enderror"
                                    wire:model="primaryAddress.country" wire:change="changeCountry"
                                    {{ $lead['type'] == 'domestic' ? 'disabled' : '' }}>
                                    <option value="">Select Country</option>
                                    @foreach (array_keys($this->countries) as $countryName)
                                        <option value="{{ $countryName }}">{{ $countryName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('primaryAddress.country')
                                <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-2">
                            <label for="currency" class="form-label">Currency</label>
                            <input type="text" class="form-control" wire:model="lead.currency" disabled>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-2">
                            <label for="dial_code" class="form-label">Dial Code</label>
                            <input type="text" class="form-control" wire:model="primaryAddress.dial_code"
                                disabled>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <label for="director_name" class="form-label">Director Name</label>
                            <input type="text" class="form-control" wire:model="lead.director_name">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-2">
                            <label for="director_mobile" class="form-label">Director Mobile
                                No</label>
                            <input type="text"
                                class="form-control @error('lead.director_mobile') is-invalid @enderror"
                                wire:model="lead.director_mobile">
                            @error('lead.director_mobile')
                                <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <label for="director_email" class="form-label">Director
                                Email</label>
                            <input type="text"
                                class="form-control @error('lead.director_email') is-invalid @enderror"
                                wire:model="lead.director_email">
                            @error('lead.director_email')
                                <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="mb-2">
                            <label for="rating" class="form-label">Rating</label>
                            <input type="number" id="rating" class="form-control" wire:model="lead.rating"
                                max="10" placeholder="1-10">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <label for="expo_participation" class="form-label">Other Expo Participation</label>
                            <textarea rows="3" class="form-control" wire:model="lead.expo_participation"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs nav-fill" data-bs-toggle="tabs">
                        <li class="nav-item">
                            <a href="#tab-primary-address" class="nav-link active" data-bs-toggle="tab">
                                Primary Address
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#tab-primary-contact" class="nav-link" data-bs-toggle="tab">
                                Primary Contact
                            </a>
                        </li>
                        @if (!empty($leadId))
                            <li class="nav-item">
                                <a href="#tab-other-address" class="nav-link" data-bs-toggle="tab">
                                    Other Billing Address
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#tab-product-category" class="nav-link" data-bs-toggle="tab">
                                    Products
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane active show" id="tab-primary-address">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <label for="state"
                                            class="form-label {{ $lead['type'] == 'domestic' ? 'required' : '' }}">State</label>
                                        <select
                                            class="form-select @error('primaryAddress.state') is-invalid @enderror"
                                            wire:model="primaryAddress.state" wire:change="changeState('primary')">
                                            <option value="">Select State</option>
                                            @if ($lead['type'] == 'domestic')
                                                @foreach ($states as $state)
                                                    <option value="{{ $state }}">{{ $state }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('primaryAddress.state')
                                            <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <label for="city" class="form-label">City</label>
                                        <select class="form-select" wire:model="primaryAddress.city"
                                            wire:change="changeCity()">
                                            <option value="">Select City</option>
                                            @foreach ($cities as $city)
                                                <option value="{{ $city }}">{{ $city }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <label for="area" class="form-label">Area</label>
                                        <div wire:ignore>
                                            <select id="area" class="form-select"
                                                wire:model="primaryAddress.area" wire:change="changeArea()">
                                                <option value="">Select Area</option>
                                                @foreach ($areas as $area)
                                                    <option value="{{ $area }}">{{ $area }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <label for="pincode" class="form-label">Pincode</label>
                                        <input type="text" class="form-control"
                                            wire:model="primaryAddress.pincode" disabled>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea placeholder="Enter address" rows="3" class="form-control" wire:model="primaryAddress.address"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <label for="lead_gst" class="form-label">GSTIN</label>
                                        <input type="text"
                                            class="form-control @error('primaryAddress.gst') is-invalid @enderror"
                                            wire:model="primaryAddress.gst">
                                        @error('primaryAddress.gst')
                                            <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <label for="lead_pan" class="form-label">PAN</label>
                                        <input type="text"
                                            class="form-control @error('primaryAddress.pan') is-invalid @enderror"
                                            wire:model="primaryAddress.pan">
                                        @error('primaryAddress.pan')
                                            <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <label for="landline_no" class="form-label">Landline
                                            No.</label>
                                        <input type="text"
                                            class="form-control @error('primaryAddress.landline_no') is-invalid @enderror"
                                            wire:model="primaryAddress.landline_no">
                                        @error('primaryAddress.landline_no')
                                            <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-primary-contact">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <label for="contact_person" class="form-label required">Contact
                                            Person</label>
                                        <input type="text"
                                            class="form-control @error('primaryContact.contact_person') is-invalid @enderror"
                                            wire:model="primaryContact.contact_person">
                                        @error('primaryContact.contact_person')
                                            <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <label for="contact_no" class="form-label required">Contact
                                            No.</label>
                                        <input type="text"
                                            class="form-control @error('primaryContact.contact_no') is-invalid @enderror"
                                            wire:model="primaryContact.contact_no">
                                        @error('primaryContact.contact_no')
                                            <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <label for="email" class="form-label required">Email</label>
                                        <input type="text"
                                            class="form-control @error('primaryContact.email') is-invalid @enderror"
                                            wire:model="primaryContact.email">
                                        @error('primaryContact.email')
                                            <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <label for="designation" class="form-label">Designation</label>
                                        <input type="text" class="form-control"
                                            wire:model="primaryContact.designation">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (!empty($leadId))
                            <div class="tab-pane" id="tab-other-address">
                                <a href="#" class="btn mb-2 pe-2" title="Add Other Billing Address"
                                    data-toggle="tooltip" data-placement="top" data-bs-toggle="modal"
                                    data-bs-target="#otherBillingAddress">
                                    @include('icons.plus')
                                </a>
                                <div class="table-responsive">
                                    <table class="table table-vcenter card-table">
                                        <thead>
                                            <tr>
                                                <th class="w-1"></th>
                                                <th class="w-1"></th>
                                                <th>Address Type</th>
                                                <th>State</th>
                                                <th>City</th>
                                                <th>Contact Person</th>
                                                <th>Contact No.</th>
                                                <th>Email</th>
                                                <th>GST</th>
                                                <th>PAN</th>
                                                <th>Landline No.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($otherBillingAddress) && count($otherBillingAddress) > 0)
                                                @foreach ($otherBillingAddress as $index => $address)
                                                    <tr wire:key='item-{{ $index }}'>
                                                        <td><a href="#" title="Remove" data-toggle="tooltip"
                                                                data-placement="top" class="text-danger"
                                                                wire:confirm="Are you sure you want to remove this address?"
                                                                wire:click="removeAddress({{ $index }})">@include('icons.square-x')</a>
                                                        </td>
                                                        <td><a href="#" title="Edit" data-toggle="tooltip"
                                                                data-placement="top" data-bs-toggle="modal"
                                                                data-bs-target="#otherBillingAddress"
                                                                wire:click="assignDataToModal({{ $index }})">@include('icons.edit')</a>
                                                        </td>
                                                        <td>{{ $address['address_type'] }}</td>
                                                        <td>{{ $address['state'] }}</td>
                                                        <td>{{ $address['city'] }}</td>
                                                        <td>{{ $address['contact_person'] }}</td>
                                                        <td>{{ $address['contact_no'] }}</td>
                                                        <td>{{ $address['email'] }}</td>
                                                        <td>{{ $address['gst'] }}</td>
                                                        <td>{{ $address['pan'] }}</td>
                                                        <td>{{ $address['landline_no'] }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab-product-category">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3" id="ts">
                                            <label class="form-label">Product Category</label>
                                            <div wire:ignore>
                                                <select id="categories" class="form-select"
                                                    wire:model="productDetails.categories"
                                                    placeholder="Select Product Category" multiple>
                                                    @foreach ($productCategories as $categoryId => $categoryName)
                                                        <option value={{ $categoryId }}>{{ $categoryName }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3" id="ts">
                                            <label class="form-label">Products</label>
                                            <div wire:ignore>
                                                <select id="products" class="form-select"
                                                    wire:model="productDetails.products" placeholder="Select Products"
                                                    multiple>
                                                    @foreach ($products as $productId => $productName)
                                                        <option value={{ $productId }}>{{ $productName }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-end mt-3">
                <a href={{ route('leads.summary') }} class="btn btn-secondary me-1">
                    Back </a>
                <button type="submit" class="btn btn-primary">{{ isset($leadId) ? 'Update' : 'Create' }}</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', function() {
            const productsElement = document.querySelector('#products');
            if (productsElement) {
                var products = new TomSelect('#products', {
                    plugins: ['dropdown_input', 'remove_button'],
                    create: true,
                    createOnBlur: true,
                });
            }
            const categoriesElement = document.querySelector('#categories');
            if (categoriesElement) {
                var productCategories = new TomSelect('#categories', {
                    plugins: ['dropdown_input', 'remove_button'],
                    create: true,
                    createOnBlur: true,
                });
            }
            Livewire.on('closeModal', function() {
                $('#otherBillingAddress').modal('hide');
            });
            Livewire.on('showNoChangesMessage', function() {
                alert('No changes made');
            });
            var country = new TomSelect('#country', {
                plugins: ['dropdown_input', 'remove_button'],
            });
            var areaField = new TomSelect('#area', {
                plugins: ['dropdown_input', 'remove_button'],
                valueField: 'title',
                labelField: 'title',
                searchField: 'title',
                create: false,
                persist: false,
            });

            Livewire.on('currentDistrictAreas', function(data) {
                let areas = data[0];
                const transformedAreas = Object.entries(areas).map(([key, value]) => ({
                    title: value
                }));

                areaField.setValue('');
                areaField.clearOptions();
                areaField.addOption(transformedAreas);
            });
            Livewire.on('enableTomSelect', function(data) {
                let value = data[0];
                if (value.leadType == 'international') {
                    country.enable();
                } else {
                    country.disable();
                }

                country.setValue(value.country);
            });

        });
    </script>
@endpush
