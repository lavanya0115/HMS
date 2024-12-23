@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush
<div class="page-body">
    <div class="container-xl">
        @include('includes.alerts')
        <div>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="d-flex gap-2">
                        <h3>List of Leads</h3>
                        <span wire:click="toggleFilter" style="cursor:pointer;" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Filter">@include('icons.filter-search')</span>
                    </div>
                    <div class="subheader">Leads Count (Total-{{ $leadsTotalCount }})</div>
                </div>
                <div>
                    <a href="{{ route('import.leads') }}" class="btn me-1">@include('icons.file-export') Import</a>
                    {{-- @can('Create Lead') --}}
                    <a href="{{ route('upsert.lead') }}" class="btn btn-primary ">@include('icons.plus') New
                        Lead</a>
                    {{-- @endcan --}}
                </div>
            </div>
            <div class="pb-3 {{ !$showFilter ? 'd-none' : '' }}">
                <div class="card">
                    <div class="card-body">
                        <div class="row col-md-12">
                            <div class="col-md-4 mb-2">
                                <select class="form-select" wire:model="leadType">
                                    <option value="">Select Lead Type</option>
                                    <option value="domestic">Domestic</option>
                                    <option value="international">International</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <select class="form-select" wire:model="leadCategory">
                                    <option value="">Select Lead Category</option>
                                    <option value="agent">Agent</option>
                                    <option value="direct">Direct</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div wire:ignore>
                                    <select id="country" class="form-select" wire:model="leadCountry">
                                        <option value="">Select Country</option>
                                        @foreach (array_keys($countries) as $countryName)
                                            <option value="{{ $countryName }}">{{ $countryName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" wire:model="source">
                                    <option value="">Select Lead Source</option>
                                    @foreach ($leadSources as $leadSource)
                                        <option value="{{ $leadSource->id }}">{{ $leadSource->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" wire:model="search"
                                    placeholder="Search name, lead no...">
                            </div>
                            <div class="col-md-4">
                                <div wire:ignore>
                                    <select id="categories" class="form-select" wire:model="productCategory">
                                        <option value="">Select Product Category</option>
                                        @foreach ($productCategories as $categoryId => $categoryName)
                                            <option value="{{ $categoryId }}">{{ $categoryName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="#" wire:click="getFilteredLeads" class="btn btn-primary">Filter</a>
                            <a href="{{ route('leads.summary') }}" class="btn btn-secondary ms-2">Reset</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap table-striped datatable fs-5">
                    <thead>
                        <tr>
                            <th class="w-1"></th>
                            <th>#</th>
                            <th>Type</th>
                            <th>Source</th>
                            <th>Category</th>
                            <th>Lead No.
                                <span wire:click.prevent="sortColumn('lead_no','asc')" style="cursor:pointer;"
                                    data-toggle="tooltip" data-placement="top" title="Sort Ascending">
                                    @include('icons.arrow-narrow-up')
                                </span>
                                <span wire:click.prevent="sortColumn('lead_no','desc')" style="cursor:pointer;"
                                    data-toggle="tooltip" data-placement="top" title="Sort Descending">
                                    @include('icons.arrow-narrow-down')
                                </span>
                            </th>
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
                            <th>Country</th>
                            <th>Contact Name</th>
                            <th>Contact No.</th>
                            <th>Contact Email</th>
                            <th>GST</th>
                            <th>Product Category</th>
                            <th>Products</th>
                            <th>Created At
                                <span wire:click.prevent="sortColumn('created_at','asc')" style="cursor:pointer;"
                                    data-toggle="tooltip" data-placement="top" title="Sort Ascending">
                                    @include('icons.arrow-narrow-up')
                                </span>
                                <span wire:click.prevent="sortColumn('created_at','desc')" style="cursor:pointer;"
                                    data-toggle="tooltip" data-placement="top" title="Sort Descending">
                                    @include('icons.arrow-narrow-down')
                                </span>
                            </th>
                            <th>Created By</th>
                            <th>Updated At
                                <span wire:click.prevent="sortColumn('updated_at','asc')" style="cursor:pointer;"
                                    data-toggle="tooltip" data-placement="top" title="Sort Ascending">
                                    @include('icons.arrow-narrow-up')
                                </span>
                                <span wire:click.prevent="sortColumn('updated_at','desc')" style="cursor:pointer;"
                                    data-toggle="tooltip" data-placement="top" title="Sort Descending">
                                    @include('icons.arrow-narrow-down')
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($leads) && count($leads) > 0)
                            @foreach ($leads as $leadIndex => $lead)
                                <tr wire:key='item-{{ $lead['id'] }}'>
                                    <td>
                                        <div class="btn-group">
                                            {{-- @can('Edit Lead') --}}
                                            <a
                                                href="{{ route('upsert.lead', ['leadId' => $lead['id']]) }}">@include('icons.edit')</a>
                                            {{-- @endcan --}}
                                            {{-- @can('Delete Announcement')
                                                <a href="#" class="text-danger ms-2"
                                                    wire:confirm="Are you sure you want to delete this announcement?"
                                                    wire:click='delete({{ $announcement->id }})'>@include('icons.trash')</a>
                                            @endcan --}}
                                        </div>
                                    </td>
                                    <td>
                                        {{ $leadIndex + $leads->firstItem() }}
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $lead['type'] }}</div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $lead['source'] }}</div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $lead['category'] }}</div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $lead['lead_no'] }}</div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $lead['name'] }}</div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $lead['country'] }}</div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $lead['contact_person'] }}</div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $lead['contact_no'] }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $lead['contact_email'] }}</div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $lead['gst_no'] }}</div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $lead['categories'] }}</div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $lead['products'] }}</div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $lead['created_at'] }}</div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $lead['created_by'] }}</div>
                                    </td>
                                    <td>
                                        <div class="text-capitalize">{{ $lead['updated_at'] }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        @if (isset($leads) && count($leads) == 0)
                            @livewire('not-found-record-row', ['colspan' => 17])
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-end">
                    {{ $leads->links() }}
                </div>
            </div>
        </div>

        <div class="col-12 mt-5 mb-3">
            <div class="card" style="height: 28rem">
                <div class="card-header ">
                    <h4>Activity Logs</h4>
                </div>
                <div class="card-body card-body-scrollable card-body-scrollable-shadow">
                    <div class="row">
                        @if (isset($leadActivities) && count($leadActivities) > 0)
                            <ul class="steps steps-vertical ps-5 pt-3">
                                @foreach ($leadActivities as $activity)
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

                        <div class="col d-flex justify-content-end">
                            @if (isset($leadActivities) && count($leadActivities) >= 0)
                                {{ $leadActivities->links() }}
                            @endif
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
            var country = new TomSelect('#country', {
                plugins: ['dropdown_input', 'remove_button'],
            });
            var productCategories = new TomSelect('#categories', {
                plugins: ['dropdown_input', 'remove_button'],
            });
        });
    </script>
@endpush
