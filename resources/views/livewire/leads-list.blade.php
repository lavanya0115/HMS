<div>
    <div class="page-header">
        <div class="container-fluid">
            <div class="col">
                <h2 class="page-title">Leads List</h2>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @include('includes.alerts')
            <div class="row row-cards">
                <div class="col-lg-8">
                    <div class="row mb-3">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select wire:model.live.debounce.300ms="event_id" id="events" class="form-control">
                                    <option value="">All Events</option>
                                    @foreach ($previousEvents as $event)
                                        <option value="{{ $event->id }}">{{ $event->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input wire:model.live.debounce.300ms="search" type="search" id="search"
                                    class="form-control" placeholder="Search City...">
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-secondary" wire:click="resetFilters">Reset</button>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <!-- Header content -->
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Mobile No</th>
                                            <th>City</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($leads->isEmpty())
                                            <tr>
                                                <td colspan="4">No Leads available.</td>
                                            </tr>
                                        @else
                                            @foreach ($leads as $lead)
                                                <tr>
                                                    <td>{{ $lead->name }}</td>
                                                    <td>{{ $lead->mobile_number }}</td>
                                                    <td>{{ optional($lead->address)->city }}</td>
                                                    <td>
                                                        <span title="Make Call" data-toggle="tooltip"
                                                            data-placement="top"
                                                            wire:click="makeOutboundCall('{{ $agentNumber }}', '{{ $lead->mobile_number }}')"
                                                            class="make-call">
                                                            @include('icons.phone-outgoing')
                                                        </span>
                                                        <span title="Register for Event" data-toggle="tooltip"
                                                            data-placement="top"
                                                            wire:click="registerVisitorForEvent({{ $lead->id }})"
                                                            wire:confirm="Are you sure you want to register this visitor for the event?">
                                                            @include('icons.registered')
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
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
                    </div>
                </div>
            </div>
        </div>
    </div>
