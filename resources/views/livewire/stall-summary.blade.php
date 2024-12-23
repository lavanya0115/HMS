<div>
    <div class="page-body">
        <div class="container-xl">
            @include('includes.alerts')
            <div class="row">

                <div class="col-lg-12">
                    <div class="d-flex flex-row justify-content-between align-items-center">
                        <div>
                            <h4>List all Events </h4>
                        </div>
                        <div class="mb-2">
                            <a href="{{ route('stall-handler') }}" class="btn btn-primary">Create Stall</a>
                        </div>
                    </div>
                    <div class="card mb-3 d-flex flex-row justify-content-between align-items-center">
                        <div class="p-3 col-md-6">
                            <div class="mb-2">
                                <label for="event_id" class="form-label">Event</label>
                                <select id="event_id" class="form-control @error('event_id') is-invalid @enderror"
                                    wire:model.live="event_id" autocomplete="off">
                                    <option value="">Select the Event</option>
                                    @foreach ($events as $event)
                                        <option value="{{ $event->id }}">
                                            {{ $event->event_description ?? $event->title }}</option>
                                    @endforeach
                                </select>
                                @error('event_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class=" col-md-6">
                            <div class="mt-3">

                                <a href="{{ route('stall-summary') }}"
                                    class="text text-danger text-decoration-none">Reset</a>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Event Name</th>
                                        <th>Hall No</th>
                                        <th>Stall No</th>
                                        <th>Stall Type</th>
                                        <th>Stall Size</th>
                                        <th>Special Feature</th>
                                        <th colspan="w-1">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($stalls) && count($stalls) > 0)
                                        @foreach ($stalls as $index => $stall)
                                            <tr wire:key='item-{{ $stall->id }}'>

                                                <td>
                                                    {{ $index + $stalls->firstItem() }}
                                                </td>

                                                <td>
                                                    <div class="text-capitalize">
                                                        {{ $stall?->event?->event_description ?? ($stall?->event?->title ?? '') }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">
                                                        {{ $stall?->hall_number ?? '' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">
                                                        {{ $stall?->stall_number ?? '' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">
                                                        {{ $stall?->stall_type ?? '' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">
                                                        {{ $stall?->size ?? '' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">
                                                        {{ $stall?->special_feature ?? '' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        @can('Update Stall')
                                                            <a
                                                                href="{{ route('stall-handler', ['stallId' => $stall->id]) }}">
                                                                @include('icons.edit')
                                                            </a>
                                                        @endcan
                                                        @can('Delete Stall')
                                                            <span type="button" class="text text-danger"
                                                                wire:click="deleteEventStall({{ $stall->id }})"
                                                                wire:confirm="Are you sure you want to delete this stall?">
                                                                @include('icons.trash')
                                                            </span>
                                                        @endcan

                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        @livewire('not-found-record-row', ['colspan' => 8])
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer">
                            <div class="row d-flex flex-row mb-3">
                                @if (isset($stalls) && count($stalls) != 0)
                                    <div class="col">
                                        <div class="d-flex flex-row mb-3">
                                            <div>
                                                <label class="p-2" for="perPage">Per Page</label>
                                            </div>
                                            <div>
                                                <select class="form-select" id="perPage" name="perPage"
                                                    wire:model="perPage"
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
                                    @if (isset($stalls) && count($stalls) >= 0)
                                        {{ $stalls->links() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pt-4">
                    @if (isset($stallActivities) && count($stallActivities) > 0)
                        <h4>Activity Logs</h4>
                        <ul class="steps steps-vertical ps-5 pt-3">
                            @foreach ($stallActivities as $activity)
                                <li class="step-item ">
                                    <div class="h4 m-0">{{ $activity->event }}</div>
                                    <div class="text-secondary">

                                        @php
                                            $role = $activity->causer->roles->first()->name;
                                        @endphp
                                        {{ $activity->causer?->name . '  (' . $role . ') ' }}

                                        @if ($activity->event === 'updated')
                                            changed value of
                                        @elseif($activity->event === 'created')
                                            created
                                        @elseif($activity->event === 'deleted')
                                            deleted
                                        @endif

                                        @php
                                            $oldValues = $activity->getExtraProperty('old') ?? [];
                                            $newValues = $activity->getExtraProperty('attributes') ?? [];
                                            $changes = getChangedValues($oldValues, $newValues);

                                        @endphp

                                        {!! implode(', ', $changes) !!}

                                        {{ ' ' .
                                            ($activity->event === 'updated' ? ' in ' : ' ') .
                                            ($activity->event === 'deleted' ? $oldValues['stall_number'] : $activity->subject->title ?? '') .
                                            ' Record  -  ' .
                                            ($activity->created_at->diffForHumans() ?? '') }}

                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="col d-flex justify-content-end mt-3">
                        @if (isset($stallActivities) && count($stallActivities) >= 0)
                            {{ $stallActivities->links() }}
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
