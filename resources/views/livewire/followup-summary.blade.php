<div>
    <div class="page-body">
        <div class="container-xl">
            @include('includes.alerts')
            <div class="row">

                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4>List Follow up History</h4>
                        </div>
                        <div class="mb-2">
                            <a class="btn btn-warning" href="#"> Back To Follow Up </a>
                            <a class="btn btn-secondary" href="{{ route('potential-summary') }}"> Back To Potential List
                            </a>
                        </div>
                    </div>
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Status</th>
                                        <th>Activity type</th>
                                        <th>Contact Mode</th>
                                        <th>Remarks</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($followups) && count($followups) > 0)
                                        @foreach ($followups as $index => $followup)
                                            <tr wire:key='item-{{ $followup->id }}'>
                                                <td>
                                                    {{ $index + $followups->firstItem() }}
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">
                                                        {{ $followup->status }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">
                                                        {{ $followup->activity_type }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">
                                                        {{ $followup->contact_mode }}</div>
                                                </td>

                                                <td>
                                                    <div class="text-capitalize">{{ $followup->remarks }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        {{-- @can('Update Followup') --}}
                                                        <a href="#">
                                                            <span>@include('icons.edit')</span>
                                                        </a>
                                                        {{-- @endcan --}}
                                                        {{-- @can('Delete Followup') --}}
                                                        <a type="button"
                                                            wire:confirm="Are you sure you want to delete this Follow-up?"
                                                            wire:click="deleteFollowup({{ $followup->id }})"
                                                            class="text-danger" style="cursor:pointer">
                                                            <span>@include('icons.trash')</span>
                                                        </a>
                                                        {{-- @endcan --}}
                                                    </div>
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
                            <div class="row d-flex flex-row mb-3">
                                @if (isset($followups) && count($followups) != 0)
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
                                    @if (isset($followups) && count($followups) >= 0)
                                        {{ $followups->links() }}
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
