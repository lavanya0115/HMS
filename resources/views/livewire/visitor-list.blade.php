<div>
    <div class="page-header">
        <div class="container-fluid">
            <div class="col">
                <h2 class="page-title">
                    My Visitors
                </h2>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @include('includes.alerts')
            <div class="row row-cards">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Mobile No</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($visitors->isEmpty())
                                        <tr>
                                            <td colspan="3">No visitors available.</td>
                                        </tr>
                                    @else
                                        @foreach ($visitors as $visitor)
                                            <tr>
                                                <td>{{ $visitor->name }}</td>
                                                <td>{{ $visitor->mobile_number }}</td>
                                                <td>
                                                    <span title="Make Call" data-toggle="tooltip" data-placement="top"
                                                        wire:click="makeOutboundCall('{{ $agentNumber }}', '{{ $customerNumber }}')"
                                                        class="make-call">
                                                        @include('icons.phone-outgoing')
                                                    </span>
                                                    <span title="Register for Event" data-toggle="tooltip"
                                                        data-placement="top"
                                                        wire:click="registerVisitorForEvent({{ $visitor->id }})"
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
                                {{ $visitors->links() }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
