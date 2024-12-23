<div class="page-body">
    <div class="container-xl">
        <div class="row row-deck row-cards">
            <div class="d-flex justify-content-between">
                <h3 class="fw-bold">{{ $type }} Campaign Details</h3>
                <a href={{ route('zoho-campaign') }} class="btn me-3">
                    Back </a>
            </div>
            <h6 class="fw-bold fs-4">{{ $campaignName }}</h6>
            @include('includes.alerts')
            <div class="col-12">
                <div class="row row-cards">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="fw-bold">
                                            Compaign status
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="text-secondary">
                                            <span class="badge bg-primary text-primary-fg ms-auto">
                                                {{ $campaignDatas['campaign_status'] ?? 'sent' }}
                                            </span>
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
                                        <div class="fw-bold">
                                            Subscribers count
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="text-secondary">
                                            <span class="badge bg-green text-primary-fg ms-auto">
                                                {{ $campaignDatas['total_subscribers_count'] ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-12">
                {{-- <div class="row row-cards">
                    <div class="col-sm-6 col-lg-12">
                        <div class="card card-sm">
                            <div class="card-body">
                                <p class="mb-3 fw-bold">Campaign Reach</p>
                                @if (!empty($campaignReach))
                                    @foreach ($campaignReach as $campaignReach)
                                        <div class="progress progress-separated mb-3">
                                            @foreach ($campaignReach as $key => $value)
                                                @php
                                                    $colorClass = $this->colorMap($key) ?? 'bg-secondary';
                                                @endphp
                                                <div class="progress-bar {{ $colorClass }}" role="progressbar"
                                                    style="width: {{ $value }}%;"
                                                    aria-label="{{ $key }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="{{ $key }}: {{ $value }}">
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="row">
                                            @foreach ($campaignReach as $key => $value)
                                                @php
                                                    $colorClass = $this->colorMap($key) ?? 'bg-secondary';
                                                @endphp
                                                <div class="col-auto d-flex align-items-center pe-2">
                                                    <span class="legend me-2 {{ $colorClass }}"></span>
                                                    <span>{{ $key }}</span>
                                                    <span
                                                        class="d-none d-md-inline d-lg-none d-xxl-inline ms-2 text-secondary">{{ $value }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-danger fw-bold">Campaign not define</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div> --}}
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h3>Total Reach </h3>
                            {{-- <small class="fw-bold">Emails</small> --}}
                            <div id="emails-percentage"></div>
                        </div>
                    </div>
                </div>
                <div class="container col-8">
                    {{-- @dump($campaignReportCountData, $campaignReportPercentData) --}}
                    <div class="row mb-2">
                        <div class="col-sm">
                            <div class="card">
                                <div class="card-body">
                                    <h3><strong> Opened</strong></h3>
                                    <small>Count
                                        <span class="badge bg-primary text-primary-fg ms-auto">
                                            {{ $campaignReportCountData['opens_count'] }}
                                        </span>
                                    </small><br>
                                    <small
                                        class="align-items-center fw-bold">{{ $campaignReportPercentData['open_percent'] }}
                                        %</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm">
                            <div class="card">
                                <div class="card-body">
                                    <h3><strong> Unopened</strong></h3>
                                    <small>Count
                                        <span class="badge bg-primary text-primary-fg ms-auto">
                                            {{ $campaignReportCountData['unopened'] }}
                                        </span>
                                    </small><br>
                                    <small
                                        class="align-items-center fw-bold">{{ $campaignReportPercentData['unopened_percent'] }}
                                        %</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm">
                            <div class="card">
                                <div class="card-body">
                                    <h3><strong> Spams</strong></h3>
                                    <small>Count
                                        <span class="badge bg-primary text-primary-fg ms-auto">
                                            {{ $campaignReportCountData['spams_count'] }}
                                        </span>
                                    </small><br>
                                    <small
                                        class="align-items-center fw-bold">{{ $campaignReportPercentData['spam_percent'] }}
                                        %</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm">
                            <div class="card">
                                <div class="card-body">
                                    <h3><strong> Unique Clicks</strong></h3>
                                    <small>Count
                                        <span class="badge bg-primary text-primary-fg ms-auto">
                                            {{ $campaignReportCountData['unique_clicks_count'] }}
                                        </span>
                                    </small><br>
                                    <small
                                        class="align-items-center fw-bold">{{ $campaignReportPercentData['unique_clicked_percent'] }}
                                        %</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="card">
                                <div class="card-body">
                                    <h3><strong> Delivered</strong></h3>
                                    <small>Count
                                        <span class="badge bg-primary text-primary-fg ms-auto">
                                            {{ $campaignReportCountData['delivered_count'] }}
                                        </span>
                                    </small><br>
                                    <small
                                        class="align-items-center fw-bold">{{ $campaignReportPercentData['delivered_percent'] }}
                                        %</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="card">
                                <div class="card-body">
                                    <h3><strong> Unsent</strong></h3>
                                    <small>Count
                                        <span class="badge bg-primary text-primary-fg ms-auto">
                                            {{ $campaignReportCountData['unsent_count'] }}
                                        </span>
                                    </small><br>
                                    <small
                                        class="align-items-center fw-bold">{{ $campaignReportPercentData['unsent_percent'] }}
                                        %</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm">
                            <div class="card">
                                <div class="card-body">
                                    <h3><strong> Bounces</strong></h3>
                                    <small>Count
                                        <span class="badge bg-primary text-primary-fg ms-auto">
                                            {{ $campaignReportCountData['bounces_count'] }}
                                        </span>
                                    </small><br>
                                    <small
                                        class="align-items-center fw-bold">{{ $campaignReportPercentData['bounce_percent'] }}
                                        %</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="card">
                                <div class="card-body">
                                    <h3><strong> Un Subscribes</strong></h3>
                                    <small>Count
                                        <span class="badge bg-primary text-primary-fg ms-auto">
                                            {{ $campaignReportCountData['unsub_count'] }}
                                        </span>
                                    </small><br>
                                    <small
                                        class="align-items-center fw-bold">{{ $campaignReportPercentData['unsubscribe_percent'] }}
                                        %</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="card">
                                <div class="card-body">
                                    <h3><strong> Complaints</strong></h3>
                                    <small>Count
                                        <span class="badge bg-primary text-primary-fg ms-auto">
                                            {{ $campaignReportCountData['complaints_count'] }}
                                        </span>
                                    </small><br>
                                    <small
                                        class="align-items-center fw-bold">{{ $campaignReportPercentData['complaints_percent'] }}
                                        %</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">

                <div class="col-lg-6 ms-2 ">
                    <div class="card">
                        <div class="card-body">
                            <h3>Compaign Location Data</h3>
                            <canvas id="campaign_locations"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 ms-2 col-xl-4 ms-5">
                    <div class="card">
                        <div class="card-body">
                            <canvas id="campaign-location-india"></canvas>
                        </div>
                    </div>
                </div>

            </div>

            {{-- <div class="col-lg-6 col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h3>Compaign Location Data</h3>
                        <canvas id=""></canvas>
                    </div>
                </div>
            </div> --}}
            @if (isset($campaignMailList) && !empty($campaignMailList))
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">Campaign Mail List Details</h3>
                        </div>
                        <div class="card-table table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>no of bounce</th>
                                        <th>contacts count</th>
                                        <th>list status</th>
                                        <th>list name</th>
                                        <th>no of unsubcontacts</th>
                                    </tr>
                                </thead>
                                @if (isset($campaignMailList) && !empty($campaignMailList))
                                    @foreach ($campaignMailList as $key => $campaignList)
                                        <tr wire:key={{ $key }}>
                                            <td class="text-secondary"> {{ $key + 1 }}
                                            </td>
                                            <td class="text-secondary">{{ $campaignList['no_of_bounce'] }}</td>
                                            <td class="text-secondary">{{ $campaignList['contactscount'] }}</td>
                                            <td class="text-secondary">{{ $campaignList['liststatus'] }}</td>
                                            <td class="text-secondary">{{ $campaignList['listname'] }}</td>
                                            <td class="text-secondary">{{ $campaignList['no_of_unsubcontacts'] }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center fw-bold text-danger ">No Records Found
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col d-flex justify-content-end mt-2">
                            @if (!empty($campaignMailList))
                                {{ $campaignMailList->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            {{-- <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title fw-bold">Campaign Location Details</h3>
                    </div>
                    <div class="card-table table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>country</th>
                                    <th>counts</th>
                                </tr>
                            </thead>
                            @if (isset($campaignLocation) && !empty($campaignLocation))
                                @foreach ($campaignLocation as $key => $value)
                                    @php

                                        $country = $key;
                                        if ($type != 'Last') {
                                            $country = $this->countryMap($key);
                                        }
                                        // dd($key, $type, $country);
                                    @endphp
                                    @dd($value, $key, $campaignLocation)
                                    <tr wire:key={{ $key }}>
                                        <td class="text-secondary">{{ $country }}</td>
                                        <td class="text-secondary"><span
                                                class="badge bg-primary text-primary-fg ms-auto">
                                                {{ $value }}
                                            </span></td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center fw-bold text-danger ">No Records Found</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>F
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const ctx = document.getElementById('campaign-reports-count');
        var countData = @json(array_values($campaignReportCountData));
        var countLabels = @json(array_keys($campaignReportCountData));
        var locationIndiaData = @json(array_values($locationIndiaData));
        const desiredLabels = ['delivered_percent', 'open_percent', 'unopened_percent', 'spam_percent'];
        const filteredData = [];
        const filteredLabels = [];

        var percentData = @json(array_values($campaignReportPercentData));
        var percentLabels = @json(array_keys($campaignReportPercentData));
        console.log(percentData, percentLabels, filteredData, filteredLabels);
        for (let i = 0; i < percentLabels.length; i++) {
            if (desiredLabels.includes(percentLabels[i])) {
                filteredLabels.push(percentLabels[i]);
                filteredData.push(percentData[i]);
            }
        }

        var colors = [
            'rgba(139, 0, 0, 0.8)',
            'rgba(0, 0, 139, 0.8)',
            'rgba(184, 134, 11, 0.8)',
            'rgba(0, 100, 0, 0.8)',
            'rgba(75, 0, 130, 0.8)',
            'rgba(210, 105, 30, 0.8)',
            'rgba(135, 0, 0, 0.8)',
            'rgba(0, 0, 139, 0.8)',
            'rgba(184, 134, 11, 0.8)',
            'rgba(0, 100, 0, 0.8)',
            'rgba(75, 0, 130, 0.8)',
            'rgba(210, 105, 30, 0.8)',
            'rgba(139, 0, 0, 0.8)',
            'rgba(0, 0, 139, 0.8)'
        ];

        var campaignLocationLabels = @json($campaignLocationLabels ?? []);
        var campaignLocationSeries = @json($campaignLocationSeries ?? []);

        // new Chart(ctx, {
        //     type: 'bar',
        //     data: {
        //         labels: countLabels,
        //         datasets: [{
        //             label: 'counts',
        //             data: countData,
        //             borderWidth: 2,
        //             backgroundColor: colors.slice(0, countLabels.length),
        //         }]
        //     },
        //     options: {
        //         scales: {
        //             y: {
        //                 beginAtZero: true
        //             }
        //         }
        //     }
        // });
        console.log(locationIndiaData);

        const ctx2 = document.getElementById('campaign-location-india').getContext('2d');
        const data = {
            labels: [
                'Location Data',
            ],
            datasets: [{
                label: 'India',
                data: locationIndiaData,
                backgroundColor: [
                    'rgba(255, 219, 88, 0.8)',
                ],
                hoverOffset: 2
            }]
        };
        new Chart(ctx2, {
            type: 'doughnut',
            data: data,
        });

        const ctx1 = document.getElementById('campaign_locations');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: campaignLocationLabels,
                datasets: [{
                    label: 'Locations',
                    data: campaignLocationSeries,
                    borderWidth: 2,
                    backgroundColor: [
                        'rgba(54, 162, 235, 2)',
                        'rgba(255, 99, 132, 2)',
                        'rgba(255, 206, 86, 2)',
                        'rgba(75, 192, 192, 2)',
                        'rgba(153, 102, 255, 2)',
                        'rgba(255, 159, 64, 2)',
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 5)',
                        'rgba(255, 99, 132, 5)',
                        'rgba(255, 206, 86, 5)',
                        'rgba(75, 192, 192, 5)',
                        'rgba(153, 102, 255, 5)',
                        'rgba(255, 159, 64, 5)',
                    ],
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const chart = new ApexCharts(document.getElementById('emails-percentage'), {
            chart: {
                type: "radialBar",
                fontFamily: 'inherit',
                height: 240,
                animations: {
                    enabled: true
                },
                sparkline: {
                    enabled: true
                },
            },
            plotOptions: {
                radialBar: {
                    dataLabels: {
                        total: {
                            show: true,
                            label: 'Total Emails',
                            formatter: function(val, opts) {
                                const index = countLabels.indexOf('emails_sent_count');
                                if (index !== -1) {
                                    return `${countData[index]}`;
                                }

                            },
                        },
                    },
                },
            },
            fill: {
                opacity: 1,
            },
            stroke: {
                width: 2,
                lineCap: "round",
                curve: "smooth",
            },
            series: filteredData,
            labels: ['Delivered', 'Open', 'Un Opened', 'Spam'],
            tooltip: {
                theme: 'dark'
            },
            grid: {
                strokeDashArray: 4,
            },
            colors: colors, // Assuming colors is your color scheme
            legend: {
                show: false,
            },
        });
        chart.render();


        // const ctx3 = document.getElementById('campaign_locations');
        // new Chart(ctx3, {
        //     type: 'doughnut',
        //     data: {
        //         labels: campaignLocationLabels,
        //         datasets: [{
        //             label: 'Campaign Locations',
        //             data: campaignLocationSeries,
        //             borderWidth: 1,
        //
        //         }]
        //     },
        //     options: {
        //         // scales: {
        //         //     y: {
        //         //         beginAtZero: true
        //         //     }
        //         // },
        //         plugins: {
        //             legend: {
        //                 display: true,
        //                 position: 'bottom'
        //             }
        //         },
        //         animation: {
        //             duration: 0
        //         }
        //     }
        // });
    </script>
@endpush
