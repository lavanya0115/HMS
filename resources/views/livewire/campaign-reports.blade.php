<div class="page-body">
    <div class="container-xl">
        <div class="col-md-12 pt-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title fw-bold"> Last Campaign Details</h3>
                </div>
                <div class="card-table table-responsive">
                    <table class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>sent date</th>
                                <th>sender name</th>
                                <th>campaign name</th>
                                <th>campaign type</th>
                                <th>email subject</th>
                            </tr>
                        </thead>
                        @if (isset($lastCampaignDetails) && !empty($lastCampaignDetails))
                            @foreach ($lastCampaignDetails as $key => $campaign)
                                <tr wire:key="{{ $key }}" wire:click.prevent="gotoZohoCampaignReport('Last')"
                                    style="cursor: pointer;">
                                    <td class="text-secondary">{{ $campaign['sent_date'] }}</td>
                                    <td class="text-secondary">{{ $campaign['sender_name'] }}</td>
                                    <td class="text-secondary">{{ $campaign['campaign_name'] }}</td>
                                    <td class="text-secondary">{{ $campaign['campaigntype'] }}</td>
                                    <td class="text-secondary">{{ $campaign['email_subject'] }}</td>
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
        </div>
        <div class="col-md-12 pt-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title fw-bold"> Recent Campaign Details</h3>
                </div>
                <div class="card-table table-responsive">
                    <table class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>sent date</th>
                                <th>sender name</th>
                                <th>campaign name</th>
                                <th>campaign type</th>
                                <th>email subject</th>
                                <th>campaign status</th>
                            </tr>
                        </thead>
                        @if (isset($recentCampaigns) && !empty($recentCampaigns))
                            @foreach ($recentCampaigns as $key => $campaign)
                                <tr wire:key="{{ $key }}"
                                    @if ($campaign['campaign_status'] != 'Draft') wire:click.prevent="getCampaignDetails('{{ $campaign['campaign_key'] }}', 'Recent')"
                                    style="cursor: pointer;" @endif>
                                    <td class="text-secondary">{{ $campaign['created_date_string'] ?? '' }}</td>
                                    <td class="text-secondary">{{ $campaign['sender_name'] ?? 'Medicall' }}</td>
                                    <td class="text-secondary">{{ $campaign['campaign_name'] ?? '' }}</td>
                                    <td class="text-secondary">{{ $campaign['campaigntype'] ?? '' }}</td>
                                    <td class="text-secondary">{{ $campaign['subject'] ?? '' }}</td>
                                    <td class="text-secondary">
                                        <span
                                            class="badge text-primary-fg ms-auto {{ $campaign['campaign_status'] == 'Draft' ? 'bg-secondary' : 'bg-success' }}">
                                            {{ $campaign['campaign_status'] ?? '-' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center fw-bold text-danger ">No Records Found</td>
                            </tr>
                        @endif
                    </table>
                </div>
                @if (!empty($recentCampaigns))
                    <div class="card-footer">
                        <div class="d-flex justify-content-end">
                            {{ $recentCampaigns->links() }}
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
