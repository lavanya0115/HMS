<div>
    <div class="page-body">
        <div class="container-xl">
            @include('includes.alerts')
            <div class="row">

                <div class="col-lg-12">
                    <div class="d-flex flex-row justify-content-between align-items-center">
                        <div>
                            <h4>List all Seminars</h4>
                        </div>
                    </div>
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>seminar title</th>
                                        <th>Seminar Dates</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th> Delegates Count</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($seminars) && count($seminars) > 0)
                                        @foreach ($seminars as $seminarIndex => $seminar)
                                            <tr wire:key='item-{{ $seminar['id'] }}'>
                                                <td>
                                                    {{ $seminarIndex + 1 }}
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $seminar['title'] ?? '' }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $seminar['date'] ?? '' }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $seminar['start_time'] ?? '' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $seminar['end_time'] ?? '' }}</div>
                                                </td>
                                                <td>
                                                    <div>{{ $seminar['delegates_count'] ?? 0 }}</div>
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
    </div>
</div>
