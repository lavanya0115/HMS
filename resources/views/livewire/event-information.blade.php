@push('styles')
    <style>
        .counts {
            color: #f1a922;
        }

        .counts:hover {
            background-color: #f1a922;
            color: #fff !important;
        }
    </style>
@endpush
<div>
    {{-- <div class="page-header d-print-none">
        <div class="container-xl">

        </div>
    </div> --}}

    <div class="page-body">
        <div wire:ignore.self class="modal modal-blur fade" id="add_seminars_modal" tabindex="-1" role="dialog"
            aria-hidden="true" data-bs-backdrop='static'>
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Seminar Attend</h5>
                        <button type="button" class="btn-close" aria-label="Close" wire:click="clearError"></button>
                    </div>
                    <form wire:submit="updateDelegateSeminars">
                        <div class="modal-body">
                            <div class="col-md-12">
                                <div class="mb-3" id="ts1">
                                    <label class="form-label required">Seminar Attend</label>
                                    <div wire:ignore>
                                        <select id="seminars" class="form-select " wire:model.live="seminars_to_attend"
                                            placeholder="Select Seminar" multiple="multiple" tabindex="-1">
                                            @if (isset($seminarData))
                                                @foreach ($seminarData as $seminar)
                                                    @php
                                                        $isRegistered = in_array($seminar->id, $registeredSeminarIds);
                                                    @endphp
                                                    <option value="{{ $seminar->id }}"
                                                        @if ($isRegistered) disabled @endif>
                                                        {{ $seminar->title }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    @error('seminars_to_attend')
                                        <div class="error text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row col-md-12">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Amount</label>
                                        <input type="text" class="form-control" id="amount" wire:model="amount"
                                            placeholder="Enter Amount" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Payment Option</label>
                                        <select class="form-select" id="payment_option" wire:model="payment_option"
                                            disabled>
                                            <option value="">Select Payment Option</option>
                                            <option value="register_and_pay">
                                                {{ !empty($seminarId) ? 'Pay' : 'Register and Pay' }}</option>
                                            <option value="register_and_pay_later">
                                                {{ !empty($seminarId) ? 'Pay Later' : 'Register and Pay Later' }}
                                            </option>
                                        </select>
                                        @error('payment_option')
                                            <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer d-flex align-center justify-content-between">
                            <button type="button" class="btn btn-link link-danger text-decoration-none"
                                wire:click="clearError">Close</button>
                            <button type="submit"
                                class="btn btn-appointment">{{ !empty($seminarId) ? 'Pay' : 'Register' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="container-xl">
            <div class="row row-deck row-cards">
                @include('includes.alerts')
                <div class="col-12">
                    <div class="row row-cards">
                        @if (isOrganizer())
                            <div class="row g-2 align-items-center">
                                <div class="col-auto ms-auto d-print-none">
                                    <div class="btn-list">
                                        @can('Create Exhibitor')
                                            <a href="{{ route('exhibitor.registration', ['eventId' => $eventId]) }}"
                                                class="btn btn-warning d-none d-sm-inline-block">
                                                @include('icons.plus')
                                                Add New Exhibitor
                                            </a>
                                        @endcan
                                        @can('Create Visitor')
                                            <a href="{{ route('visitor-registration', ['eventId' => $eventId]) }}"
                                                class="btn btn-primary d-none d-sm-inline-block">
                                                @include('icons.plus')
                                                Add New Visitor
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($isSalesPerson && isset($exhibitors) && count($exhibitors) > 0)

                            <div class="row">
                                <span class="text-orange fw-bold fs-3">Exhibitors</span>

                                <div class="row ">
                                    @foreach ($exhibitors as $exhibitor)
                                        <div class="col-md-4 pt-3" wire:key="exhibitor-{{ $exhibitor->id }}">
                                            <div class="card">
                                                <div class="card-body ">

                                                    <div class="d-flex justify-content-between">
                                                        <span>
                                                            <img src="{{ asset('storage/' . $exhibitor->logo) }}"
                                                                class="rounded-circle avatar-xl" height="50"
                                                                width="50" />
                                                        </span>
                                                        <span>
                                                            <div class="ms-auto">
                                                                <div class="text d-flex">
                                                                    <span>
                                                                        @include('icons.building-skyscraper')
                                                                    </span>
                                                                    <strong class="ps-2" data-bs-toggle="tooltip"
                                                                        data-bs-placement="right"
                                                                        title="{{ $exhibitor->name }}">{{ substr($exhibitor->name, 0, 20) }}
                                                                    </strong>
                                                                </div>
                                                                <div class="text d-flex">
                                                                    <span>
                                                                        @include('icons.user-circle')
                                                                    </span>
                                                                    <small
                                                                        class="ps-2">{{ $exhibitor->exhibitorContact->name }}</small>
                                                                </div>
                                                            </div>
                                                        </span>

                                                    </div>

                                                    <div class="pt-2">
                                                        @livewire('exhibitor.profile-status', ['exhibitorId' => $exhibitor->id], key($exhibitor->id))
                                                    </div>

                                                    <div class="d-flex justify-content-around">
                                                        @if (isset($exhibitor->userLogin) && count($exhibitor->userLogin) > 0)
                                                            <small class="text text-green fw-b p-2">
                                                                @include('icons.user-check')Engaged
                                                            </small>
                                                        @else
                                                            <small
                                                                class="text text-red fw-b p-2">@include('icons.user-exclamation')Un-Engaged
                                                            </small>
                                                        @endif
                                                        <div class="ms-auto">
                                                            @if (
                                                                !(isset($exhibitor->userLogin) && count($exhibitor->userLogin) > 0) ||
                                                                    !in_array($exhibitor->id, $profileExhibitorIds))
                                                                <button class="btn text-green d-flex lh-1"
                                                                    wire:click="sendNotification('{{ $exhibitor->name }}','{{ $exhibitor->mobile_number }}')">
                                                                    Intimate <span
                                                                        class="ps-2">@include('icons.send')</span>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        @endif

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-orange fw-bold fs-3">Appointment Status</span>
                            @if (auth()->guard('visitor')->check())
                                <div>
                                    <a wire:click="openAttendSeminarModal"
                                        style="text-align: right; padding-right: 5px;"
                                        class="btn btn-sm align-content-end btn-appointment" data-bs-toggle="modal"
                                        data-bs-target="#add_seminars_modal">
                                        @include('icons.presentation')
                                        <span style="padding-left: 10px;"> Attend Seminar </span>
                                    </a>

                                    <a href="{{ route('visitor.find-products', ['eventId' => $eventId]) }}"
                                        class="btn btn-sm align-content-end btn-appointment">
                                        @include('icons.calender-plus')
                                        <span style="padding-left: 5px;"> Book Appointment </span>
                                    </a>
                                </div>
                            @endif
                        </div>
                        {{-- <div class="col-sm-6 col-lg-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="bg-primary text-white avatar">
                                                @include('icons.users-group')
                                            </span>
                                        </div>
                                        <div class="col">
                                            <div class="font-weight-medium">
                                                Visitors
                                            </div>
                                            <div class="text-secondary">
                                                <b>{{ $event->visitors_count ?? 0 }}</b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="bg-green text-white avatar">
                                                @include('icons.building-skyscraper')
                                            </span>
                                        </div>
                                        <div class="col">
                                            <div class="font-weight-medium">
                                                Exhibitors
                                            </div>
                                            <div class="text-secondary">
                                                <b>{{ $event->exhibitors_count ?? 0 }}</b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                        @if (auth()->guard('visitor')->check() || auth()->guard('exhibitor')->check())
                            {{-- <div class="col-sm-6 col-lg-3">
                                <a class="text-decoration-none"
                                    href="{{ route('myappointments', ['eventId' => $eventId, 'appointmentStatus' => 'confirmed']) }}">
                                    <div class="card card-sm counts">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <div class="font-weight-medium ps-3 fw-bold">
                                                        Confirmed
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <span class="avatar bg-gray">
                                                        <b> {{ $confirmedCount ?? 0 }}</b>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div> --}}

                            <div class="col-sm-6 col-lg-3">
                                <a class="text-decoration-none"
                                    href="{{ route('myappointments', ['eventId' => $eventId, 'appointmentStatus' => 'scheduled']) }}">
                                    <div class="card card-sm counts">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <div class="font-weight-medium ps-3 fw-bold">
                                                        Scheduled
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <span class="avatar bg-gray">
                                                        <b> {{ $scheduledCount ?? 0 }} </b>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="text-decoration-none"
                                    href="{{ route('myappointments', ['eventId' => $eventId, 'appointmentStatus' => 'rescheduled']) }}">
                                    <div class="card card-sm counts">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <div class="font-weight-medium ps-3  fw-bold">
                                                        Re-scheduled
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <span class="avatar bg-gray">
                                                        <b>{{ $rescheduledCount ?? 0 }}</b>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="text-decoration-none"
                                    href="{{ route('myappointments', ['eventId' => $eventId, 'appointmentStatus' => 'no-show']) }}">
                                    <div class="card card-sm counts">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <div class="font-weight-medium ps-3  fw-bold">
                                                        No-Show
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <span class="avatar bg-gray">
                                                        <b> {{ $lapsedCount ?? 0 }} </b>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="text-decoration-none"
                                    href="{{ route('myappointments', ['eventId' => $eventId, 'appointmentStatus' => 'cancelled']) }}">
                                    <div class="card card-sm counts">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <div class="font-weight-medium ps-3  fw-bold">
                                                        Cancelled
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <span class="avatar bg-gray">
                                                        <b>{{ $cancelledCount ?? 0 }}</b>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="text-decoration-none"
                                    href="{{ route('myappointments', ['eventId' => $eventId, 'appointmentStatus' => 'completed']) }}">
                                    <div class="card card-sm  counts">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <div class="font-weight-medium ps-3 fw-bold">
                                                        Completed
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <span class="avatar bg-gray">
                                                        <b>{{ $completedCount ?? 0 }}</b>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endif

                        <div class="col-sm-6 col-lg-3">
                            <a class="text-decoration-none"
                                href="{{ isOrganizer() ? route('appointment.summary', ['eventId' => $eventId]) : route('myappointments', ['eventId' => $eventId]) }}">
                                <div class="card card-sm counts">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <div class="font-weight-medium ps-3  fw-bold">
                                                    Total Appointments
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <span class="avatar bg-gray">
                                                    <b>{{ $appointmentCount ?? 0 }}</b>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        @if (auth()->guard('visitor')->check() || isOrganizer())
                            <div class="col-sm-6 col-lg-3">
                                {{-- <a class="text-decoration-none"
                                                    href="{{ route('seminars', ['eventId' => $eventId]) }}"> --}}
                                <div class="card card-sm counts">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <div class="font-weight-medium ps-3 fw-bold">
                                                    Total Seminar count
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <span class="avatar bg-gray">
                                                    <b> {{ $seminarCount ?? 0 }} </b>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
            @if (auth()->guard('visitor')->check())
                <div class="col-lg-12 pt-4">
                    <div class="d-flex flex-row justify-content-between align-items-center">
                        <div>
                            <h4 class="text text-orange">Registered Seminars</h4>
                        </div>
                    </div>
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Seminar Title</th>
                                        <th>Date</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        {{-- <th>Payment Status</th>
                                        <th class="w-1"></th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($registeredSeminars) && count($registeredSeminars) > 0)
                                        @foreach ($registeredSeminars as $seminarIndex => $registeredSeminar)
                                            <tr wire:key='{{ $registeredSeminar->id }}'>
                                                <td>
                                                    {{ $seminarIndex + 1 }}
                                                </td>

                                                <td>
                                                    <div class="text-capitalize">
                                                        {{ $registeredSeminar->seminar?->title ?? '' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    {{ $registeredSeminar->seminar?->date ?? '' }}
                                                </td>
                                                <td>
                                                    {{ $registeredSeminar->seminar?->start_time ?? '' }}
                                                </td>
                                                <td>
                                                    {{ $registeredSeminar->seminar?->end_time ?? '' }}
                                                </td>
                                                {{-- <td
                                                    class="{{ $registeredSeminar->payment_status == 'paid' ? 'text-success' : 'text-danger' }}">
                                                    {{ $registeredSeminar->payment_status == 'paid' ? 'Paid' : 'Unpaid' }}
                                                </td>
                                                <td>
                                                    <a href="#" data-bs-toggle="modal"
                                                        data-bs-target="#add_seminars_modal"
                                                        wire:click="confirmPayment({{ $registeredSeminar->seminar_id }})">
                                                        {{ $registeredSeminar->payment_status == 'paid' ? '' : 'Pay' }}</a>
                                                </td> --}}
                                            </tr>
                                        @endforeach
                                    @endif
                                    @if (isset($registeredSeminars) && count($registeredSeminars) == 0)
                                        @livewire('not-found-record-row', ['colspan' => 6])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
            {{-- @if (auth()->guard('exhibitor')->check())
                <div class="col-lg-12 pt-4">
                    <div class="d-flex flex-row justify-content-between align-items-center">
                        <div>
                            <h4 class="text text-orange">Pending Appointments</h4>
                        </div>
                    </div>
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Visitor Name</th>
                                        <th>Organization</th>
                                        <th>Place</th>
                                        <th>Purpose of Meeting</th>
                                        <th>Schedule Date & Time</th>
                                        <th>Confirm Appointment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($pendingAppointments) && count($pendingAppointments) > 0)
                                        @foreach ($pendingAppointments as $appointmentIndex => $pendingAppointment)
                                            <tr wire:key='item-{{ $pendingAppointment->id }}'>
                                                <td>
                                                    {{ $appointmentIndex + $pendingAppointments->firstItem() }}
                                                </td>

                                                <td>
                                                    <div class="text-capitalize">
                                                        {{ $pendingAppointment->visitor->name }}</div>
                                                </td>
                                                <td>
                                                    <strong>{{ $pendingAppointment->visitor->organization ?? '' }}</strong><br>
                                                    <small>{{ $pendingAppointment->visitor->designation ?? '' }}</small>
                                                </td>
                                                <td>
                                                    <div class='text-capitalize'>
                                                        {{ $pendingAppointment->visitor->address->city ?? '' }}</div>
                                                </td>
                                                <td>
                                                    <div class='text-capitalize' data-bs-toggle="tooltip"
                                                        title="{{ $pendingAppointment->notes ?? '' }}">
                                                        {{ substr($pendingAppointment->notes ?? '', 0, 10) . (strlen($pendingAppointment->notes ?? '') > 10 ? '...' : '') }}
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="text-capitalize">
                                                        {{ $pendingAppointment->scheduled_at->isoFormat('llll') ?? '' }}
                                                    </div>
                                                </td>

                                                <td>
                                                    <a href="javascript:void(0);"
                                                        wire:click="confirmAppointment({{ $pendingAppointment->id }})">
                                                        @include('icons.squrebox')
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @if (isset($pendingAppointments) && count($pendingAppointments) == 0)
                                        @livewire('not-found-record-row', ['colspan' => 7])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif --}}
            {{-- @if (isOrganizer())
                <div class="col-sm-6 pt-4">
                    <h4 class="text-orange fw-bold fs-3">Insights based on Known Source:</h4>
                    <div class="card">
                        <div class="card-body">
                            @if (empty($knownSources))
                                <p>No known sources available for this event.</p>
                            @else
                                <canvas id="knownSourceChart" width="450" height="450"></canvas>
                            @endif
                        </div>
                    </div>
                </div>
            @endif --}}

        </div>

    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <script>
            document.addEventListener('livewire:initialized', function() {
                var seminarsSelect = new TomSelect('#seminars', {
                    plugins: ['dropdown_input', 'remove_button'],
                    create: false,
                    createOnBlur: true,
                });

                Livewire.on('closeModal', function() {
                    $('#add_seminars_modal').modal('hide');
                    seminarsSelect.clear();
                    seminarsSelect.enable();
                });

                Livewire.on('showSeminars', function(seminarId) {
                    seminarsSelect.setValue(seminarId);
                    seminarsSelect.disable();
                });
            });
        </script>
        {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const knownSources = @json(array_column($knownSourcesPercentages, 'label'));
            const knownSourcesPercentages = @json(array_column($knownSourcesPercentages, 'percentage'));

            console.log(knownSources);
            console.log(knownSourcesPercentages);

            const data = {
                labels: knownSources,
                datasets: [{
                    label: 'Known Sources Percentages',
                    data: knownSourcesPercentages,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            };

            const config = {
                type: 'bar',
                data: data,
                options: {
                    responsive: false,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Known Source Bar Chart'
                        }
                    },
                    layout: {
                        padding: {
                            left: 10,
                            right: 10,
                            top: 10,
                            bottom: 10
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                min: 0,
                                max: 100,
                                stepSize: 10,
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            };

            document.addEventListener('DOMContentLoaded', function() {
                console.log("DOM Loaded. Initializing Chart...");

                const ctx = document.getElementById('knownSourceChart').getContext('2d');
                new Chart(ctx, config);

                console.log("Chart initialized.");
            });
        </script> --}}
    @endpush
