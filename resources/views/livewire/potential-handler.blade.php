@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush
<div class="page-header">
    <div class="container">
        <sapn>
            @include('includes.alerts')
        </sapn>
        <div class="d-flex justify-content-between">
            <div>
                <h4 class="text">{{ isset($potentialId) ? 'Edit Potential' : 'New Potential' }}</h4>
            </div>
            <div class="d-flex">
                @if (isset($potentialId))
                    <div class="mb-2">
                        <a class="btn btn-warning"
                            href="{{ route('potential-follow-up', ['potentialId' => $potentialId]) }}"> Follow Up </a>
                    </div>
                @endif

                <div class="mb-2 ms-2">
                    <a class="btn btn-primary" href="{{ route('potential-create') }}"> New Potential </a>
                </div>

                <div class="mb-2 ms-2">
                    <a class="btn btn-secondary" href="{{ route('potential-summary') }}"> Back To Summary </a>
                </div>
            </div>
        </div>

        <div class="card">

            <div class="card-body">
                <div class="row row-cards">

                    <div class="col-md-3">
                        <div class="mb-2" id="ts">
                            <div wire:ignore>
                                <label class="form-label required tomselect" for="tomselect-event">Event</label>
                                <select id="tomselect-event" type="select" @class([
                                    'form-control',
                                    'is-invalid' => $errors->has('event_id') ? true : false,
                                ])
                                    wire:model.live="event_id">
                                    @if (!empty($events))
                                        @foreach ($events as $event)
                                            <option value="{{ $event->id }}">
                                                {{ $event->event_description ?? $event->title }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @error('event_id')
                                <div class="text-danger text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">

                        <div class="mb-2" id="ts">
                            <div wire:ignore>
                                <label class="form-label required tomselect" for="tomselect-lead">Lead Name</label>
                                <select id="tomselect-lead" type="select"
                                    class="form-control @error('lead_id') is-invalid @enderror"
                                    wire:model.live="lead_id" wire:change="filLeadData">
                                    <option value="">Select Lead</option>
                                    @if (isset($leads))
                                        @foreach ($leads as $lead)
                                            <option value="{{ $lead->id }}">{{ $lead->name ?? $lead->alias_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @error('lead_id')
                                <div class="text-danger text">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="col-md-3">
                        <label class="form-label " for="lead-id">Lead Id</label>
                        <input id="lead-id" type="text" @class(['form-control']) wire:model="lead_unique_id"
                            disabled>

                    </div>

                    <div class="col-md-3">
                        <label class="form-label " for="code">Lead Category</label>
                        <input type="text" class="form-control" id="code" name="lead_category"
                            wire:model.live="lead_category" disabled>
                        @error('lead_category')
                            <div class="text-danger text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <div class="mb-2" id="ts">
                            <div wire:ignore>
                                <label class="form-label required tomselect" for="tomselect-sales-person">Assign
                                    To</label>
                                <select id="tomselect-sales-person" type="select" @class([
                                    'form-control',
                                    'is-invalid' => $errors->has('sales_person_id') ? true : false,
                                ])
                                    wire:model.live="sales_person_id">
                                    <option value="">Select Sales Person</option>
                                    @if (!empty($salesPersons))
                                        @foreach ($salesPersons as $salesPerson)
                                            <option value="{{ $salesPerson->id }}">{{ $salesPerson->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @error('sales_person_id')
                                <div class="text-danger text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label " for="contact">Primary Contact</label>
                        <input type="text" class="form-control" id="contact" disabled wire:model="contact_mode">
                    </div>

                    <div class=" col-md-6">
                        <label class="form-label " for="address">Street Address</label>
                        <input id="address" type="text" @class([
                            'form-control',
                            'is-invalid' => $errors->has('address') ? true : false,
                        ]) wire:model="address" disabled>
                        @error('address')
                            <div class="text-danger text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mt-3">

                    <div class=" d-flex">
                        <label class="text fw-bold mb-3">Stall Details</label>

                        @if (($lead_category === 'direct' && count($stallDetails) == 0) || $lead_category === 'agent')
                            <div class="ms-2">
                                <a title="Add" data-bs-toggle="modal" data-bs-target="#staticModal">
                                    {{-- <a title="Add" id="add_row" class="add_row me-2"> --}}
                                    <span class="text-success" style="cursor: pointer;">
                                        @include('icons.plus')
                                    </span>
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="table-responsive" id="part_table">
                        <table id="Table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Stall No</th>
                                    <th>Stall Type</th>
                                    <th>Squre Meter</th>
                                    <th>Rate / Sq.mtr</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Company Name</th>
                                    @if ($potentialId)
                                        <th>Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($stallDetails) && !empty($stallDetails))
                                    @foreach ($stallDetails as $index => $stallDetail)
                                        <tr wire:key={{ $index }}>
                                            <td>
                                                <span class="text">{{ $index + $stallDetails->firstItem() }}</span>
                                            </td>

                                            <td>
                                                {{ $stallDetail->stall->stall_number ?? '--' }}
                                            </td>

                                            <td>
                                                {{ $stallDetail->stall_type ?? '--' }}

                                            </td>

                                            <td>
                                                {{ $stallDetail->_meta['stall_sqr_mtr'] ?? '--' }}
                                            </td>

                                            <td>
                                                {{ $stallDetail->_meta['stall_rate'] ?? '--' }}
                                            </td>

                                            <td>
                                                {{ '₹ ' . $stallDetail->amount ?? '--' }}
                                            </td>

                                            <td>
                                                {{ $stallDetail->stall_status ?? '--' }}
                                            </td>

                                            <td>
                                                {{ $stallDetail->agent->name ?? '--' }}
                                            </td>

                                            @if ($potentialId)
                                                <td>
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <div>
                                                            <a title="Add" data-bs-toggle="modal"
                                                                data-bs-target="#staticModal">
                                                                <span class="text-success" style="cursor: pointer;">
                                                                    @include('icons.edit')
                                                                </span>
                                                            </a>
                                                        </div>
                                                        <div class="">
                                                            <a title="Remove" id="remove_row" class="remove_row">
                                                                <span class="text-danger" style="cursor: pointer;">
                                                                    @include('icons.trash')
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            @endif

                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {{-- <div class="card-footer text-end">
                    @if ($potentialId)
                        <a href={{ route('potential-create') }} class="text-danger me-2"> Cancel </a>
                    @else
                        <a href=# wire:click.prevent ="resetFields" class="text-danger me-2"> Reset </a>
                    @endif
                    <button type ="submit"
                        class="btn btn-primary">{{ isset($potentialId) ? 'Update Potential' : 'Create Potential' }}</button>
                </div> --}}

        </div>

    </div>

    {{-- Stall Modal --}}

    <div wire:ignore.self class="modal modal-blur fade" id="staticModal" role="dialog" aria-hidden="true"
        data-bs-backdrop='static' tabindex="-1" aria-labelledby="staticModalLabel">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticModalLabel">Stall</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="potentialForm" wire:submit.prevent="{{ isset($potentialId) ? 'update' : 'create' }}">
                        @csrf
                        <div class="row">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Stall Selection -->
                            <div class="col-md-3 mb-3">
                                <label for="tomselect-event" class="form-label required">Stall Number</label>
                                <div>
                                    <select id="tomselect-event" class="form-control" wire:model.live="stall_id">
                                        <option value="">Select Stall</option>
                                        @if (!empty($stalls))
                                            @foreach ($stalls as $stall)
                                                <option value="{{ $stall->id }}"
                                                    wire:key="stall-{{ $stall->id }}">{{ $stall->stall_number }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @error('stall_id')
                                    <div class="text-danger text">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Stall Type Selection -->
                            <div class="col-md-3 mb-3">
                                <label for="stall_type" class="form-label required">Stall Type</label>
                                <div wire:ignore>
                                    <select id="stall_type" class="form-control" wire:model="stall_type">
                                        <option value="">Select Type</option>
                                        <option value="shell">Shell</option>
                                        <option value="bare">Bare</option>
                                    </select>
                                </div>
                                @error('stall_type')
                                    <div class="text-danger text">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Square Meters -->
                            <div class="col-md-3 mb-3">
                                <label for="sq_mtr" class="form-label">Square Meters</label>
                                <input type="text" class="form-control" id="sq_mtr" wire:model="sq_mtr"
                                    disabled />
                            </div>

                            <!-- Rate -->
                            <div class="col-md-3 mb-3">
                                <label for="rate" class="form-label required">Rate</label>
                                <input type="number" class="form-control" id="rate" wire:model.live="rate" />
                                @error('rate')
                                    <div class="text-danger text">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div class="col-md-3 mb-3">
                                <label for="stall_amount" class="form-label">Amount</label>
                                <input type="text" class="form-control" id="stall_amount"
                                    wire:model="stall_amount" disabled />
                                @error('stall_amount')
                                    <div class="text-danger text">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Stall Status -->
                            <div class="col-md-3 mb-3">
                                <label for="stall-status" class="form-label required">Status</label>
                                <div wire:ignore>
                                    <select id="stall-status" class="form-control" wire:model="stall_status">
                                        <option value="">Select Status</option>
                                        @if (!empty($stallStatus))
                                            @foreach ($stallStatus as $key => $status)
                                                <option value="{{ $key }}">{{ $status }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @error('stall_status')
                                    <div class="text-danger text">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Company Name -->
                            <div class="col-md-6 mb-3">
                                <div wire:ignore.self>
                                    <label for="tomselect-company_name" class="form-label">Company Name</label>
                                    <select id="tomselect-company_name" type="select" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('stall_agent_id') ? true : false,
                                    ])
                                        wire:model.live="stall_agent_id">
                                        <option value="">Select Company</option>"
                                        @if ($this->lead_category === 'agent')
                                            @foreach ($leads as $lead)
                                                <option value="{{ $lead->id }}"
                                                    {{ $stall_agent_id == $lead->id ? 'selected' : '' }}>
                                                    {{ $lead->name ?? $lead->alias_name }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="{{ $stall_agent_id }}">{{ $company_name }}</option>
                                        @endif
                                    </select>
                                </div>
                                @error('stall_agent_id')
                                    <div class="text-danger text">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <!-- Form Footer -->
                        <div class="modal-footer">

                            <button type="submit" class="btn btn-primary">
                                {{ isset($potentialId) ? 'Update' : 'Add' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', function() {
            var events = new TomSelect('#tomselect-event', {
                plugins: ['dropdown_input'],
            });

            var sales_person = new TomSelect('#tomselect-sales-person', {
                plugins: ['dropdown_input'],
            });
            var lead = new TomSelect('#tomselect-lead', {
                plugins: ['dropdown_input'],
            });
            // var lead = new TomSelect('#tomselect-company_name', {
            //     plugins: ['dropdown_input'],
            // });
            Livewire.hook('element.init', ({
                el,
                component
            }) => {
                new TomSelect('#tomselect-company_name', {
                    allowEmptyOption: true,
                });
            });
        });
    </script>
@endpush
