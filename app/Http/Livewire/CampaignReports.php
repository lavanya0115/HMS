<?php

namespace App\Http\Livewire;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;
use Livewire\Component;

class CampaignReports extends Component
{
    use WithPagination;
    public function gotoZohoCampaignReport($type)
    {
        return redirect()->route('zoho-reports', ['type' => $type]);
    }
    public function getCampaignDetails($key, $type)
    {
        return redirect()->route('zoho-reports', ['type' => $type, 'key' => $key]);
    }
    public function render()
    {
        $lastCampaignDatas = getLastCampaignReport();
        $recentCampaignReports = getRecentCampaignReport();
        $lastCampaignDetails = [];
        if (!empty($lastCampaignDatas) && isset($lastCampaignDatas['campaign-details'])) {
            $lastCampaignDetails = $lastCampaignDatas['campaign-details'];
        }
        $recentCampaigns = [];
        if (!empty($recentCampaignReports)) {
            $recentCampaigns = getPaginatedData($recentCampaignReports['recent_campaigns']);
        }
        return view('livewire.campaign-reports', [
            'lastCampaignDetails' => $lastCampaignDetails,
            'recentCampaigns' => $recentCampaigns,
        ])->layout('layouts.admin');
    }
}
