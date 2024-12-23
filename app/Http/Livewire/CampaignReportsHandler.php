<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;

class CampaignReportsHandler extends Component
{
    use WithPagination;
    public $type;
    public $key;
    public $campaignLocationLabels;
    public $campaignLocationSeries;

    public function mount()
    {
        $this->type = request('type') ?? '';
        if ($this->type == 'Recent') {
            $this->key = request('key');
        }
    }

    public function colorMap($key)
    {
        $varients = [
            'emails' => 'bg-purple',
            'gplus' => 'bg-yellow',
            'twitter' => 'bg-teal',
            'other' => 'bg-warning',
            'total' => 'bg-primary',
            'facebook' => 'bg-black',
            'tumblr' => 'bg-cyan',
            'pinterest' => 'bg-red',
            'linkedin' => 'bg-pink',
        ];
        $color = $varients[$key];
        return $color;
    }

    public function countryMap($keys)
    {
        $countries = [
            'de' => 'Germany',
            'hk' => 'Hong Kong',
            'tw' => 'Taiwan',
            'tz' => 'Tanzania',
            'ua' => 'Ukraine',
            'hu' => 'Hungary',
            'qa' => 'Qatar',
            'ug' => 'Uganda',
            'ma' => 'Morocco',
            'ie' => 'Ireland',
            'us' => 'United States',
            'ec' => 'Ecuador',
            'ae' => 'United Arab Emirates',
            'in' => 'India',
            'za' => 'South Africa',
            'mv' => 'Maldives',
            'mw' => 'Malawi',
            'it' => 'Italy',
            'my' => 'Malaysia',
            'es' => 'Spain',
            'et' => 'Ethiopia',
            'at' => 'Austria',
            'au' => 'Australia',
            'vn' => 'Vietnam',
            'ng' => 'Nigeria',
            'ro' => 'Romania',
            'nl' => 'Netherlands',
            'np' => 'Nepal',
            'bd' => 'Bangladesh',
            'ru' => 'Russia',
            'jp' => 'Japan',
            'bh' => 'Bahrain',
            'fr' => 'France',
            'nz' => 'New Zealand',
            'sa' => 'Saudi Arabia',
            'sc' => 'Seychelles',
            'br' => 'Brazil',
            'bt' => 'Bhutan',
            'se' => 'Sweden',
            'sg' => 'Singapore',
            'ke' => 'Kenya',
            'gb' => 'United Kingdom',
            'ca' => 'Canada',
            'cd' => 'Democratic Republic of the Congo',
            'gh' => 'Ghana',
            'ch' => 'Switzerland',
            'kr' => 'South Korea',
            'gn' => 'Guinea',
            'cl' => 'Chile',
            'cn' => 'China',
            'kw' => 'Kuwait',
            'kz' => 'Kazakhstan',
            'pa' => 'Panama',
            'th' => 'Thailand',
            'ph' => 'Philippines',
            'pk' => 'Pakistan',
            'tr' => 'Turkey',
            'lk' => 'Sri Lanka',
            'id' => 'Indonesia',
            'ai' => 'Anguilla',
            'mz' => 'Mozambique',
            'az' => 'Azerbaijan',
            'no' => 'Norway',
            'om' => 'Oman',
            'ge' => 'Georgia',
            'ci' => 'Ivory Coast',
            'co' => 'Colombia',
            'gu' => 'Guam',
            'tn' => 'Tunisia',
        ];
        $country = [];
        foreach ($keys as $key) {
            $country[] = isset($countries[$key]) ? $countries[$key] : $key;
        }
        return $country;
    }

    public function render()
    {
        $campaignReports = '';
        $campaignReach = '';
        $campaignLocation = [];
        $campaignDetails = '';
        $campaignMailList = '';
        $campaignDatas = '';
        $campaignReportCountData = [];
        $campaignReportPercentData = [];
        $campaignName = '';
        $locationIndiaData = [];

        if ($this->type === 'Last') {
            $campaignDatas = getLastCampaignReport();
            if (!empty($campaignDatas)) {
                // dd($campaignDatas);
                $mailData = $campaignDatas['associated_mailing_lists'] ?? [];
                $campaignReports = $campaignDatas['campaign-reports'] ?? [];
                $campaignReach = $campaignDatas['campaign-reach'] ?? [];
                $campaignLocation = $campaignDatas['campaign-by-loaction'] ?? [];

                if (is_array($campaignLocation) && !empty($campaignLocation)) {
                    arsort($campaignLocation);

                    if (isset($campaignLocation['India'])) {
                        $locationIndiaData['India'] = $campaignLocation['India'];
                        unset($campaignLocation['India']);
                    }
                    $top10CampaignLocation = array_slice($campaignLocation, 0, 5, true);
                    $otherCampaignLocation = array_slice($campaignLocation, 6, true);
                    $this->campaignLocationLabels = array_keys($top10CampaignLocation);
                    $this->campaignLocationSeries = array_values($top10CampaignLocation);
                    $otherCount = array_sum($otherCampaignLocation);
                    $this->campaignLocationLabels[] = 'Others';
                    $this->campaignLocationSeries[] = $otherCount;
                }


                $campaignDetails = $campaignDatas['campaign-details'] ?? [];
                $campaignName = $campaignDetails[0]['campaign_name'] ?? '';
                $campaignMailList = getPaginatedData($mailData) ?? '';
                foreach ($campaignReports as  $data) {
                    foreach ($data as $key => $value) {
                        if (strpos($key, 'percent') !== false || strpos($key, 'rate') !== false) {
                            $campaignReportPercentData[$key] = $value;
                        } else {
                            $campaignReportCountData[$key] = $value;
                        }
                    }
                }
            }
        }


        if ($this->type === 'Recent') {
            $campaignDatas = getCampaignReport($this->key);
            if (!empty($campaignDatas)) {

                if ($campaignDatas['code'] == 6002) {
                    return session()->flash('error', 'Invalid campaign / or Its a Draft Campaign');
                }
            } else {
                return session()->flash('error', 'No campaign data available');
            }
            // dd($campaignDatas);
            $mailData = $campaignDatas['associated_mailing_lists'] ?? [];
            $campaignReports = $campaignDatas['campaign-reports'] ?? [];

            $campaignReach = $campaignDatas['campaign-reach'] ?? [];
            $campaignLocation = $campaignDatas['campaign-by-loaction'] ?? [];
            if (is_array($campaignLocation) && !empty($campaignLocation)) {
                arsort($campaignLocation);
                // dump($campaignLocation);
                if (isset($campaignLocation['in'])) {
                    $locationIndiaData['in'] = $campaignLocation['in'];
                    unset($campaignLocation['in']);
                }
                $top10CampaignLocation = array_slice($campaignLocation, 0, 5, true);
                $otherCampaignLocation = array_slice($campaignLocation, 6, true);
                $this->campaignLocationLabels = $this->countryMap(array_keys($top10CampaignLocation));
                $this->campaignLocationSeries = array_values($top10CampaignLocation);
                $otherCount = array_sum($otherCampaignLocation);
                $this->campaignLocationLabels[] = 'Others';
                $this->campaignLocationSeries[] = $otherCount;
            }
            $campaignDetails = $campaignDatas['campaign-details'] ?? [];
            $campaignName = $campaignDetails[0]['campaign_name'] ?? '';
            if (!empty($mailData)) {
                $campaignMailList = getPaginatedData($mailData) ?? '';
            }
            // dd(json_encode($campaignLocation));
            foreach ($campaignReports as  $data) {
                foreach ($data as $key => $value) {
                    if (strpos($key, 'percent') !== false || strpos($key, 'rate') !== false) {
                        $campaignReportPercentData[$key] = $value;
                    } else {
                        $campaignReportCountData[$key] = $value;
                    }
                }
            }
            // dd($campaignReportCountData['delivered_count']);
        }
        // dd($campaignDatas, $campaignReach, $campaignMailList);
        return view('livewire.campaign-reports-handler', [
            'campaignDatas' => $campaignDatas,
            'campaignReportCountData' => $campaignReportCountData,
            'campaignReportPercentData' => $campaignReportPercentData,
            'campaignReach' => $campaignReach,
            'campaignLocationLabels' => $this->campaignLocationSeries,
            'campaignLocationSeries' => $this->campaignLocationSeries,
            'campaignLocation' => $campaignLocation,
            'campaignDetails' => $campaignDetails,
            'campaignMailList' => $campaignMailList,
            'campaignName' => $campaignName,
            'locationIndiaData' => $locationIndiaData,
        ])->layout('layouts.admin');
    }
}
