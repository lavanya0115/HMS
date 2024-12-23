<div class="page-body">
    <div class="container-xl">
        @include('includes.alerts')
        <div class="row justify-content-end">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Select a date" id="daterange" name="daterange">
            </div>
        </div>
        <div class="row row-deck row-cards pt-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title fw-bold">Last Seven Days</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter text-nowrap datatable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Visitors</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($lastSevenDaysVisitors['lastSevenDays']) && count($lastSevenDaysVisitors['lastSevenDays']) > 0)
                                        @foreach ($lastSevenDaysVisitors['lastSevenDays'] as $date)
                                            <tr>
                                                <td class="text-capitalize">{{ $date['date'] }}</td>
                                                <td>{{ $date['count'] }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light">
                                            <td class="fw-bold">Grand Total</td>
                                            <td class="fw-bold">{{ $lastSevenDaysVisitors['total'] }}</td>
                                        </tr>
                                    @else
                                        @livewire('not-found-record-row', ['colspan' => 2])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title fw-bold">Registration Typewise Counts</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter text-nowrap datatable">
                                <thead>
                                    <tr>
                                        <th>Registration Type</th>
                                        <th>Visitors</th>
                                        <th>ON {{ now()->format('d M') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($registrationTypeCounts['registrationTypes']) && count($registrationTypeCounts['registrationTypes']) > 0)
                                        @foreach ($registrationTypeCounts['registrationTypes'] as $type => $registrationType)
                                            <tr>
                                                <td class="text-capitalize">{{ $type }}</td>
                                                <td>{{ $registrationType['total'] }}</td>
                                                <td>{{ $registrationType['todayCount'] }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light">
                                            <td class="fw-bold">Grand Total</td>
                                            <td class="fw-bold">{{ $registrationTypeCounts['overAllCount'] }}</td>
                                            <td class="fw-bold">{{ $registrationTypeCounts['overAllTodayCount'] }}</td>
                                        </tr>
                                    @else
                                        @livewire('not-found-record-row', ['colspan' => 3])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-deck row-cards pt-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="card-title fw-bold">Top 5 Cities</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter text-nowrap datatable">
                                <thead>
                                    <tr>
                                        <th>City</th>
                                        <th>Visitors</th>
                                        <th>ON {{ now()->format('d M') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($top5Cities['top5Locations']) && count($top5Cities['top5Locations']) > 0)
                                        @foreach ($top5Cities['top5Locations'] as $cities)
                                            <tr>
                                                <td class="text-capitalize">{{ $cities->city }}</td>
                                                <td>{{ $cities->total }}</td>
                                                <td>{{ $cities->today_count }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light">
                                            <td class="fw-bold">Grand Total</td>
                                            <td class="fw-bold">{{ $top5Cities['overAllCount'] }}</td>
                                            <td class="fw-bold">{{ $top5Cities['overAllTodayCount'] }}</td>
                                        </tr>
                                    @else
                                        @livewire('not-found-record-row', ['colspan' => 3])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="card-title fw-bold">Top 5 States</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter text-nowrap datatable">
                                <thead>
                                    <tr>
                                        <th>State</th>
                                        <th>Visitors</th>
                                        <th>ON {{ now()->format('d M') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($top5States['top5Locations']) && count($top5States['top5Locations']) > 0)
                                        @foreach ($top5States['top5Locations'] as $states)
                                            <tr>
                                                <td class="text-capitalize">{{ $states->state }}</td>
                                                <td>{{ $states->total }}</td>
                                                <td>{{ $states->today_count }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light">
                                            <td class="fw-bold">Grand Total</td>
                                            <td class="fw-bold">{{ $top5States['overAllCount'] }}</td>
                                            <td class="fw-bold">{{ $top5States['overAllTodayCount'] }}</td>
                                        </tr>
                                    @else
                                        @livewire('not-found-record-row', ['colspan' => 3])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="card-title fw-bold">Top 5 Countries</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter text-nowrap datatable">
                                <thead>
                                    <tr>
                                        <th>Country</th>
                                        <th>Visitors</th>
                                        <th>ON {{ now()->format('d M') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($top5Countries['top5Locations']) && count($top5Countries['top5Locations']) > 0)
                                        @foreach ($top5Countries['top5Locations'] as $countries)
                                            <tr>
                                                <td class="text-capitalize">{{ $countries->country }}</td>
                                                <td>{{ $countries->total }}</td>
                                                <td>{{ $countries->today_count }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light">
                                            <td class="fw-bold">Grand Total</td>
                                            <td class="fw-bold">{{ $top5Countries['overAllCount'] }}</td>
                                            <td class="fw-bold">{{ $top5Countries['overAllTodayCount'] }}</td>
                                        </tr>
                                    @else
                                        @livewire('not-found-record-row', ['colspan' => 3])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-deck row-cards pt-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="card-title fw-bold">Business-type-wise counts</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter text-nowrap datatable">
                                <thead>
                                    <tr>
                                        <th>Profile</th>
                                        <th>Visitors</th>
                                        <th>ON {{ now()->format('d M') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($businessTypeCounts['businessTypes']) && count($businessTypeCounts['businessTypes']) > 0)
                                        @foreach ($businessTypeCounts['businessTypes'] as $businessType)
                                            <tr>
                                                <td class="text-capitalize">{{ $businessType->name ?? 'N/A' }}
                                                </td>
                                                <td>{{ $businessType->total }}</td>
                                                <td>{{ $businessType->today_count }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light">
                                            <td class="fw-bold">Grand Total</td>
                                            <td class="fw-bold">{{ $businessTypeCounts['overAllCount'] }}</td>
                                            <td class="fw-bold">{{ $businessTypeCounts['overAllTodayCount'] }}</td>
                                        </tr>
                                    @else
                                        @livewire('not-found-record-row', ['colspan' => 3])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="card-title fw-bold">Known Source</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter text-nowrap datatable">
                                <thead>
                                    <tr>
                                        <th>Known Source</th>
                                        <th>Visitors</th>
                                        <th>ON {{ now()->format('d M') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($knownSourceCounts['knownSources']) && count($knownSourceCounts['knownSources']) > 0)
                                        @foreach ($knownSourceCounts['knownSources'] as $knownSource)
                                            <tr>
                                                <td class="text-capitalize">{{ $knownSource->known_source }}</td>
                                                <td>{{ $knownSource->total }}</td>
                                                <td>{{ $knownSource->today_count }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light">
                                            <td class="fw-bold">Grand Total</td>
                                            <td class="fw-bold">{{ $knownSourceCounts['overAllCount'] }}</td>
                                            <td class="fw-bold">{{ $knownSourceCounts['overAllTodayCount'] }}</td>
                                        </tr>
                                    @else
                                        @livewire('not-found-record-row', ['colspan' => 3])
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
@push('scripts')
    <script src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script>
        $(function() {
            $('input[name="daterange"]').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'DD-MM-YYYY'
                },
                opens: 'left'
            });

            var startDate;
            var endDate;
            $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
                    'DD-MM-YYYY'));
                startDate = picker.startDate.format('YYYY-MM-DD');
                endDate = picker.endDate.format('YYYY-MM-DD');
                @this.call('dateRangeFilter', startDate, endDate);
            });

            $('input[name="daterange"]').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                startDate = null;
                endDate = null;
                @this.call('dateRangeFilter', startDate, endDate);
            });

        });
    </script>
@endpush
