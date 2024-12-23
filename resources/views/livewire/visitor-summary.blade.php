<div class="page-body">
    @if (isset($requestEventId))
        @livewire('appointments-modal')
    @endif
    <div wire:ignore.self class="modal" id="visitorModal" tabindex="-1">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Visitor</h5>
                    <button type="button" class="btn-close" aria-label="Close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="event" class="form-label required">Events</label>
                                <div wire:ignore>
                                    <select id="events"
                                        class="form-select @error('visitorData.event_id') is-invalid @enderror"
                                        wire:model.defer="visitorData.event_id" name="event" multiple>
                                        <option value="">Select Event</option>
                                        @foreach ($events as $eventID => $eventTitle)
                                            <option value="{{ $eventID }}">{{ $eventTitle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('visitorData.event_id')
                                    <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label required">Name</label>
                            <div class="input-group">
                                <div class="mb-3 col-md-3" style="padding-right: 4px">
                                    <select class="form-select" wire:model="visitorData.salutation">
                                        <option value="Dr">Dr</option>
                                        <option value="Mr" selected>Mr</option>
                                        <option value="Ms">Ms</option>
                                        <option value="Mrs">Mrs</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-9">
                                    <input type="text" wire:model.defer="visitorData.name"
                                        class="form-control @error('visitorData.name') is-invalid @enderror"
                                        wire:change="getProfileName" class="form-control" name="name"
                                        placeholder="Enter your name" />
                                    @error('visitorData.name')
                                        <div class="error text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="userName" class="form-label required">Profile Name</label>
                                <input type="text" id="username" wire:model.defer="visitorData.username"
                                    class="form-control @error('visitorData.username') is-invalid @enderror"
                                    name="userName" wire:input="checkUserName" pattern="^\S+$"
                                    placeholder="Enter your username" />

                                @if (!$username_exists && $visitorData['username'] !== '')
                                    <div class="text-success">Username is available</div>
                                @endif

                                @error('visitorData.username')
                                    <span class="error text-danger ">{{ $message }}</span>
                                    @if ($visitorData['name'] !== '')
                                        <p class="text-success cursor-pointer" wire:click="setSuggestedValue">
                                            Suggested:
                                            {{ $suggestedValue }}</p>
                                    @endif
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="email" class="form-label required">Email</label>
                                <input type="email" placeholder="Enter email" wire:model.defer="visitorData.email"
                                    class="form-control @error('visitorData.email') is-invalid @enderror"
                                    name="email" />
                                @error('visitorData.email')
                                    <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="mobileNo" class="form-label required">Mobile No.</label>
                                <input type="text" placeholder="Enter your mobile no."
                                    wire:model.defer="visitorData.mobile_no"
                                    class="form-control @error('visitorData.mobile_no') is-invalid @enderror"
                                    name="mobileNo" />
                                @error('visitorData.mobile_no')
                                    <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="organization" class="form-label required">Organization</label>
                                <input type="text" wire:model.defer="visitorData.organization"
                                    class="form-control @error('visitorData.organization') is-invalid @enderror"
                                    name="organization" placeholder="Enter organization" />
                                @error('visitorData.organization')
                                    <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="designation" class="form-label required">Designation</label>
                                <input type="text" wire:model.defer="visitorData.designation"
                                    class="form-control @error('visitorData.designation') is-invalid @enderror"
                                    name="designation" placeholder="Enter designation" />
                                @error('visitorData.designation')
                                    <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            @php
                                $knowSources = getKnownSourceData();
                            @endphp
                            <div class="mb-3">
                                <label for="knownSource" class="form-label required">Known Source</label>
                                <select wire:model.defer="visitorData.known_source" class="form-select"
                                    name="knownSource">
                                    <option value="">Select Known Source</option>
                                    @foreach ($knowSources as $knowSourceKey => $knowSourceLabel)
                                        <option value="{{ $knowSourceKey }}">{{ $knowSourceLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('visitorData.known_source')
                                    <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="country" class="form-label required">Country</label>
                                <select wire:model.live="visitorData.country" wire:change="clearAddressFields"
                                    class="form-select @error('visitorData.country') is-invalid @enderror"
                                    name="country">
                                    @foreach ($countries as $country)
                                        <option value={{ $country }}>{{ $country }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('visitorData.country')
                                    <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="pincode"
                                    class="form-label required">{{ $visitorData['country'] == 'India' ? 'Pincode' : 'Zipcode' }}</label>
                                <input type="text" wire:model.defer="visitorData.pincode" wire:blur='pincode()'
                                    class="form-control @error('visitorData.pincode') is-invalid @enderror"
                                    name="pincode" />
                                @error('visitorData.pincode')
                                    <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3 {{ $visitorData['country'] != 'India' ? 'd-none' : '' }}">
                                <label for="city" class="form-label">City</label>
                                <input type="text" wire:model.defer="visitorData.city" class="form-control"
                                    name="city" disabled />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3 {{ $visitorData['country'] != 'India' ? 'd-none' : '' }}">
                                <label for="state" class="form-label">State</label>
                                <input type="text" wire:model.defer="visitorData.state" class="form-control"
                                    name="state" disabled />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea wire:model.defer="visitorData.address"
                                class="form-control @error('visitorData.address') is-invalid @enderror" rows="3" name="address"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="closeModal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary ms-auto" wire:click="createVisitor">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="container-xl">
        @include('includes.alerts')
        <div class="row">
            <div class="col-lg-12">
                <div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="d-flex gap-2">
                                <h3>List of Visitors</h3>
                                <span wire:click="toggleFilter" style="cursor:pointer;" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Filter">@include('icons.filter-search')</span>
                            </div>
                            <div class="subheader">Visitors Count (Total-{{ $visitorsTotalCount }})</div>
                        </div>
                        <div>
                            @if (!$requestEventId)
                                <button class="btn" wire:click="filterCurrentEventRecords">Current Event<span
                                        class="badge bg-yellow text-yellow-fg ms-2">{{ $eventVisitorsCount }}</span></button>
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
                                        <select class="form-select" wire:model.live.debounce.250ms="eventId"
                                            {{ isset($requestEventId) ? 'disabled' : '' }}>
                                            <option value="">Select Event</option>
                                            @foreach ($eventsList as $eventID => $eventTitle)
                                                <option value="{{ $eventID }}">{{ $eventTitle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control " placeholder="Select a date"
                                            id="daterange" name="daterange">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-flat">
                                            <input type="text" wire:model.live.debounce.250ms="visitorRegId"
                                                class="form-control" placeholder="Reg.ID">
                                            <span class="input-group-text pe-3">
                                                <a href="#" wire:click="$set('visitorRegId', '')"
                                                    class="link-secondary" title="Clear search"
                                                    data-bs-toggle="tooltip">
                                                    @include('icons.close')
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 pt-3">
                                        <div class="input-group input-group-flat">
                                            <input type="text" wire:model.live.debounce.250ms="search"
                                                value="" class="form-control" placeholder="Search…">
                                            <span class="input-group-text pe-3">
                                                <a href="#" wire:click="$set('search', '')"
                                                    class="link-secondary" title="Clear search"
                                                    data-bs-toggle="tooltip">
                                                    @include('icons.close')
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 pt-3">
                                        <select class="form-select"
                                            wire:model.live.debounce.250ms="participateStatus">
                                            <option value="">Select Participation Status</option>
                                            <option value="visited">Visited</option>
                                            <option value="not_visited">Not Visited</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="float-end">
                                    <a href="#" id="filterBtn" class="btn btn-primary">Filter</a>
                                    <a href="{{ isset($requestEventId) ? route('visitors.summary', ['eventId' => $requestEventId]) : route('visitors.summary') }}"
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
                                        wire:click="selectedVisitorsId"
                                        {{ empty($event_id) ? 'disabled' : '' }}>Add</button>
                                </div>
                            @endif
                            @can('Transfer Visitor')
                                <a href="#" class="mb-2 text-decoration-none pe-3" data-bs-toggle="tooltip"
                                    title="Move to Another Event"
                                    wire:click="toggleEvents">@include('icons.cloud-upload')</a>
                            @endcan
                        @endif
                        @can('Export Visitor')
                            <div class="col-auto ps-2">
                                <button class="btn w-10" wire:click="exportToExcel" wire:loading.attr="disabled"
                                    {{ isset($visitors) && count($visitors) == 0 ? 'disabled' : '' }}>
                                    @include('icons.file-export')
                                    <span wire:loading wire:target="exportToExcel">Exporting...</span>
                                    <span wire:loading.remove wire:target="exportToExcel">Export to Excel</span>
                                </button>
                            </div>
                        @endcan
                        @can('Create Visitor')
                            @if (empty($requestEventId))
                                <div class="ps-2">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#visitorModal">
                                        Add Visitor
                                    </button>
                                </div>
                            @endif
                        @endcan
                    </div>

                    <div class="table-responsive visitor-table" wire:ignore>
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    @if (!isset($requestEventId))
                                        <th>
                                            <div>
                                                <label class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        wire:model.live="selectAll"
                                                        style="border-color:rgb(134, 132, 132);">
                                                </label>
                                            </div>
                                        </th>
                                    @endif
                                    <th>#</th>
                                    <th>Score</th>
                                    <th>Name
                                        <span wire:click.prevent="sortColumn('name','asc')" style="cursor:pointer;"
                                            data-toggle="tooltip" data-placement="top" title="Sort Ascending">
                                            @include('icons.arrow-narrow-up')
                                        </span>
                                        <span wire:click.prevent="sortColumn('name','desc')" style="cursor:pointer;"
                                            data-toggle="tooltip" data-placement="top" title="Sort Descending">
                                            @include('icons.arrow-narrow-down')
                                        </span>
                                    </th>
                                    <th>Known Source</th>
                                    <th>City</th>

                                    <th>Nature of Business</th>
                                    <th>Organization
                                        <span wire:click.prevent="sortColumn('organization','asc')"
                                            style="cursor:pointer;" data-toggle="tooltip" data-placement="top"
                                            title="Sort Ascending">
                                            @include('icons.arrow-narrow-up')
                                        </span>
                                        <span wire:click.prevent="sortColumn('organization','desc')"
                                            style="cursor:pointer;" data-toggle="tooltip" data-placement="top"
                                            title="Sort Descending">
                                            @include('icons.arrow-narrow-down')
                                        </span>
                                    </th>

                                    <th>Designation</th>
                                    <th>Reason for Visit</th>
                                    <th>Product Looking for</th>
                                    <th>Source</th>
                                    <th>Mobile.No.
                                        <span wire:click.prevent="sortColumn('mobile_number','asc')"
                                            style="cursor:pointer;" data-toggle="tooltip" data-placement="top"
                                            title="Sort Ascending">
                                            @include('icons.arrow-narrow-up')
                                        </span>
                                        <span wire:click.prevent="sortColumn('mobile_number','desc')"
                                            style="cursor:pointer;" data-toggle="tooltip" data-placement="top"
                                            title="Sort Descending">
                                            @include('icons.arrow-narrow-down')
                                        </span>
                                    </th>
                                    <th>Email
                                        <span wire:click.prevent="sortColumn('email','asc')" style="cursor:pointer;"
                                            data-toggle="tooltip" data-placement="top" title="Sort Ascending">
                                            @include('icons.arrow-narrow-up')
                                        </span>
                                        <span wire:click.prevent="sortColumn('email','desc')" style="cursor:pointer;"
                                            data-toggle="tooltip" data-placement="top" title="Sort Descending">
                                            @include('icons.arrow-narrow-down')
                                        </span>
                                    </th>
                                    <th>Timestamp</th>
                                    <th>No of Appointments
                                        <span wire:click.prevent="sortColumn('appointments_count','asc')"
                                            style="cursor:pointer;" data-toggle="tooltip" data-placement="top"
                                            title="Sort Ascending">
                                            @include('icons.arrow-narrow-up')
                                        </span>
                                        <span wire:click.prevent="sortColumn('appointments_count','desc')"
                                            style="cursor:pointer;" data-toggle="tooltip" data-placement="top"
                                            title="Sort Descending">
                                            @include('icons.arrow-narrow-down')
                                        </span>
                                    </th>
                                    @if (empty($requestEventId))
                                        <th>Attended Shows Count
                                            <span wire:click.prevent="sortColumn('event_visitors_count','asc')"
                                                style="cursor:pointer;" data-toggle="tooltip" data-placement="top"
                                                title="Sort Ascending">
                                                @include('icons.arrow-narrow-up')
                                            </span>
                                            <span wire:click.prevent="sortColumn('event_visitors_count','desc')"
                                                style="cursor:pointer;" data-toggle="tooltip" data-placement="top"
                                                title="Sort Descending">
                                                @include('icons.arrow-narrow-down')
                                            </span>
                                        </th>
                                    @endif
                                    <th class="w-2"></th>

                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($visitors) && count($visitors) > 0)
                                    @foreach ($visitors as $visitorsIndex => $visitor)
                                        <tr wire:key="{{ $visitor->id }}" style="cursor: pointer;"
                                            wire:click.prevent="gotoProfile('{{ $visitor->id }}', 'visitor')">
                                            @if (!isset($requestEventId))
                                                <td>
                                                    <div>
                                                        <label class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                onclick="event.stopPropagation();"
                                                                wire:model="selectedVisitors"
                                                                value="{{ $visitor->id }}"
                                                                style="border-color:rgb(134, 132, 132);">
                                                        </label>
                                                    </div>
                                                </td>
                                            @endif
                                            <td>
                                                {{ $visitorsIndex + $visitors->firstItem() }}
                                            </td>

                                            @php
                                                $completionPercentage = $visitor->getProfileCompletionPercentage();
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
                                            <td>
                                                <div class="text-capitalize small lh-base">
                                                    @php
                                                        $badgeColor = 'bg-danger';
                                                        if ($visitor->visitor_logins_count === 1) {
                                                            $badgeColor = 'bg-warning';
                                                        } elseif ($visitor->visitor_logins_count > 1) {
                                                            $badgeColor = 'bg-success';
                                                        }
                                                    @endphp
                                                    <span
                                                        class="badge {{ $badgeColor }} me-1"></span>{{ $visitor->name }}
                                                </div>
                                            </td>

                                            <td class="text-left small lh-base">
                                                {{ $visitor->eventVisitors?->where('event_id', $eventId)->isNotEmpty()
                                                    ? $visitor->eventVisitors?->where('event_id', $eventId)->first()->known_source
                                                    : $visitor->known_source ?? ' ' }}
                                            </td>
                                            <td class="text-left small lh-base">
                                                {{ $visitor->address?->city ?? '-' }}
                                            </td>

                                            <td>
                                                <div class="text-capitalize small lh-base">
                                                    {{ $visitor->category->name ?? '' }}</div>
                                            </td>
                                            <td>
                                                <div class="text-capitalize small lh-base">
                                                    {{ $visitor->organization }}</div>
                                            </td>
                                            <td>
                                                <div class="text-capitalize small lh-base">
                                                    {{ $visitor->designation }}</div>
                                            </td>
                                            <td style="max-width: 100px; overflow: hidden;">
                                                <div class="text-capitalize small lh-base">
                                                    {{ $visitor->reason_for_visit }}</div>
                                            </td>
                                            <td style="max-width: 100px; overflow: hidden;">
                                                <div class="text-capitalize small lh-base">
                                                    @php
                                                        $productNames = [];
                                                        if (!empty($eventId)) {
                                                            $visitorData = $visitor
                                                                ->eventVisitors()
                                                                ->where('event_id', $eventId)
                                                                ->first();
                                                            if ($visitorData) {
                                                                $productNames[] = explode(
                                                                    ',',
                                                                    $visitorData->getProductNames(),
                                                                );
                                                            }
                                                        } else {
                                                            foreach ($visitor->eventVisitors as $eventVisitor) {
                                                                $productNames[] = explode(
                                                                    ',',
                                                                    $eventVisitor->getProductNames(),
                                                                );
                                                            }
                                                        }
                                                        $uniqueProductNames = array_unique(
                                                            array_merge(...$productNames),
                                                        );
                                                        $productCount = count($uniqueProductNames);
                                                    @endphp

                                                    @if ($productCount > 0)
                                                        {{ implode(', ', array_slice($uniqueProductNames, 0, 3)) }}
                                                        @if ($productCount > 3)
                                                            <a href="#" data-bs-toggle="tooltip"
                                                                title="{{ implode(', ', array_slice($uniqueProductNames, 3)) }}"
                                                                class="fs-5">
                                                                <br>+{{ $productCount - 3 }} more
                                                            </a>
                                                        @endif
                                                    @endif

                                                </div>
                                            </td>
                                            <td class="text-left small lh-base">
                                                @if (!empty($eventId) && $visitor->eventVisitors?->where('event_id', $eventId)->isNotEmpty())
                                                    {{ $visitor->eventVisitors?->where('event_id', $eventId)->first()->registration_type ?? '_' }}
                                                @elseif (empty($eventId))
                                                    {{ $visitor->registration_type }}
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-capitalize small lh-base">
                                                    {{ $visitor->mobile_number }}</div>
                                            </td>
                                            <td>
                                                <div class="small lh-base">{{ strtolower($visitor->email) }}

                                                </div>
                                            </td>
                                            <td class="text-left small lh-base">
                                                @if (!empty($eventId) && $visitor->eventVisitors?->where('event_id', $eventId)->isNotEmpty())
                                                    {{ $visitor->eventVisitors?->where('event_id', $eventId)->first()->created_at->format('d-m-Y H:i:s') ?? '_' }}
                                                @elseif (empty($eventId))
                                                    {{ $visitor->created_at->format('d-m-Y H:i:s') }}
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-capitalize small lh-base">
                                                    @if (!empty($eventId) && $visitor->appointments->where('event_id', $eventId)->count() > 0)
                                                        {{ $visitor->appointments->where('event_id', $eventId)->count() }}
                                                    @elseif(empty($eventId) && $visitor->appointments->count() > 0)
                                                        {{ $visitor->appointments->count() }}
                                                    @else
                                                        No Appointments
                                                    @endif
                                                </div>
                                            </td>
                                            @if (empty($requestEventId))
                                                <td>
                                                    <div class="text-capitalize small lh-base">
                                                        @if ($visitor->eventVisitors?->where('is_visited', 1)->count() > 0)
                                                            {{ $visitor->eventVisitors?->where('is_visited', 1)->count() }}
                                                        @else
                                                            No Visits
                                                        @endif
                                                </td>
                                            @endif
                                            <td>
                                                @php
                                                    $previousEvents = getPreviousEvents();
                                                    $isPreviousEvent = in_array(
                                                        $requestEventId,
                                                        $previousEvents->pluck('id')->toArray(),
                                                    );
                                                @endphp
                                                @if (!$isPreviousEvent)
                                                    <div class="d-flex">
                                                        @can('Update Visitor')
                                                            <a href="{{ route('visitors.edit', ['visitorId' => $visitor->id, 'eventId' => $requestEventId]) }}"
                                                                data-toggle="tooltip" data-placement="top"
                                                                title="Edit Visitor" onclick="event.stopPropagation();">
                                                                @include('icons.edit')
                                                            </a>
                                                        @endcan
                                                        @if (isset($requestEventId))
                                                            @can('Create Appointment')
                                                                <a class="ps-3" href="#"
                                                                    wire:click="getVisitor({{ $visitor->id }})"
                                                                    data-bs-toggle="modal" data-bs-target="#modal-report"
                                                                    data-toggle="tooltip" data-placement="top"
                                                                    title="Make Appointment"
                                                                    onclick="event.stopPropagation();">
                                                                    @include('icons.appointment')
                                                                </a>
                                                            @endcan
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>

                                        </tr>
                                        {{-- </a> --}}
                                    @endforeach
                                @endif
                                @if (isset($visitors) && count($visitors) == 0)
                                    @livewire('not-found-record-row', ['colspan' => 12])
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-end">
                            {{ $visitors->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('closeModal', function() {
                $('#modal-report').modal('hide');
            });
            var eventSelect = new TomSelect('#events', {
                plugins: ['dropdown_input', 'remove_button'],
                create: false,
                createOnBlur: false,
            });
            Livewire.on('closeVisitorModal', function() {
                $('#visitorModal').modal('hide');
                eventSelect.clear();
            });

            var usernameInput = document.getElementById('username');
            usernameInput.addEventListener('focus', function() {
                @this.checkUserName();
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="{{ asset('assets/libs/freeze-table/freeze-table.min.js') }}"></script>

    <script>
        $(function() {
            $('input[name="daterange"]').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'DD-MM-YYYY'
                },
                opens: 'left'
            });

            var startDate;
            var endDate;
            $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
                    'DD-MM-YYYY'));
                startDate = picker.startDate.format('YYYY-MM-DD');
                endDate = picker.endDate.format('YYYY-MM-DD');
            });

            $('input[name="daterange"]').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                startDate = null;
                endDate = null;
                @this.call('dateRangeChanged', startDate, endDate);
            });

            $('#filterBtn').on('click', function() {
                @this.call('dateRangeChanged', startDate, endDate);
            })

        });


        $(".visitor-table").freezeTable({
            "scrollBar": true,
            "shadow": true,
            "columnNum": 4,
            'columnkeep': true,
            "freezeColumn": true,
        });
    </script>
@endpush
