<div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-md-12">
                    @include('includes.alerts')
                    <div class="d-flex justify-content-between mb-3">
                        @if ($previousProfileId)
                            <a href="{{ route('profile-view', ['profileId' => $previousProfileId, 'type' => $type, 'eventId' => $eventId]) }}"
                                class="btn">
                                Previous
                            </a>
                        @endif
                        @if ($nextProfileId)
                            <a href="{{ route('profile-view', ['profileId' => $nextProfileId, 'type' => $type, 'eventId' => $eventId]) }}"
                                class="btn">Next
                            </a>
                        @endif
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs nav-fill" data-bs-toggle="tabs">
                                <li class="nav-item">
                                    <a href="#profile" class="nav-link active" data-bs-toggle="tab">
                                        <span>
                                            @include('icons.user')
                                        </span>
                                        <span class="ps-2">
                                            Profile
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#event" class="nav-link" data-bs-toggle="tab">
                                        <span>
                                            @include('icons.dashboard')
                                        </span>
                                        <span class="ps-2">
                                            Event
                                        </span>
                                    </a>
                                </li>
                                @if ($type === 'visitor')
                                    <li class="nav-item">
                                        <a href="#wishlist" class="nav-link" data-bs-toggle="tab">
                                            <span>
                                                @include('icons.wishlist')
                                            </span>
                                            <span class="ps-2">
                                                Wishlist
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#seminar" class="nav-link" data-bs-toggle="tab">
                                            <span>
                                                @include('icons.presentation')
                                            </span>
                                            <span class="ps-2">
                                                Seminars
                                            </span>
                                        </a>
                                    </li>
                                @endif
                                <li class="nav-item">
                                    <a href="#loginLogs" class="nav-link" data-bs-toggle="tab">
                                        <span>
                                            @include('icons.history-toggle')
                                        </span>
                                        <span class="ps-2">
                                            Login Logs
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#activityLogs" class="nav-link" data-bs-toggle="tab">
                                        <span>
                                            @include('icons.activity')
                                        </span>
                                        <span class="ps-2">
                                            Activity Logs
                                        </span>
                                    </a>
                                </li>

                            </ul>
                        </div>

                        <div class="card-body">

                            <div class="tab-content">

                                <div class="tab-pane active show" id="profile">


                                    <div class="card border-0">
                                        <div class="card-header border-0 d-flex justify-content-between">
                                            <div>
                                                @php
                                                    if ($type === 'visitor') {
                                                        $path = isset($visitor['_meta']['logo'])
                                                            ? $visitor['_meta']['logo']
                                                            : '';
                                                        $editPath = route('visitors.edit', [
                                                            'visitorId' => $visitor->id,
                                                        ]);
                                                        $data = $visitor;
                                                    } else {
                                                        $path = $exhibitor->logo ?? '';
                                                        $editPath = route('exhibitor.edit', [
                                                            'exhibitorId' => $exhibitor->id,
                                                        ]);
                                                        $data = $exhibitor;
                                                    }
                                                    $userType = ['visitor', 'super_admin', 'admin'];
                                                    $nonEditableFields = [
                                                        'No. of Appointments',
                                                        'Registration Type',
                                                        'User Name',
                                                        'Nature of Business',
                                                        'Known Source',
                                                    ];

                                                @endphp
                                                <span>
                                                    <img src="{{ asset('storage/' . $path) }}" class="rounded avatar-xl"
                                                        height="80" width="80" />
                                                </span>
                                            </div>
                                            <div class="col-6 ps-5">
                                                <h1 class="fw-bold">{{ $data->name }}</h1>
                                                <div class="col-8">
                                                    <span>
                                                        @if ($type === 'visitor')
                                                            @livewire('visitor.profile-status', ['visitorId' => $data->id])
                                                        @elseif($type === 'exhibitor')
                                                            @livewire('exhibitor.profile-status', ['exhibitorId' => $data->id])
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                @if ($editMode)
                                                    <span class=" fw-bold text text-success me-3 "
                                                        style="cursor: pointer;"
                                                        wire:click="updateUserDetails({{ $data->id }})">
                                                        <strong> Save </strong>
                                                    </span>
                                                    <a class="fw-bold text text-danger text-decoration-none "
                                                        style="cursor: pointer;"
                                                        href="{{ route('profile-view', ['profileId' => $data->id, 'type' => $type]) }}">
                                                        <strong> Cancel </strong>
                                                    </a>
                                                @else
                                                    <span class="fw-bold text text-primary" style="cursor: pointer;"
                                                        wire:click="editDetails">
                                                        <strong> Edit</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-body">

                                                <div class="datagrid">
                                                    @foreach ($this->getDatagridItems($data) as $item)
                                                        <div class="datagrid-item">
                                                            <div class="datagrid-title d-flex align-items-center gap-2">
                                                                <div>
                                                                    <span>@include($item['icon'])</span>
                                                                    <span class="ps-2">{{ $item['label'] }}</span>
                                                                </div>

                                                                @if ($type === 'visitor')
                                                                    @if (strtolower($item['label']) == 'address')
                                                                        <label
                                                                            class="form-check form-switch d-flex align-items-center gap-1 m-0">
                                                                            <input class="form-check-input"
                                                                                wire:model="is_correct_address"
                                                                                type="checkbox" value="1"
                                                                                {{ $is_correct_address ? 'checked' : '' }}
                                                                                wire:change="updateIsCorrectAddress">
                                                                            <span class="form-check-label">Is Correct
                                                                                Address?</span>
                                                                        </label>
                                                                    @endif
                                                                @endif

                                                            </div>

                                                            <div class="datagrid-content">
                                                                @if ($editMode && !in_array($item['label'], $nonEditableFields))
                                                                    <input wire:key="{{ $item['id'] }}" type="text"
                                                                        class="form-control"
                                                                        wire:model="{{ $item['fieldName'] }}" />
                                                                @elseif($editMode && $item['label'] === 'Nature of Business')
                                                                    <select class="form-select form-control-md"
                                                                        wire:model="{{ $item['fieldName'] }}">
                                                                        @foreach ($categories as $category)
                                                                            <option value="{{ $category->id }}"
                                                                                {{ $category->id === $data->category_id ? 'selected' : '' }}>
                                                                                {{ $category->name }} </option>
                                                                        @endforeach
                                                                    </select>
                                                                @else
                                                                    <strong>{!! $item['value'] !!}</strong>
                                                                @endif
                                                                {{-- <strong>{!! $item['value'] !!}</strong> --}}
                                                            </div>
                                                        </div>
                                                    @endforeach

                                                    <!-- Product Looking For section -->
                                                    <div class="datagrid-item">
                                                        <div class="datagrid-title">
                                                            <span>@include('icons.basket-filled')</span>
                                                            <span class="ps-2">Product Looking For</span>
                                                        </div>
                                                        <div class="datagrid-content">
                                                            @php
                                                                $productNames =
                                                                    $type === 'visitor'
                                                                        ? collect($visitor->eventVisitors)
                                                                            ->flatMap(
                                                                                fn($eventVisitor) => explode(
                                                                                    ',',
                                                                                    $eventVisitor->getProductNames(),
                                                                                ),
                                                                            )
                                                                            ->unique()
                                                                            ->values()
                                                                        : collect($exhibitor->eventExhibitors)
                                                                            ->flatMap(
                                                                                fn($eventExhibitor) => explode(
                                                                                    ',',
                                                                                    $eventExhibitor->getProductNames(),
                                                                                ),
                                                                            )
                                                                            ->unique()
                                                                            ->values();
                                                            @endphp

                                                            @foreach ($productNames->take(3) as $productName)
                                                                <span
                                                                    class="badge bg-blue-lt mt-1 text-wrap">{{ $productName ?? '--' }}</span>
                                                            @endforeach

                                                            @if ($productNames->count() > 3)
                                                                <a href="#" data-bs-toggle="tooltip"
                                                                    title="{{ $productNames->slice(3)->join(', ') }}"
                                                                    class="fs-5">
                                                                    <br>+{{ $productNames->count() - 3 }} more
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                @if (in_array($type, $userType))
                                                    <div class="card mt-3">
                                                        <div class="card-body">
                                                            <div class="datagrid-title">
                                                                <span>@include('icons.building-hospital')</span>
                                                                <span class="ps-2">Hospital Info</span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <label class="form-check form-check-inline">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        wire:model="newHospital"
                                                                        id="newHospitalCheckbox">
                                                                    <span class="form-check-label">New Hospital</span>
                                                                </label>
                                                            </div>
                                                            <div class="col-auto">
                                                                <label class="form-check form-check-inline">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        wire:model="oldHospital"
                                                                        id="oldHospitalCheckbox">
                                                                    <span class="form-check-label">Old Hospital</span>
                                                                </label>
                                                            </div>

                                                            <div id="newHospitalOptions" class="col-auto mt-3"
                                                                style="{{ $newHospital ? 'display: block;' : 'display: none;' }}">
                                                                <div class="option-group">
                                                                    <label class="form-label">Under
                                                                        Construction?</label>
                                                                    <select class="form-select form-control-md"
                                                                        wire:model="underConstruction"
                                                                        id="underConstruction" style="width: 200px;">
                                                                        <option value="">Select</option>
                                                                        <option value="yes">Yes</option>
                                                                        <option value="no">No</option>
                                                                    </select>
                                                                </div>
                                                                <div id="yearsToComplete" class="mt-2"
                                                                    style="{{ $underConstruction ? 'display: block;' : 'display: none;' }}">
                                                                    <label class="form-label">How many years to
                                                                        complete?</label>
                                                                    <input type="text"
                                                                        class="form-control form-control-md"
                                                                        wire:model="yearsToComplete"
                                                                        placeholder="e.g., 1.5 years"
                                                                        style="width: 200px;">
                                                                </div>
                                                            </div>

                                                            <div id="oldHospitalOptions" class="col-auto mt-3"
                                                                style="{{ $oldHospital ? 'display: block;' : 'display: none;' }}">
                                                                <div class="option-group">
                                                                    <label class="form-label">How many years old is the
                                                                        hospital?</label>
                                                                    <input type="text"
                                                                        class="form-control form-control-md"
                                                                        wire:model="yearsOld"
                                                                        placeholder="e.g., 10 years"
                                                                        style="width: 200px;">
                                                                </div>
                                                            </div>

                                                            <div class="col-auto mt-3">
                                                                <button type="button" class="btn btn-primary"
                                                                    wire:click="updateHospitalInfo">Save</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>

                                    </div>


                                </div>

                                <div class="tab-pane" id="event">
                                    <div class="col-md-12">
                                        <div class="row row-cards">
                                            <div class="col-12">
                                                <div class="card border-0">
                                                    <div class="card-header">
                                                        <h3 class="card-title fw-bold">Event Details</h3>
                                                    </div>
                                                    <div class="list-group list-group-flush list-group-hoverable">
                                                        @php
                                                            $participantDatas = isset($eventVisitors)
                                                                ? $eventVisitors
                                                                : (isset($eventExhibitors)
                                                                    ? $eventExhibitors
                                                                    : []);
                                                            $participantDatas = collect($participantDatas)->sortByDesc(
                                                                'event.id',
                                                            );
                                                        @endphp
                                                        @if (!empty($participantDatas) && count($participantDatas) > 0)
                                                            @foreach ($participantDatas as $participant)
                                                                @php
                                                                    $isPreviousEvent = in_array(
                                                                        $participant->event->id,
                                                                        $this->previousEvents->pluck('id')->toArray(),
                                                                    );
                                                                    $isCurrentEvent =
                                                                        $participant->event->id ===
                                                                        $this->currentEvent->id;
                                                                    $isUpcomingEvent = in_array(
                                                                        $participant->event->id,
                                                                        $this->upcomingEvents->pluck('id')->toArray(),
                                                                    );
                                                                    $eventType = '';
                                                                    $statusColor = '';

                                                                    if ($isPreviousEvent) {
                                                                        $eventType = 'Previous Event';
                                                                        $statusColor = 'status-secondary';
                                                                    } elseif ($isUpcomingEvent) {
                                                                        $eventType = 'Upcoming Event';
                                                                        $statusColor = 'status-primary';
                                                                    } else {
                                                                        $eventType = 'Current Event';
                                                                        $statusColor = 'status-green';
                                                                    }

                                                                    $start = \Carbon\Carbon::parse(
                                                                        $participant->event->start_date,
                                                                    );
                                                                    $end = \Carbon\Carbon::parse(
                                                                        $participant->event->end_date,
                                                                    );
                                                                @endphp
                                                                <div class="list-group-item">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-auto">
                                                                            <span
                                                                                class="badge bg-{{ $colors[array_rand($colors)] }}"></span>
                                                                        </div>
                                                                        <div class="col-auto">
                                                                            @php
                                                                                $thumbnailPath =
                                                                                    $participant->event?->_meta[
                                                                                        'thumbnail'
                                                                                    ] ?? '';
                                                                            @endphp
                                                                            <a href="#">
                                                                                <span class="avatar">
                                                                                    <img src="{{ asset('storage/' . $thumbnailPath) }}"
                                                                                        class="rounded avatar-xl"
                                                                                        height="40"
                                                                                        width="40" />
                                                                                </span>
                                                                            </a>
                                                                        </div>
                                                                        <div class="col text-truncate">
                                                                            <span
                                                                                class="text-reset d-block fw-bold fs-3">{{ $participant->event->title ?? '--' }}</span>
                                                                            <div
                                                                                class="d-block text-secondary text-truncate mt-n1">
                                                                                {{ $start->isoFormat('ll') . ' - ' . $end->isoFormat('ll') }}
                                                                            </div>
                                                                            @if ($type === 'exhibitor')
                                                                                <div class="pt-3">
                                                                                    <span class="d-block text-muted">
                                                                                        <strong>Stall No:</strong>
                                                                                        {{ $participant->stall_no ?? 'N/A' }}
                                                                                    </span>
                                                                                    <div class="datagrid-content mt-2">
                                                                                        @foreach ($productNames->take(3) as $productName)
                                                                                            <span
                                                                                                class="badge bg-blue-lt mt-1 text-wrap">{{ $productName ?? '--' }}</span>
                                                                                        @endforeach

                                                                                        @if ($productNames->count() > 3)
                                                                                            <a href="#"
                                                                                                data-bs-toggle="tooltip"
                                                                                                title="{{ $productNames->slice(3)->join(', ') }}"
                                                                                                class="fs-5">
                                                                                                <br>+{{ $productNames->count() - 3 }}
                                                                                                more
                                                                                            </a>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            @elseif ($type === 'visitor')
                                                                                <div class="pt-3">
                                                                                    @if ($participant?->is_visited !== 0)
                                                                                        <span
                                                                                            class="badge bg-green-lt me-1">Visited</span>
                                                                                    @elseif(($isCurrentEvent || $isUpcomingEvent) && $participant?->is_visited == 0)
                                                                                        <span
                                                                                            class="badge bg-yellow-lt me-1">Registered</span>
                                                                                    @else
                                                                                        <span
                                                                                            class="badge bg-red-lt me-1">Not
                                                                                            Visited</span>
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                        <div class="col-auto">
                                                                            <div
                                                                                class="float-end status {{ $statusColor }}">
                                                                                {{ $eventType }}</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                    </div>
                                </div>
                                @if ($type === 'visitor')
                                    <div class="tab-pane" id="wishlist">
                                        @if (!empty($whishlistExhibitors) || !empty($whishlistProducts))
                                            <div class="row">
                                                <div class="col-6 mt-3 ">
                                                    @foreach ($whishlistExhibitors as $key => $eventVisitorList)
                                                        <h2 class="mb-3">{{ $key }}</h2>
                                                        <div class="mb-4">
                                                            <div class="card card-sm">
                                                                <div class="card-status-top bg-green"></div>
                                                                <div class="card-body">
                                                                    <h3 class="card-title">Exhibitor List
                                                                    </h3>
                                                                    {{-- @dump($eventVisitorList) --}}
                                                                    <div class="divide-y-2 mt-4">
                                                                        @if (isset($eventVisitorList) && !empty($eventVisitors))
                                                                            @forelse ($eventVisitorList as $exhibitor)
                                                                                <div>
                                                                                    <span>
                                                                                        @include('icons.user-circle')
                                                                                    </span>
                                                                                    <span
                                                                                        class="text-secondary ">{{ $exhibitor }}</span>
                                                                                </div>
                                                                            @empty
                                                                                <span
                                                                                    class="text-secondary fw-bold text-danger ">No
                                                                                    Wishlist
                                                                                    Added</span>
                                                                            @endforelse
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="col-6 mt-3">
                                                    @foreach ($whishlistProducts as $key => $productLists)
                                                        <h2 class="mb-3">{{ $key }}</h2>
                                                        <div class=" mb-4">
                                                            <div class="card card-sm">
                                                                <div class="card-status-top bg-yellow"></div>
                                                                <div class="card-body">
                                                                    <h3 class="card-title">Product List
                                                                    </h3>
                                                                    <div class="divide-y-2 mt-4">
                                                                        @if (isset($productLists) && !empty($productLists))
                                                                            @forelse ($productLists as $productList)
                                                                                <div>
                                                                                    <span>
                                                                                        @include('icons.basket-filled')
                                                                                    </span>
                                                                                    <span
                                                                                        class="text-secondary ">{{ $productList }}</span>
                                                                                </div>
                                                                            @empty
                                                                                <span
                                                                                    class="text-secondary fw-bold text-danger ">No
                                                                                    Wishlist
                                                                                    Added</span>
                                                                            @endforelse
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="tab-pane" id="seminar">
                                        <div class="row">
                                            @foreach ($seminars as $seminar)
                                                <div class="col-md-6 col-lg-3">
                                                    <div class="card">
                                                        <div class="card-stamp">
                                                            <div class="card-stamp-icon bg-yellow">
                                                                @include('icons.star')
                                                            </div>
                                                        </div>

                                                        <div class="card-body">
                                                            <h3 class="card-title fw-bold">
                                                                {{ $seminar['eventName'] }}</h3>
                                                            @if (isset($seminar['seminarTitle']))
                                                                <h6 class="card-title">Seminars List</h6>
                                                                <ul class="steps steps-counter steps-vertical">
                                                                    @foreach (explode(', ', $seminar['seminarTitle']) as $title)
                                                                        <li class="step-item">
                                                                            {{ $title }}
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <h6 class="card-title text-danger fw-bold text-center">
                                                                    No
                                                                    Seminars</h6>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <div class="tab-pane" id="loginLogs">
                                    <div class="row">
                                        <div class="col-md-12 pt-3">
                                            <div class="card">
                                                <div class="card-table table-responsive">
                                                    <table class="table table-vcenter card-table">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>User Id</th>
                                                                {{-- <th>Role</th> --}}
                                                                <th>Mobile No</th>
                                                                <th>Login At</th>
                                                                <th>Logout At</th>
                                                                <th>Created At</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if (isset($userLogs) && count($userLogs) > 0)
                                                                @foreach ($userLogs as $logIndex => $log)
                                                                    @php
                                                                        $role = 'Admin';
                                                                        $name = !empty($log->user?->name)
                                                                            ? $log->user?->name
                                                                            : '';
                                                                        $mobile_number =
                                                                            $log->user->mobile_number ?? '';
                                                                        $logInTime = Carbon\Carbon::parse(
                                                                            $log->last_login_at,
                                                                        );
                                                                        $logOutTime = Carbon\Carbon::parse(
                                                                            $log->last_logout_at,
                                                                        );
                                                                        if (
                                                                            $log->userable_type == 'App\Models\Visitor'
                                                                        ) {
                                                                            $role = 'Visitor';
                                                                            $name = $log?->visitor?->username ?? '--';
                                                                            $mobile_number =
                                                                                $log->visitor?->mobile_number;
                                                                        } elseif (
                                                                            $log->userable_type ==
                                                                            'App\Models\Exhibitor'
                                                                        ) {
                                                                            $role = 'Exhibitor';
                                                                            $name = $log?->exhibitor?->name ?? '--';
                                                                            $mobile_number =
                                                                                $log->exhibitor?->mobile_number ?? '--';
                                                                        }
                                                                    @endphp
                                                                    <tr wire:key='item-{{ $log->id }}'>
                                                                        <td>
                                                                            {{ $logIndex + $userLogs->firstItem() }}
                                                                        </td>

                                                                        <td>
                                                                            {{ $name ?? '' }}
                                                                        </td>
                                                                        {{-- <td>
                                                                                    {{ $role }}
                                                                                </td> --}}
                                                                        <td>
                                                                            {{ $mobile_number ?? '0' }}
                                                                        </td>

                                                                        <td class="text-capitalize">
                                                                            {{ $logInTime->diffForHumans() }}
                                                                        </td>

                                                                        <td class="text-capitalize">
                                                                            {{ $log->last_logout_at != null ? $logOutTime->diffForHumans() : '--' }}
                                                                        </td>
                                                                        <td>
                                                                            {{ $log->created_at->isoFormat('llll') }}
                                                                        </td>

                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                @livewire('not-found-record-row', ['colspan' => 6])
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="activityLogs">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row row-cards">
                                                <div class="col-12">
                                                    <div class="card border-0">
                                                        <div class="card-header">
                                                            <h3 class="card-title fw-bold">Logs</h3>
                                                        </div>
                                                        <div class="list-group list-group-flush list-group-hoverable">
                                                            @if (isset($logs) && count($logs) > 0)
                                                                @foreach ($logs as $logIndex => $log)
                                                                    @php
                                                                        $action = $log?->event ?? '';
                                                                        $createdAt = $log?->created_at ?? ' ';
                                                                        $updatedAt = $log?->updated_at ?? ' ';
                                                                        $byWhom = $log->causer?->name ?? '';
                                                                        $byWhomType = $log?->causer_type ?? '';
                                                                        $whom = $log->subject?->name ?? '';
                                                                        $whomType = $log->subject_type?->name ?? '';
                                                                        $logName = $log?->log_name ?? '';

                                                                        $role =
                                                                            $this->roleMapping($byWhomType) ??
                                                                            ($this->roleMapping($whomType) ?? '--');
                                                                        // dd($role, $logs, $byWhomType, $whomType, $role);
                                                                        if (is_null($log->causer_type)) {
                                                                            $byWhom =
                                                                                $log->properties['attributes'][
                                                                                    'registration_type'
                                                                                ] ?? '--';
                                                                        }

                                                                        $changes = [];
                                                                        if (
                                                                            isset($log->properties['old']) &&
                                                                            isset($log->properties['attributes'])
                                                                        ) {
                                                                            $oldValue = $log->properties['old'];
                                                                            $newValue = $log->properties['attributes'];

                                                                            $changes = getChangedValues(
                                                                                $oldValue,
                                                                                $newValue,
                                                                            );
                                                                        }

                                                                        if (
                                                                            $log->log_name === 'appointment_log' &&
                                                                            $action === 'created'
                                                                        ) {
                                                                            $withWhom = $this->getVisitorOrExhibitorName(
                                                                                $log->properties['attributes'][
                                                                                    'exhibitor_id'
                                                                                ],
                                                                                'exhibitor',
                                                                            );
                                                                            $changes[] = "Appointment scheduled with $withWhom";
                                                                        }
                                                                    @endphp
                                                                    <div class="list-group-item">
                                                                        <div class="row align-items-center">
                                                                            <div class="col text-truncate">
                                                                                <span
                                                                                    class="text-reset d-block fw-bold fs-3">{{ ucfirst($action) }}</span>
                                                                                <div
                                                                                    class="d-block text-secondary text-truncate mt-n1">
                                                                                    @if ($action === 'created')
                                                                                        {{ $createdAt->isoFormat('llll') }}
                                                                                    @elseif($action === 'updated')
                                                                                        {{ $updatedAt->isoFormat('llll') }}
                                                                                    @else
                                                                                        {{ $createdAt->isoFormat('llll') . ' - ' . $updatedAt->isoFormat('ll') }}
                                                                                    @endif
                                                                                </div>
                                                                                @if (!empty($changes))
                                                                                    <div
                                                                                        class="d-block text-secondary text-truncate mt-n1 pt-2">
                                                                                        {{ " Changes made by $byWhom: " }}
                                                                                        @foreach ($changes as $change)
                                                                                            <li>
                                                                                                <span class="ps-2">
                                                                                                    {{ strip_tags($change) }}
                                                                                                </span>
                                                                                            </li>
                                                                                        @endforeach
                                                                                    </div>
                                                                                @endif
                                                                                <div class="pt-2">
                                                                                    <span class="text fw-bold">
                                                                                        Record {{ $action }} by
                                                                                        <span
                                                                                            class="badge bg-yellow-lt me-1">{{ $byWhom }}</span>
                                                                                        {{ $role }}

                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            @if (!empty($statusColor) && !empty($eventType))
                                                                                <div class="col-auto">
                                                                                    <div
                                                                                        class="float-end status {{ $statusColor }}">
                                                                                        {{ $eventType }}
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newHospitalCheckbox = document.getElementById('newHospitalCheckbox');
            const oldHospitalCheckbox = document.getElementById('oldHospitalCheckbox');
            const newHospitalOptions = document.getElementById('newHospitalOptions');
            const oldHospitalOptions = document.getElementById('oldHospitalOptions');
            const yearsToComplete = document.getElementById('yearsToComplete');
            const underConstruction = document.getElementById('underConstruction');

            function handleCheckboxChange(selectedCheckbox, relatedOptions) {
                if (selectedCheckbox.checked) {

                    if (selectedCheckbox !== newHospitalCheckbox) {
                        newHospitalCheckbox.checked = false;
                        newHospitalOptions.style.display = 'none';
                    }
                    if (selectedCheckbox !== oldHospitalCheckbox) {
                        oldHospitalCheckbox.checked = false;
                        oldHospitalOptions.style.display = 'none';
                    }


                    relatedOptions.style.display = 'block';
                } else {
                    relatedOptions.style.display = 'none';
                }
            }


            function handleUnderConstruction() {
                if (underConstruction.value === 'yes') {
                    yearsToComplete.style.display = 'block';
                } else {
                    yearsToComplete.style.display = 'none';
                }
            }


            if (newHospitalCheckbox.checked) {
                newHospitalOptions.style.display = 'block';
                handleUnderConstruction();
            }

            if (oldHospitalCheckbox.checked) {
                oldHospitalOptions.style.display = 'block';
            }


            newHospitalCheckbox.addEventListener('change', function() {
                handleCheckboxChange(this, newHospitalOptions);
                if (this.checked) {
                    underConstruction.addEventListener('change', handleUnderConstruction);
                } else {
                    yearsToComplete.style.display = 'none';
                }
            });

            // Handle Old Hospital checkbox

            oldHospitalCheckbox.addEventListener('change', function() {
                handleCheckboxChange(this, oldHospitalOptions);
            });

            // Handle Under Construction change initially in case there's a pre-selected value
            underConstruction.addEventListener('change', handleUnderConstruction);
        });
    </script>
@endpush
