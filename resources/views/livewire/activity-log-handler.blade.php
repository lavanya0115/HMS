<div class="page-body">
    <div class="container mt-3 col-lg-12">
        @include('includes.alerts')
        <div class="card p-2">
            <div class="d-flex flex-row justify-content-start">
                <div>
                    <h3>Activity Logs</h3>
                </div>
            </div>
            <div class="container mt-3">
                <div class="row">
                    <div class="col">
                        <div class="mb-3">
                            <select type="text" class="form-select" placeholder="Select tags" id="select-log"
                                value="" wire:model.live="log">
                                <option value="">Select Log</option>
                                <option value="user_log">User Log</option>
                                <option value="appointment_log">Appointment Log</option>
                                <option value="visitor_log">Visitor Log</option>
                                <option value="event_visitor_log">Event Visitor Log</option>
                                <option value="exhibitor_log">Category Log</option>
                                <option value="event_exhibitor_log">Event Exhibitor Log</option>
                                <option value="exhibitor_contact_log">Exhibitor Contact Log</option>
                                <option value="exhibitor_product_log">Exhibitor Product Log</option>
                                <option value="category_log">Category Log</option>
                                <option value="sales_person_unlink_log">Sales Person Unlink Log</option>
                                <option value="announcement_log">Announcement Log</option>
                                <option value="medshorts_log">Medshorts Log</option>
                                <option value="mail_template_log">Mail Template Log</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <select type="text" class="form-select" placeholder="Select tags" id="select-action"
                                value="" wire:model.live="action">
                                <option value="">Select Action</option>
                                <option value="created">Created</option>
                                <option value="updated">Updated</option>
                                <option value="deleted">Deleted</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <button class="btn btn-outline-danger" wire:click= "resetAttributes"><span
                                    class="ms-2">@include('icons.close')</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Action</th>
                            <th>By Whom</th>
                            <th>Mobile No</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($logs) && count($logs) > 0)
                            @foreach ($logs as $logIndex => $log)
                                @php

                                    $role = 'Admin';
                                    if ($log->causer_type === 'App\Models\Visitor') {
                                        $role = 'Visitor';
                                    } elseif ($log->causer_type === 'App\Models\Exhibitor') {
                                        $role = 'Exhibitor';
                                    }
                                    $name = $log->causer?->name ?? '';
                                    if ($log->causer_type === null) {
                                        $name = isset($log->properties['attributes']['registration_type'])
                                            ? $log->properties['attributes']['registration_type']
                                            : '--';
                                        $mobileNo = isset($log->properties['attributes']['mobile_number'])
                                            ? $log->properties['attributes']['mobile_number']
                                            : '--';
                                    }

                                    $changes = [];
                                    $subjectTypeParts = explode('\\', $log?->subject_type);
                                    $model = $subjectTypeParts[2] ?? '';
                                    if (isset($log->properties['old']) && isset($log->properties['attributes'])) {
                                        $oldValue = $log->properties['old'];
                                        $newValue = $log->properties['attributes'];
                                        $changes = getChangedValues($oldValue, $newValue);
                                    } elseif ($log?->event === 'created') {
                                        $changes[] = "$model Record created by $name";
                                    } elseif ($log?->event === 'deleted') {
                                        $changes[] = "$model Record deleted ";
                                    }
                                    $changesList = implode('<br>', array_map('strip_tags', $changes));

                                @endphp
                                <tr wire:key='item-{{ $log->id }}'>
                                    <td>
                                        {{ $logIndex + $logs->firstItem() }}
                                    </td>
                                    <td class="text-capitalize">
                                        {{ str_replace('_', ' ', $log?->log_name) }}
                                    </td>
                                    <td class="text-capitalize">
                                        {{ $log?->event ?? '' }}
                                        <br>
                                        <small
                                            class="fw-bold">{{ !empty($changesList) ? $changesList : $log?->description ?? '--' }}</small>
                                        @if ($log?->log_name !== 'appointment_log' && $log?->event === 'created')
                                            <small class="fw-bold">{{ " --by $name" }}</small>
                                        @endif
                                        <br>
                                        {{-- <small class="fw-bold">{{ $log?->description }}</small> --}}
                                    </td>
                                    <td>
                                        {{ $name ?? '' }}
                                    </td>
                                    <td>
                                        {{ $log->causer?->mobile_number ?? ($mobileNo ?? '') }}
                                    </td>
                                    <td>
                                        {{ $log->created_at->isoFormat('llll') }}
                                    </td>

                                </tr>
                            @endforeach
                        @else
                            @livewire('not-found-record-row', ['colspan' => 7])
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="row d-flex flex-row ">
                    @if (isset($logs) && count($logs) != 0)
                        <div class="col">
                            <div class="d-flex flex-row mb-3">
                                <div>
                                    <label class="p-2" for="perPage">Per Page</label>
                                </div>
                                <div>
                                    <select class="form-select" id="perPage" name="perPage" wire:model="perPage">
                                        <option value=10>10</option>
                                        <option value=50>50</option>
                                        <option value=100>100</option>
                                    </select>

                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col d-flex justify-content-end">
                        @if (isset($logs) && count($logs) >= 0)
                            {{ $logs->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-5 col-lg-12">
        <div class="card p-2">
            <div class="d-flex flex-row justify-content-between align-items-center">
                <div>
                    <h3>User Login Activity</h3>
                </div>
            </div>
            <div class="container mt-3">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <select type="text" class="form-select" placeholder="Select tags" id="select-log"
                                value="" wire:model.live="role">
                                <option value="">Select Role</option>
                                <option value="visitor">Visitor</option>
                                <option value="exhibitor">Exhibitor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <button class="btn btn-outline-danger" wire:click= "resetAttributes"><span
                                    class="ms-2">@include('icons.close')</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Role</th>
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
                                    $name = !empty($log->user?->name) ? $log->user?->name : '';
                                    $mobile_number = $log->user->mobile_number ?? '';
                                    $logInTime = Carbon\Carbon::parse($log->last_login_at);
                                    $logOutTime = Carbon\Carbon::parse($log->last_logout_at);
                                    if ($log->userable_type == 'App\Models\Visitor') {
                                        $role = 'Visitor';
                                        $name = $log?->visitor?->username ?? '--';
                                        $mobile_number = $log->visitor?->mobile_number;
                                    } elseif ($log->userable_type == 'App\Models\Exhibitor') {
                                        $role = 'Exhibitor';
                                        $name = $log?->exhibitor?->name ?? '--';
                                        $mobile_number = $log->exhibitor?->mobile_number ?? '--';
                                    }
                                @endphp
                                <tr wire:key='item-{{ $log->id }}'>
                                    <td>
                                        {{ $logIndex + $userLogs->firstItem() }}
                                    </td>

                                    <td>
                                        {{ $name ?? '' }}
                                    </td>
                                    <td>
                                        {{ $role }}
                                    </td>
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
            <div class="card-footer">
                <div class="row d-flex flex-row ">
                    @if (isset($userLogs) && count($userLogs) != 0)
                        <div class="col">
                            <div class="d-flex flex-row mb-3">
                                <div>
                                    <label class="p-2" for="perPage">Per Page</label>
                                </div>
                                <div>
                                    <select class="form-select" id="logPerPage" name="logPerPage"
                                        wire:model="logPerPage" wire:change="changePageValue($event.target.value)">
                                        <option value=10>10</option>
                                        <option value=50>50</option>
                                        <option value=100>100</option>
                                    </select>

                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col d-flex justify-content-end">
                        @if (isset($userLogs) && count($userLogs) >= 0)
                            {{ $userLogs->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
