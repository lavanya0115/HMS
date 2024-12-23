<div class="page-body">

    <div wire:ignore.self class="modal modal-blur fade" id="viewFeedback" tabindex="-1" role="dialog" aria-hidden="true"
        data-bs-backdrop='static'>
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Post Meeting Notes</h5>
                    <button type="button" class="btn-close" wire:click.prevent="$dispatch('closeViewFeedbackModal')"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="timeline">
                        @foreach ($feedbacks as $feedback)
                            <li class="timeline-event">
                                <div class="timeline-event-icon bg-twitter-lt">
                                    @php
                                        $logopath = $feedback['logo'];
                                    @endphp
                                    @if ($logopath)
                                        <img src="{{ asset('storage/' . $logopath) }}">
                                    @elseif(!$logopath && $feedback['type'] == 'exhibitor')
                                        @include('icons.building')
                                    @else()
                                        @include('icons.user')
                                    @endif
                                </div>
                                <div class="card timeline-event-card">
                                    <div class="card-body row">
                                        <p class="fw-bold feedback">{{ $feedback['name'] }}</p>
                                        <p class="text-secondary col-md-8 feedback">
                                            {{ $feedback['message'] }}</p>
                                        <div class="text-secondary col-md-4 feedback_timestamp">
                                            {{ isset($feedback['timestamp']) ? Carbon\Carbon::parse($feedback['timestamp'])->isoFormat('lll') : null }}
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3 col-lg-12">
        @include('includes.alerts')
        <div class="card p-2">
            <div class="d-flex flex-row justify-content-between align-items-center">
                <div>
                    <h3>List of Appointments</h3>
                    {{-- <small> --}}
                    {{-- {!! $selectRecordsCount
                            ? '<strong>' .
                                $selectRecordsCount .
                                '</strong> record selected <a href="#" wire:click.prevent="getAllRecords"> click here </a>  to select <strong>' .
                                $totalCount .
                                '</strong> records'
                            : '' !!} --}}
                    {{-- </small> --}}
                    {{-- <small> {!! $selectRecordsCount ? '<a href="#" wire:click.prevent ="clearSelection">Clear Selected Rows</a>' : '' !!}</small> --}}
                </div>
                <div>
                    @can('Export Appointment')
                        <button class="me-3 btn align-items-center text-secondary" wire:loading.attr="disabled"
                            {{ isset($appointments) && count($appointments) > 0 ? '' : 'disabled' }}
                            wire:click.prevent="exportData" style="cursor: pointer">
                            @include('icons.table-export')
                            <span wire:loading.remove wire:target="exportData">
                                Export to Excel
                            </span>
                            <span wire:loading wire:target="exportData">
                                Exporting...
                            </span>
                        </button>
                    @endcan

                    <button id="btn" wire:click="toggleBtn" class=" btn text-secondary " style="cursor: pointer">
                        <span class="ms-2">@include('icons.filter-search')</span>
                    </button>
                    @if ($toggleContent == true || !empty($search))
                        <a href ="{{ route('appointment.summary', ['eventId' => $eventId]) }}"
                            class="text-danger text-decoration-none fw-bold"><small>Reset</small></a>
                    @endif
                </div>
            </div>
            @if ($toggleContent == true || !empty($search))
                <div class="card-body d-flex justify-content-between p-2 pt-3">
                    <div class="d-flex align-items-center">
                        <input type="text" class="form-control" wire:model.live="search"
                            placeholder="Search Exhibitors, Visitors">
                        <span wire:click="$set('search', '')" class="p-2"
                            style="margin-left:-20%; cursor: pointer;">@include('icons.close')</span>
                    </div>

                    <div class="d-flex align-items-center">
                        <label for="source" class="me-2">Source</label>
                        <select wire:model.live="source" class="form-select" id="source">
                            <option value="all">All Sources</option>
                            @foreach ($sourceOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="d-flex align-items-center">
                        <input type="date" class="form-control px-2 ms-2" wire:model.live="dateFilter">
                        <span wire:click="$set('dateFilter', null)" class="p-2"
                            style="cursor: pointer;">@include('icons.close')</span>
                    </div>
                </div>
            @endif
        </div>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            {{-- <th>
                                <span class="p-3 ">
                                    <input type="checkbox" wire:model.live="isSelectAll" class="form-input">
                                </span>
                            </th> --}}
                            <th>Products</th>
                            <th>
                                <div class="d-flex">
                                    Visitors
                                    <span data-bs-toggle="tooltip" title="Sort By Asc"
                                        wire:click="orderByAsc('visitor')" style="cursor: pointer">
                                        @include('icons.arrow-narrow-up')
                                    </span>
                                    <span data-bs-toggle="tooltip" data-bs-placement="right" title="Sort By Desc"
                                        wire:click="orderByDesc('visitor')" style="cursor: pointer">
                                        @include('icons.arrow-narrow-down')
                                    </span>
                                </div>
                            </th>
                            <th>
                                <div class="d-flex">
                                    Designation
                                    <span data-bs-toggle="tooltip" title="Sort By Asc"
                                        wire:click="orderByAsc('designation')" style="cursor: pointer">
                                        @include('icons.arrow-narrow-up')
                                    </span>
                                    <span data-bs-toggle="tooltip" data-bs-placement="right" title="Sort By Desc"
                                        wire:click="orderByDesc('designation')" style="cursor: pointer">
                                        @include('icons.arrow-narrow-down')
                                    </span>
                                </div>
                            </th>
                            <th>
                                <div class="d-flex">
                                    Exhibitors
                                    <span data-bs-toggle="tooltip" title="Sort By Asc"
                                        wire:click="orderByAsc('exhibitor')" style="cursor: pointer">
                                        @include('icons.arrow-narrow-up')
                                    </span>
                                    <span data-bs-toggle="tooltip" data-bs-placement="right" title="Sort By Desc"
                                        wire:click="orderByDesc('exhibitor')" style="cursor: pointer">
                                        @include('icons.arrow-narrow-down')
                                    </span>
                                </div>
                            </th>
                            <th>
                                <div class="d-flex">
                                    Date & Time
                                    <span data-bs-toggle="tooltip" title="Sort By Asc"
                                        wire:click="orderByAsc('scheduled_at')" style="cursor: pointer">
                                        @include('icons.arrow-narrow-up')
                                    </span>
                                    <span data-bs-toggle="tooltip" data-bs-placement="right" title="Sort By Desc"
                                        wire:click="orderByDesc('scheduled_at')" style="cursor: pointer">
                                        @include('icons.arrow-narrow-down')
                                    </span>
                                </div>
                            </th>
                            <th>
                                <div class="d-flex">
                                    Status
                                    <span data-bs-toggle="tooltip" title="Sort By Asc"
                                        wire:click="orderByAsc('status')" style="cursor: pointer">
                                        @include('icons.arrow-narrow-up')
                                    </span>
                                    <span data-bs-toggle="tooltip" data-bs-placement="right" title="Sort By Desc"
                                        wire:click="orderByDesc('status')" style="cursor: pointer">
                                        @include('icons.arrow-narrow-down')
                                    </span>
                                </div>
                            </th>
                            <th>
                                <div class="d-flex">Created At
                                    <span data-bs-toggle="tooltip" title="Sort By Asc"
                                        wire:click="orderByAsc('created_at')" style="cursor: pointer">
                                        @include('icons.arrow-narrow-up')
                                    </span>
                                    <span data-bs-toggle="tooltip" data-bs-placement="right" title="Sort By Desc"
                                        wire:click="orderByDesc('created_at')" style="cursor: pointer">
                                        @include('icons.arrow-narrow-down')
                                    </span>
                            </th>
                            <th>Updated At</th>
                            {{-- <th >Action</th> --}}
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>

                        @if (isset($appointments) && count($appointments) > 0)
                            @foreach ($appointments as $appointmentIndex => $appointment)
                                <tr wire:key='item-{{ $appointment->id }}'>
                                    <td>
                                        {{ $appointmentIndex + $appointments->firstItem() }}
                                    </td>
                                    {{-- <td>
                                        <span class="p-3 ">
                                            <input type="checkbox" class="form-input singleRow"
                                                wire:key="checkbox-{{ $appointment->id }}"
                                                wire:click="selectedRows({{ $appointment->id }})"
                                                {{ $isSelectAll ? 'checked' : '' }} >
                                        </span>

                                    </td> --}}
                                    <td>
                                        @php
                                            $visitorData = $appointment
                                                ->eventVisitorInfo()
                                                ->where('event_id', $eventId)
                                                ->first();
                                            $productNames = $visitorData ? $visitorData->getProductNames() : null;
                                            $products = $productNames
                                                ? collect(explode(',', $productNames))
                                                : collect();
                                            $productCount = count($products);
                                        @endphp
                                        <div class="text-capitalize small lh-base">
                                            {{ $productCount > 0 ? implode(', ', $products->take(2)->all()) : 'No Products' }}
                                            @if ($productCount > 2)
                                                <a href="#" data-bs-toggle="tooltip"
                                                    title="{{ implode(', ', $products->slice(2)->all()) }}"
                                                    class="fs-5">
                                                    <br>+{{ $productCount - 2 }} more
                                                </a>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- @if (str_word_count($appointment->eventVisitorInfo->getProductNames()) >= 3)
                                            'Read More'
                                        @else --}}
                                    {{-- {{ $appointment->eventVisitorInfo->getProductNames() ?? ' No Products ' }} --}}
                                    {{-- @endif --}}
                                    </td>
                                    <td>
                                        <div class="text-capitalize">
                                            <div>{{ $appointment->visitor->name ?? 'Visitor Details' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">
                                            <div>
                                                <strong>{{ $appointment->visitor->designation ?? 'Visitor designation' }}</strong>
                                                <br>
                                                <small>{{ $appointment->visitor->organization ?? 'Visitor organization' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $eventExhibitorData = $appointment->exhibitor?->eventExhibitors
                                                ?->where('event_id', $eventId)
                                                ->first();
                                        @endphp
                                        <div class="text-capitalize">
                                            {{ $appointment->exhibitor->name ?? 'Exhibitor Details' }} - <span
                                                class="fw-bold">{{ $eventExhibitorData->stall_no ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">
                                            {{ $appointment->scheduled_at->isoFormat('llll') ?? 'Date and Time' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div @class([
                                            'text-capitalize',
                                            'badge',
                                            'bg-blue-lt' => $appointment->status == 'scheduled' ? true : false,
                                            'bg-secondary-lt' => $appointment->status == 'rescheduled' ? true : false,
                                            'bg-red-lt' =>
                                                $appointment->status == 'cancelled' || $appointment->status == 'no-show'
                                                    ? true
                                                    : false,
                                            'bg-green-lt' =>
                                                $appointment->status == 'completed' ||
                                                $appointment->status == 'confirmed'
                                                    ? true
                                                    : false,
                                        ])>
                                            {{ $appointment->status ?? '' }}
                                        </div>
                                    </td>
                                    <td>{{ $appointment->created_at->isoFormat('llll') ?? '' }}</td>
                                    <td>{{ $appointment->updated_at->isoFormat('llll') ?? '' }}</td>
                                    <td>
                                        <div class="d-flex justify-content-between gap-2">
                                            @can('View Feedback')
                                                @if (!empty($appointment->_meta['exhibitor_feedback']) || !empty($appointment->_meta['visitor_feedback']))
                                                    <a href="#"
                                                        wire:click='exhibitorAppointmentId({{ $appointment->id }})'
                                                        title="View Post Meeting Notes" data-toggle="tooltip"
                                                        data-placement="top" data-bs-toggle="modal"
                                                        data-bs-target="#viewFeedback">
                                                        @include('icons.eye-check')
                                                    </a>
                                                @endif
                                            @endcan
                                            @can('Delete Appointment')
                                                @if (isOrganizer())
                                                    <a href="#"class="text-danger"
                                                        wire:click.prevent="$dispatch('canDeleteAppointment',{{ $appointment->id }})"
                                                        {{-- wire:click='deleteAppointment({{ $appointment->id }})' --}} title="delete appointment"
                                                        data-toggle="tooltip" data-placement="top">
                                                        @include('icons.trash')
                                                    </a>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @livewire('not-found-record-row', ['colspan' => 9])
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="row d-flex flex-row ">
                    @if (isset($appointments) && count($appointments) != 0)
                        <div class="col">
                            <div class="d-flex flex-row mb-3">
                                <div>
                                    <label class="p-2" for="perPage">Per Page</label>
                                </div>
                                <div>
                                    <select class="form-select" id="perPage" name="perPage" wire:model="perPage"
                                        wire:change="changePageValue($event.target.value)">
                                        <option value=10>10</option>
                                        <option value=50>50</option>
                                        <option value=100>100</option>
                                    </select>

                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col d-flex justify-content-end">
                        @if (isset($appointments) && count($appointments) >= 0)
                            {{ $appointments->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mt-5">
            @if (isset($activities) && count($activities) > 0)
                <div class="card" style="height: 28rem">
                    <div class="card-header ">
                        <h4>Activity Logs</h4>
                    </div>
                    <div class="card-body card-body-scrollable card-body-scrollable-shadow">
                        <div class="divide-y">
                            <div class="row">
                                <ul class="steps steps-vertical ps-5 pt-3">
                                    @foreach ($activities as $activity)
                                        <li class="step-item ">
                                            <div class="h4 m-0">{{ $activity->event }}</div>
                                            <div class="text-secondary">
                                                @php
                                                    $visitorClass = $activity->causer_type == 'App\Models\Visitor';
                                                    $exhibitorClass = $activity->causer_type == 'App\Models\Exhibitor';
                                                    $role = $activity->causer?->roles?->first()?->name ?? '';
                                                    $name = '';
                                                    if ($visitorClass) {
                                                        $role = 'Visitor';
                                                        $name = $activity->subject?->exhibitor?->name ?? '';
                                                    } elseif ($exhibitorClass) {
                                                        $role = 'Exhibitor';
                                                        $name = $activity->subject?->visitor?->name ?? '';
                                                    }
                                                    $oldValues = $activity->getExtraProperty('old') ?? [];
                                                    $newValues = $activity->getExtraProperty('attributes') ?? [];
                                                    $changes = getChangedValues($oldValues, $newValues);
                                                    if ($activity->event == 'deleted') {
                                                        $visitorName = App\Models\Visitor::where(
                                                            'id',
                                                            $oldValues['visitor_id'],
                                                        )
                                                            ->pluck('username')
                                                            ->first();
                                                        $exhibitorName = App\Models\Exhibitor::where(
                                                            'id',
                                                            $oldValues['exhibitor_id'],
                                                        )
                                                            ->pluck('name')
                                                            ->first();
                                                    }
                                                @endphp

                                                {{ $activity->causer?->name . '  (' . ucfirst($role) . ') ' }}

                                                @if ($activity->event === 'updated')
                                                    changed value of
                                                @elseif($activity->event === 'created')
                                                    created
                                                @elseif($activity->event === 'deleted')
                                                    {{ 'deleted ' . $visitorName . ' - ' . $exhibitorName . ' ' . $oldValues['status'] . ' appointment' }}
                                                @endif
                                                {!! implode(', ', $changes) !!}

                                                {{ ' ' . ($activity->event === 'updated' ? ' in ' : ' ') . $name . ' Record  -  ' . ($activity->created_at->diffForHumans() ?? '') }}
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col d-flex justify-content-end">
                                {{ $activities->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('closeViewFeedbackModal', function() {
                $('#viewFeedback').modal('hide');
                $(".feedback").html("");
                $(".feedback_timestamp").html("");
            });
        });
        // Livewire.on('clearCheckboxSelection', function() {
        //     const checkboxes = document.querySelectorAll('.singleRow');
        //     checkboxes.forEach(checkbox => {
        //         checkbox.checked = false;
        //     });
        // });

        Livewire.on('canDeleteAppointment', (appointmentId) => {
            if (confirm('Are you sure you want to delete this Appointment ?')) {
                Livewire.dispatch('deleteAppointment', {
                    appointmentId
                });
            }
        });
        Livewire.on('canDeleteEvent', (eventId) => {
            if (confirm('Are you sure to delete this Event ?')) {
                Livewire.dispatch('deleteEvent', {
                    eventId
                });
            }
        });
    </script>
@endpush
