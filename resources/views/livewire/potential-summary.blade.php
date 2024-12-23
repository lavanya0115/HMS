<div>
    <div class="page-body">
        <div class="container-xl">
            @include('includes.alerts')
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4>List all Potential</h4>
                        </div>
                        <div class="mb-2">
                            <a class="btn btn-primary" href="{{ route('potential-create') }}"> Create Potential </a>
                        </div>
                    </div>
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Lead Id</th>
                                        <th>Source / Category</th>
                                        <th>Potential Name</th>
                                        <th>Contact Details</th>
                                        <th>Assign To</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Updated By</th>
                                        <th>Modified Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($potentials) && count($potentials) > 0)
                                        @foreach ($potentials as $potentialIndex => $potential)
                                            <tr wire:key='item-{{ $potential->id }}'>

                                                <td>
                                                    {{ $potentialIndex + $potentials->firstItem() }}
                                                </td>

                                                <td>
                                                    {{ substr($potential?->lead?->lead_no ?? '--', 0, 4) . '...' . substr($potential?->lead?->lead_no ?? '--', -2) }}

                                                </td>

                                                <td>
                                                    {{ $potential?->lead?->leadSource?->name ?? '--' }}
                                                    <br>
                                                    <small class="fw-bold">
                                                        {{ ucfirst($potential?->lead?->category) ?? '--' }}</small>
                                                </td>

                                                <td title="{{ $potential?->lead?->name }}">
                                                    {{ strlen($potential?->lead?->name ?? '') > 10
                                                        ? substr($potential->lead->name, 0, 10) . '...'
                                                        : $potential->lead->name }}
                                                </td>

                                                <td>
                                                    <span>
                                                        {{ $potential?->lead?->leadContactPerson->name ?? '' }}
                                                    </span>
                                                    <span>{{ $potential?->lead?->leadContactPerson?->contact_number ?? '--' }}</span>
                                                    <small class="fw-bold">
                                                        <span>{{ $potential?->lead?->leadContactPerson?->email ?? '--' }}</span>
                                                    </small>
                                                </td>

                                                <td>
                                                    {{ $potential?->assignedPerson?->name ?? '--' }}
                                                </td>
                                                <td>
                                                    {{ $potential->status ?? '--' }}
                                                </td>
                                                <td>
                                                    {{ $potential?->createdBy?->name ?? '--' }}
                                                </td>
                                                <td>
                                                    {{ $potential?->updatedBy?->name ?? '--' }}
                                                </td>
                                                <td>
                                                    {{ $potential->updated_at ?? '--' }}
                                                </td>
                                                <td>
                                                    <button data-bs-toggle="dropdown" type="button"
                                                        class="btn dropdown-toggle dropdown-toggle-split"></button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('potential-follow-up', ['potentialId' => $potential->id]) }}">Follow
                                                                Up</a></li>
                                                        <li><a class="dropdown-item" data-bs-toggle="modal"
                                                                data-bs-target="#reAssignStaticModal"
                                                                wire:click="getPotentialId({{ $potential->id }})">Reassign</a>
                                                        </li>
                                                        <li><a class="dropdown-item" href="#">Edit</a></li>
                                                    </ul>
                                                </td>

                                            </tr>
                                        @endforeach
                                    @else
                                        @livewire('not-found-record-row', ['colspan' => 13])
                                    @endif
                                </tbody>
                            </table>
                        </div>


                        <div class="card-footer">
                            <div class="row d-flex flex-row mb-3">
                                @if (isset($potentials) && count($potentials) != 0)
                                    <div class="col">
                                        <div class="d-flex flex-row mb-3">
                                            <div>
                                                <label class="p-2" for="perPage">Per Page</label>
                                            </div>
                                            <div>
                                                <select class="form-select" id="perPage" name="perPage"
                                                    wire:model="perPage"
                                                    wire:change="changePageValue($potential.target.value)">
                                                    <option value=10>10</option>
                                                    <option value=50>50</option>
                                                    <option value=100>100</option>
                                                </select>

                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col d-flex justify-content-end">
                                    @if (isset($potentials) && count($potentials) >= 0)
                                        {{ $potentials->links() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-5 mb-3">
                    <div class="card" style="height: 28rem">
                        <div class="card-header ">
                            <h4>Activity Logs</h4>
                        </div>
                        <div class="card-body card-body-scrollable card-body-scrollable-shadow">
                            <div class="divide-y">
                                <div class="row">
                                    @if (isset($potentialActivities) && count($potentialActivities) > 0)
                                        <ul class="steps steps-vertical ps-5 pt-3">
                                            @foreach ($potentialActivities as $activity)
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
                                                            $newValues =
                                                                $activity->getExtraProperty('attributes') ?? [];
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
                                </div>
                                <div class="col d-flex justify-content-end">
                                    @if (isset($potentialActivities) && count($potentialActivities) >= 0)
                                        {{ $potentialActivities->links() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Reassign Sales Person Modal --}}
    <div wire:ignore.self class="modal modal-blur fade" id="reAssignStaticModal" role="dialog" aria-hidden="true"
        data-bs-backdrop='static' tabindex="-1" aria-labelledby="staticModalLabel">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticModalLabel">Re-assign </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="potentialForm" wire:submit.prevent="reAssignSalesPerson">
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

                            <!-- Event Name -->
                            <div class="col-md-4 mb-3">
                                <label for="tomselect-event" class="form-label required">Event</label>
                                <div wire:ignore>
                                    <select id="tomselect-event" class="form-control" wire:model.live="event_id">
                                        @if (!empty($events))
                                            @foreach ($events as $event)
                                                <option value="{{ $event->id }}"
                                                    wire:key="event-{{ $event->id }}">{{ $event->title }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @error('event_id')
                                    <div class="text-danger text">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Lead Type -->
                            <div class="col-md-4 mb-3">
                                <label for="lead_type" class="form-label required">Lead Type</label>
                                <div wire:ignore>
                                    <select id="lead_type" class="form-control" wire:model="lead_type">
                                        <option value="">Select Type</option>
                                        <option value="domestic" {{-- {{ $lead_type== 'domestic' ? 'Selected' : '' }} --}}>Domestic</option>
                                        <option value="international" {{-- {{ lcfirst($lead_type) == 'international' ? 'Selected' : '' }} --}}>
                                            International</option>
                                        <option value="all">ALL</option>
                                    </select>
                                </div>
                                @error('lead_type')
                                    <div class="text-danger text">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Lead Category -->
                            <div class="col-md-4 mb-3">
                                <label for="lead_category" class="form-label required">Lead Category</label>
                                <div wire:ignore>
                                    <select id="lead_category" class="form-control" wire:model.live="lead_category">
                                        <option value="">Select Category</option>
                                        <option value="agent">Agent</option>
                                        <option value="direct">Direct</option>
                                        <option value="all">ALL</option>
                                    </select>
                                </div>
                                @error('lead_category')
                                    <div class="text-danger text">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Assign From  -->
                            <div class="col-md-4 mb-3">
                                <label for="assign_id" class="form-label">Assigned Person</label>
                                <input type="text" class="form-control" id="assign_id"
                                    wire:model.live="assign_id" disabled />
                            </div>

                            <!-- Assign To -->
                            <div class="col-md-4 mb-3">
                                <label for="reassign" class="form-label required">Re-Assign Person</label>
                                <div wire:ignore>
                                    <select id="reassign" class="form-control" wire:model.live="re_assign_id">
                                        <option value="">Select Sales Person</option>
                                        @if (!empty($salesPersons))
                                            @foreach ($salesPersons as $key => $salesPerson)
                                                <option value="{{ $salesPerson->id }}">{{ $salesPerson->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @error('re_assign_id')
                                    <div class="text-danger text">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Potential List -->
                            {{-- <div class="col-md-12 mb-3">
                                <label for="potentials" class="form-label required">Potentials</label> --}}
                            <div class="col-md-12 mb-3" wire:ignore>
                                <label for="potentials" class="form-label required">Potentials</label>
                                <select id="potentials" class="form-control" wire:model="potential_Ids" multiple>
                                    <option value="">Select Potentials</option>
                                    @if (!empty($potentialList))
                                        @foreach ($potentialList as $potential)
                                            <option value="{{ $potential->id }}">
                                                {{ $potential?->lead?->name ?? '--' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('potential_Ids')
                                    <div class="text-danger text">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- </div>
                        </div> --}}

                            <!-- Form Footer -->
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">
                                    Save
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
    <script>
        document.addEventListener('livewire:initialized', function() {
            var reassign = new TomSelect('#reassign', {
                plugins: ['dropdown_input'],
            });
            var event = new TomSelect('#tomselect-event', {
                plugins: ['dropdown_input'],
            });
            var potentials = new TomSelect('#potentials', {
                plugins: ['dropdown_input', 'remove_button'],
            });

            // Livewire.hook('message.processed', (message, component) => {
            //     const selectElement = document.getElementById('potentials');
            //     if (selectElement) {
            //         new TomSelect(selectElement, {

            //             plugins: ['dropdown_input'],
            //         });
            //     }
            // });
            const selectElement = document.getElementById('potentials');
            if (selectElement) {
                selectElement.addEventListener('change', function() {
                    const selectedValues = Array.from(selectElement.selectedOptions).map(option => option
                        .value);
                    Livewire.emit('updatePotentialIds', selectedValues);
                });
            }
        });
    </script>
@endpush
