<div class="page-body">
    <div wire:ignore.self class="modal modal-blur fade" id="stall_allocate" tabindex="-1" role="dialog" aria-hidden="true"
        data-bs-backdrop='static'>
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Allocate Stall</h5>
                    <button type="button" class="btn-close" wire:click="clearError" aria-label="Close"></button>
                </div>
                <form wire:submit="updateStallDetail">
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="space" class="form-label">Space</label>
                                <select id="space" class="form-select" wire:model="stall_space">
                                    <option value="">Select Space</option>
                                    <option value="Shell Space">Shell Space</option>
                                    <option value="Bare Space">Bare Space</option>
                                </select>
                                {{-- @error('stall_space')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror --}}
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="square_feet" class="form-label">Square Feet</label>
                                <input type="text" id="square_feet" wire:model="square_space" class="form-control"
                                    placeholder="Enter square feet">
                                {{-- @error('square_space')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror --}}
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="stall_no" class="form-label required">Stall No.</label>
                                <input type="text" id="stall_no" wire:model="stall_no" class="form-control"
                                    placeholder="Enter stall number">
                                @error('stall_no')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn me-auto" wire:click="clearError">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="container">
        @include('includes.alerts')
        <div>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="d-flex gap-2">
                        <h3>List all Exhibitors</h3>
                        <span wire:click="toggleFilter" style="cursor:pointer;" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Filter">@include('icons.filter-search')</span>
                    </div>
                    <div class="subheader">Exhibitors Count (Total-{{ $exhibitorsTotalCount }})</div>
                </div>
                <div>
                    @if (!$requestEventId)
                        <button class="btn" wire:click="filterCurrentEventRecords">Current Event<span
                                class="badge bg-yellow text-yellow-fg ms-2">{{ $eventExhibitorsCount }}</span></button>
                        <button class="btn {{ $eventId !== $currentEventId ? 'd-none' : '' }}"
                            wire:click="resetEventId">Reset</button>
                    @endif
                </div>
            </div>
            <div class="pb-3 {{ !$showFilter ? 'd-none' : '' }}">
                <div class="card">
                    <div class="card-body">
                        <div class="row col-md-12">
                            <div class="col-md-4">
                                <select class="form-select" wire:model.live.debounce.200ms="eventId"
                                    {{ isset($requestEventId) ? 'disabled' : '' }}>
                                    <option value="">Select Event</option>
                                    @foreach ($eventsList as $eventID => $eventTitle)
                                        <option value="{{ $eventID }}">{{ $eventTitle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control " placeholder="Select a date" id="daterange"
                                    name="daterange">
                            </div>
                            <div class="col-md-4">
                                <div wire:ignore>
                                    <select id="products"
                                        class="form-select @error('exhibitor.products') is-invalid @enderror"
                                        wire:model.live.debounce.200ms="productSearch" placeholder="Select Products">
                                        <option value="">Select Products</option>
                                        @foreach ($products as $productId => $productName)
                                            <option value={{ $productId }}>{{ substr($productName, 0, 50) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 pt-3">
                                <div class="input-group input-group-flat">
                                    <input type="text" placeholder="Search Name, Email, Mobile No..."
                                        class="form-control" wire:model.live.debounce.200ms="search">
                                    <span class="input-group-text">
                                        <a href="#" wire:click="$set('search', '')" class="link-secondary"
                                            title="Clear search" data-bs-toggle="tooltip">
                                            @include('icons.close')
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="float-end">
                            <a href="#" id="filterBtn" class="btn btn-primary">Filter</a>
                            <a href="{{ isset($requestEventId) ? route('exhibitor.summary', ['eventId' => $requestEventId]) : route('exhibitor.summary') }}"
                                class="btn btn-secondary ms-2">Reset</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-end">
                @if (!isset($requestEventId))
                    @if ($showToggle == true)
                        <div class="d-flex gap-1 mb-2 pt-2">
                            <select class="form-select" wire:model.live="event_id">
                                <option value="">Select Event</option>
                                @foreach ($events as $eventID => $eventTitle)
                                    <option value="{{ $eventID }}">{{ $eventTitle }}</option>
                                @endforeach
                            </select>
                            <button class="btn text-white" style="background-color: #f1a922;"
                                wire:click="selectedExhibitorsId"
                                {{ empty($event_id) ? 'disabled' : '' }}>Add</button>
                        </div>
                    @endif
                    @can('Transfer Exhibitor')
                        <a href="#" class="mb-2 text-decoration-none pe-1 pt-2" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Move to Another Event"
                            wire:click="toggleEvents">@include('icons.cloud-upload')</a>
                    @endcan
                @endif
                @can('Export Exhibitor')
                    <div class="col-auto ms-2">
                        <button class="btn w-10" wire:click="exportToExcel" wire:loading.attr="disabled"
                            {{ isset($exhibitors) && count($exhibitors) == 0 ? 'disabled' : '' }}>
                            @include('icons.table-export')
                            <span wire:loading wire:target="exportToExcel">Exporting...</span>
                            <span wire:loading.remove wire:target="exportToExcel">Export to Excel</span>
                        </button>
                    </div>
                @endcan
            </div>
            <div class="table-responsive exhibitor-table" wire:ignore>
                <table class="table card-table table-vcenter text-nowrap table-striped datatable">
                    <thead>
                        <tr>
                            @if (!isset($requestEventId))
                                <th>
                                    <div>
                                        <label class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                wire:model.live="selectAll" style="border-color:rgb(134, 132, 132);">
                                        </label>
                                    </div>
                                </th>
                            @endif
                            <th>#</th>
                            <th>Score</th>

                            <th class="w-1"></th>
                            <th class="w-1"></th>
                            <th class="w-1"></th>
                            @if (isset($requestEventId))
                                <th>Stall No.</th>
                            @endif
                            <th>
                                Company
                                <span style="cursor:pointer;" wire:click="sortBy('exhibitor', 'name','asc')">
                                    @include('icons.arrow-narrow-up')
                                </span>
                                <span style="cursor:pointer;margin-left:-10px;"
                                    wire:click="sortBy('exhibitor', 'name','desc')">
                                    @include('icons.arrow-narrow-down')
                                </span>
                            </th>

                            <th>
                                Email
                                <span style="cursor:pointer;" wire:click="sortBy('exhibitor', 'email','asc')">
                                    @include('icons.arrow-narrow-up')
                                </span>
                                <span style="cursor:pointer;margin-left:-10px;"
                                    wire:click="sortBy('exhibitor', 'email','desc')">
                                    @include('icons.arrow-narrow-down')
                                </span>
                            </th>
                            <th style="padding-top: 12px">Phone No.</th>
                            <th>
                                Address
                                <span style="cursor:pointer;" wire:click="sortBy('address', 'city','asc')">
                                    @include('icons.arrow-narrow-up')
                                </span>
                                <span style="cursor:pointer;margin-left:-10px;"
                                    wire:click="sortBy('address', 'city','desc')">
                                    @include('icons.arrow-narrow-down')
                                </span>
                            </th>
                            <th>
                                Contact Person
                                <span style="cursor:pointer;" wire:click="sortBy('contact_person', 'name','asc')">
                                    @include('icons.arrow-narrow-up')
                                </span>
                                <span style="cursor:pointer;margin-left:-10px;"
                                    wire:click="sortBy('contact_person', 'name','desc')">
                                    @include('icons.arrow-narrow-down')
                                </span>
                            </th>
                            <th style="padding-top: 12px">Contact No.</th>
                            <th style="padding-top: 12px">Products</th>
                            @if (!empty($eventId))
                                <th style="padding-top: 12px">Is Sponsor</th>
                            @endif
                            <th style="padding-top: 12px">Source</th>
                            <th style="padding-top: 12px">Known Source</th>
                            <th style="padding-top: 12px">Timestamp</th>
                            <th style="padding-top: 12px">Exhibitor Participated Events</th>
                            <th>
                                No of Appointments
                                <span style="cursor:pointer;"
                                    wire:click="sortBy('appointments', 'appointments_count','asc')">
                                    @include('icons.arrow-narrow-up')
                                </span>
                                <span style="cursor:pointer;margin-left:-10px;"
                                    wire:click="sortBy('appointments', 'appointments_count','desc')">
                                    @include('icons.arrow-narrow-down')
                                </span>
                            </th>

                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($exhibitors) && count($exhibitors) > 0)
                            @foreach ($exhibitors as $exhibitorsIndex => $exhibitor)
                                @php
                                    $address = $exhibitor->address ?? $exhibitor->branchPrimaryAddress?->address;
                                    $city = $address->city ?? null;
                                    $pincode = $address->pincode ?? '_';
                                    $state = $address->state ?? '_';
                                    $country = $address->country ?? '--';
                                    $fullAddress = $address->address ?? '--';
                                @endphp
                                <tr wire:key="{{ $exhibitor->id }}" style="cursor: pointer;"
                                    wire:click.prevent="gotoProfile('{{ $exhibitor->id }}', 'exhibitor')">
                                    @if (!isset($requestEventId))
                                        <td>
                                            <div>
                                                <label class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        onclick="event.stopPropagation();"
                                                        wire:model="selectedExhibitors" value="{{ $exhibitor->id }}"
                                                        style="border-color:rgb(134, 132, 132);">
                                                </label>
                                            </div>
                                        </td>
                                    @endif
                                    {{-- @dd() --}}
                                    <td>
                                        {{ $exhibitorsIndex + $exhibitors->firstItem() }}
                                    </td>
                                    @php
                                        $completionPercentage = $exhibitor->getProfileCompletionPercentage();
                                    @endphp
                                    <td>
                                        <span @class([
                                            'badge me-1',
                                            'bg-green-lt' => $completionPercentage >= 70,
                                            'bg-yellow-lt' => $completionPercentage >= 50 && $completionPercentage < 70,
                                            'bg-red-lt' => $completionPercentage < 50,
                                        ])>
                                            {{ $completionPercentage }} %
                                        </span>

                                    </td>
                                    @php
                                        $previousEvents = getPreviousEvents();
                                        $isPreviousEvent = in_array(
                                            $requestEventId,
                                            $previousEvents->pluck('id')->toArray(),
                                        );
                                    @endphp
                                    <td>
                                        @can('Stall Allocation')
                                            @if (isset($requestEventId) && !$isPreviousEvent)
                                                <a href="#" wire:click="getExhibitorId({{ $exhibitor->id }})"
                                                    onclick="event.stopPropagation();" title="Allocate Stall"
                                                    data-toggle="tooltip" data-placement="top" data-bs-toggle="modal"
                                                    data-bs-target="#stall_allocate">
                                                    @include('icons.aspect-ratio')
                                                </a>
                                            @endif
                                        @endcan
                                    </td>
                                    <td>
                                        @can('Update Exhibitor')
                                            @if (!$isPreviousEvent)
                                                <a href="{{ route('exhibitor.edit', ['eventId' => $requestEventId, 'exhibitorId' => $exhibitor->id]) }}"
                                                    onclick="event.stopPropagation();" title="Edit"
                                                    data-toggle="tooltip" data-placement="top">
                                                    @include('icons.edit')
                                                </a>
                                            @endif
                                        @endcan
                                    </td>
                                    <td>
                                        @can('Delete Exhibitor')
                                            @if (isset($requestEventId))
                                                <a href="#" onclick="event.stopPropagation();"
                                                    wire:click.prevent="deleteExhibitor({{ $exhibitor->id }})"
                                                    wire:confirm="Are you sure you want to delete this Exhibitor?"
                                                    class="text-danger" title="Delete" data-toggle="tooltip"
                                                    data-placement="top">
                                                    @include('icons.trash')
                                                </a>
                                            @endif
                                        @endcan
                                    </td>
                                    @if (isset($requestEventId))
                                        @php
                                            $eventExhibior = $exhibitor->eventExhibitors
                                                ->where('event_id', $eventId)
                                                ->first();
                                        @endphp
                                        <td class="text-left fs-5">
                                            @if (!empty($eventExhibior->_meta['stall_space']))
                                                {{ $eventExhibior->_meta['stall_space'] ?? 'NA' }}/
                                            @endif
                                            @if (!empty($eventExhibior->_meta['square_space']))
                                                {{ $eventExhibior->_meta['square_space'] ?? 'NA' }}/
                                            @endif
                                            @if (!empty($eventExhibior->stall_no ?? ''))
                                                {{ $eventExhibior->stall_no ?? 'NA' }}
                                            @endif
                                        </td>
                                    @endif
                                    <td class="text-left small lh-base">
                                        <a href="{{ route('exhibitor.edit', ['eventId' => $requestEventId, 'exhibitorId' => $exhibitor->id]) }}"
                                            class="text-muted text-decoration-none">
                                            {{ $exhibitor->name }}
                                        </a>
                                    </td>
                                    <td class="text-left small lh-base text-wrap">{{ $exhibitor->email }}</td>
                                    <td class="text-left small lh-base text-wrap">{{ $exhibitor->mobile_number }}</td>
                                    <td class="text-left small lh-base" data-bs-toggle="tooltip"
                                        data-bs-placement="left" data-bs-html="true"
                                        title='
                                                @if ($address) @if ($city)
                                                        Pincode: {{ $pincode }},
                                                        City: {{ $city }},
                                                        State: {{ $state }},
                                                        Country: {{ $country }},
                                                        Address: {{ $fullAddress }}
                                                    @else
                                                        Pincode: {{ $pincode }},
                                                        Country: {{ $country }},
                                                        Address: {{ $fullAddress }} @endif
                                                @endif 
                                        '>
                                        {{ $city ?? $country }}
                                    </td>
                            
                                    <td class="text-left @if (strlen($exhibitor->exhibitorContact->name ?? '_') > 25) text-wrap @endif">
                                        <strong
                                            class="small lh-base">{{ $exhibitor->exhibitorContact->salutation ?? '_' }}.{{ $exhibitor->exhibitorContact->name ?? '_' }}</strong><br>
                                        <small>{{ $exhibitor->exhibitorContact?->designation ?? '_' }}</small>
                                    </td>
                                    <td class="text-left small lh-base">
                                        {{ $exhibitor->exhibitorContact->contact_number ?? '_' }}
                                    </td>
                                    <td class="fs-5 small lh-base">
                                        @if (!empty($eventId))
                                            @php
                                                $exhibitorData = $exhibitor
                                                    ->eventExhibitors()
                                                    ->where('event_id', $eventId)
                                                    ->first();
                                                $productNames = $exhibitorData
                                                    ? explode(',', $exhibitorData->getProductNames())
                                                    : [];
                                                $filteredProducts = array_filter($productNames, function ($value) {
                                                    return !is_null($value) && $value !== '';
                                                });
                                                $products = $productNames ? collect($filteredProducts) : collect();
                                                $productCount = count($products);
                                            @endphp

                                            <div class="text-capitalize">
                                                {{ $productCount > 0 ? implode(', ', $products->take(2)->all()) : 'No Products' }}
                                                @if ($productCount > 2)
                                                    <a href="#" data-bs-toggle="tooltip"
                                                        title="{{ implode(', ', $products->slice(2)->all()) }}"
                                                        class="fs-5">
                                                        <br>+{{ $productCount - 2 }} more
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-capitalize">
                                                @php
                                                    $overallProducts = [];
                                                @endphp
                                                @foreach ($exhibitor->exhibitorProducts as $exhibitorProduct)
                                                    @php
                                                        $product = $exhibitorProduct->product;
                                                        if ($product) {
                                                            $productName = $product->name;
                                                            $overallProducts[] = $productName;
                                                        }
                                                    @endphp
                                                @endforeach
                                                @if (count($overallProducts) > 2)
                                                    {{ implode(', ', collect($overallProducts)->take(2)->all()) }}
                                                    <a href="#" data-bs-toggle="tooltip"
                                                        title="{{ implode(', ', collect($overallProducts)->slice(2)->all()) }}"
                                                        class="fs-5">
                                                        <br>+{{ count($overallProducts) - 2 }} more
                                                    </a>
                                                @else
                                                    {{ implode(', ', collect($overallProducts)->all()) }}
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    @if (!empty($eventId))
                                        <td class="text-left small lh-base">
                                            @php
                                                $eventExhibitor = $exhibitor->eventExhibitors
                                                    ?->where('event_id', $eventId)
                                                    ->first();
                                                $isSponsor = $eventExhibitor ? $eventExhibitor->is_sponsorer : 0;
                                                $sponsor = $isSponsor == 1 ? 'Sponsor' : '';
                                            @endphp
                                            {{ $sponsor }}
                                        </td>
                                    @endif
                                    <td class="text-left small lh-base">
                                        @if (!empty($eventId) && $exhibitor->eventExhibitors?->where('event_id', $eventId)->isNotEmpty())
                                            {{ $exhibitor->eventExhibitors?->where('event_id', $eventId)->first()->registration_type ?? '_' }}
                                        @elseif (empty($eventId))
                                            {{ $exhibitor->registration_type }}
                                        @endif
                                    </td>
                                    <td class="text-left small lh-base">
                                        {{ $exhibitor->known_source }}
                                    </td>
                                    <td class="text-left small lh-base">
                                        @if (!empty($eventId) && $exhibitor->eventExhibitors?->where('event_id', $eventId)->isNotEmpty())
                                            {{ $exhibitor->eventExhibitors?->where('event_id', $eventId)->first()->created_at->format('d-m-Y H:i:s') ?? '_' }}
                                        @elseif (empty($eventId))
                                            {{ $exhibitor->created_at->format('d-m-Y H:i:s') ?? '_' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($exhibitor->eventExhibitors->isNotEmpty())
                                            <ul>
                                                @foreach ($exhibitor->eventExhibitors as $eventExhibitor)
                                                    <li>{{ $eventExhibitor->event->title }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            No Events
                                        @endif
                                    </td>

                                    <td>
                                        <div class="text-capitalize small lh-base">
                                            @if (!empty($eventId) && $exhibitor->appointments?->where('event_id', $eventId)->count() > 0)
                                                {{ $exhibitor->appointments?->where('event_id', $eventId)->count() }}
                                            @elseif(empty($eventId) && $exhibitor->appointments?->count() > 0)
                                                {{ $exhibitor->appointments?->count() }}
                                            @else
                                                No Appointments
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        @can('Password Reset')
                                            <a href="#" onclick="event.stopPropagation();"
                                                wire:click.prevent="resetPassword({{ $exhibitor->id }})"
                                                wire:confirm="Are you sure you want to reset this Exhibitor's password?"
                                                class="text-warning" title="Reset Password" data-toggle="tooltip"
                                                data-placement="top">
                                                @include('icons.refresh')
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        @if (isset($exhibitors) && count($exhibitors) == 0)
                            @livewire('not-found-record-row', ['colspan' => 12])
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-end">
                    {{ $exhibitors->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('closeModal', function() {
                $('#stall_allocate').modal('hide');
                // $('#stall_no').val('');
                // $('#space').val('');
                // $('#square_feet').val('');

            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="{{ asset('assets/libs/freeze-table/freeze-table.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#daterange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'DD-MM-YYYY'
                },
                opens: 'left'
            });

            var startDate;
            var endDate;

            $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
                    'DD-MM-YYYY'));
                startDate = picker.startDate.format('YYYY-MM-DD');
                endDate = picker.endDate.format('YYYY-MM-DD');
            });

            $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                startDate = null;
                endDate = null;
                @this.call('dateRangeChanged', startDate, endDate);
            });

            $('#filterBtn').on('click', function() {
                @this.call('dateRangeChanged', startDate, endDate);
            })
        });
        $(".exhibitor-table").freezeTable({
            "scrollBar": true,
            "shadow": true,
            "columnNum": 4,
            'columnkeep': true,
            "freezeColumn": true,
        });
    </script>
    <script>
        document.addEventListener('livewire:initialized', function() {
            var products = new TomSelect('#products', {
                plugins: ['dropdown_input', 'remove_button'],
            });
        });
    </script>
@endpush
