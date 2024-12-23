<?php

namespace App\Http\Livewire;

use App\Models\Exhibitor;
use App\Models\MailTemplate;
use Livewire\Component;

class EmailTemplatesSummary extends Component
{

    public $currentEvent;
    public function mount()
    {
        $this->currentEvent = getCurrentEvent();
    }

    public function render()
    {
        $emailTemplates = MailTemplate::all();
        // dd($emailTemplates);

        return view('livewire.email-templates-summary', [
            'emailTemplates' => $emailTemplates,
        ])->layout('layouts.admin');

    }

    public function deleteEmailTemplate($templateId)
    {
        $template = MailTemplate::find($templateId);
        if ($template) {
            $template->delete();
            session()->flash('success', 'Template deleted successfully.');
        } else {
            session()->flash('error', 'Template not found.');
        }
    }

    public function sendEmail($templateId)
    {

        $template = MailTemplate::find($templateId);

        if (!$template) {
            session()->flash('error', 'Invalid template id');
            return false;
        }
        $exhibitorIds = [];
        $halls = [
            'hall1' => 1,
            'hall2' => 2,
            'hall3' => 3,
            'hall4' => 4,
            'hall5' => 5,
        ];

        $subject = $template->subject ?? '';
        $content = $template->message_content ?? '';

        if ($template->target_id == 'all-exhibitors') {
            $exhibitors = Exhibitor::whereHas('eventExhibitors', function ($query) {
                return $query->where('event_id', $this->currentEvent->id);
            })->get();
        } else if ($template->target_id == 'hall') {
            $hallNo = isset($halls[$template->hall_id]) ? $halls[$template->hall_id] : '';
            $exhibitors = Exhibitor::whereHas('eventExhibitors', function ($query) use ($hallNo) {
                return $query->where('event_id', $this->currentEvent->id)
                    ->where('stall_no', 'like', $hallNo . '%');
            })->get();
        } else if ($template->target_id == 'specific-exhibitors') {

            $exhibitors = Exhibitor::whereHas('eventExhibitors', function ($query) use ($template) {
                return $query->where('event_id', $this->currentEvent->id)
                    ->whereIn('exhibitor_id', $template->exhibitor_ids);
            })->get();
        }

        if (empty($exhibitors)) {
            session()->flash('info', 'No exhibitors were found');
            return;
        }

        $sentEmailsCount = 0;

        foreach ($exhibitors as $exhibitor) {

            $attributes = [];
            $attributes["{exhibitor_name}"] = $exhibitor->name;
            $attributes['{event_title}'] = $this->currentEvent->title ?? '';

            $receiverEmail = $exhibitor->email;

            if (empty($receiverEmail)) {
                continue;
            }

            $subject = strtr($subject, $attributes);
            $content = strtr($content, $attributes);

            $result = $this->sendingEmail($receiverEmail, $exhibitor->name, $subject, $content);
            if ($result['status'] == 'success') {
                $sentEmailsCount++;
            }
        }

        if ($sentEmailsCount) {
            session()->flash('success', 'Mail sent successfully.');
        }

    }

    public function sendingEmail($receiverEmail, $receiverName, $subject, $content)
    {

        try {
            $payload = [
                'from' => [
                    'address' => 'info@medicall.in',
                ],
                'to' => [
                    [
                        'email_address' => [
                            'address' => $receiverEmail,
                            'name' => $receiverName,
                        ],
                    ],
                ],

                'subject' => $subject,
                'htmlbody' => $content,
            ];

            $curl = curl_init();
            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL => "https://api.zeptomail.in/v1.1/email",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_HTTPHEADER => array(
                        "accept: application/json",
                        "authorization: Zoho-enczapikey PHtE6r1cQrvpjm969kcF7KLqQMGgPYp9rONkfVNOtohKA/JXHk1Vro0plGW0rEgjAaJFRqabyY1tue+Z5u6FIWrkY2hEVGqyqK3sx/VYSPOZsbq6x00atlgTfkzVU4/set5o1yDWv9/bNA==",
                        "cache-control: no-cache",
                        "content-type: application/json",
                    ),
                )
            );
            $result = curl_exec($curl);
            $curlError = curl_error($curl);
            curl_close($curl);

            $response = json_decode($result, true);

            if ($curlError) {
                return ['status' => 'error', 'message' => $curlError];
            }

            return ['status' => 'success', 'message' => 'send success', 'response' => $response];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
