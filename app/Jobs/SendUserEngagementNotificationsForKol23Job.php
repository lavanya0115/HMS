<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendUserEngagementNotificationsForKol23Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $mobileNumber;

    /**
     * Create a new job instance.
     */
    public function __construct($mobileNumber)
    {
        $this->mobileNumber = $mobileNumber;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->sendNotification();
    }

    public function sendNotification()
    {
        $mobileNumber = $this->mobileNumber;

        if (empty($mobileNumber)) {
            return;
        }

        $url = 'https://api.engati.com/whatsapp-api/v1.0/customer/68259/bot/b6ac87a70aa24210/template';
        $payload = [
            "payload" => [
                "name" => "kolkatavisitormarking",
                "components" => [
                    [
                        "type" => "header",
                        "parameters" => [
                            [
                                "type" => "image",
                                "image" => [
                                    "link" => "https://crm.medicall.in/images/Kolkatavisitormarkting.jpg",
                                ],
                            ],
                        ],
                    ],
                    [
                        "index" => 0,
                        "parameters" => [
                            [
                                "payload" => "flow_E0EC01C600144B718D7B7874450A2DC5",
                                "type" => "payload",
                            ],
                        ],
                        "sub_type" => "quick_reply",
                        "type" => "button",
                    ],
                ],
                "language" => [
                    "code" => "en_US",
                    "policy" => "deterministic",
                ],
                "namespace" => "dd96ee84_2909_4425_86ba_8a7fb56c2d2a",
            ],
            "phoneNumber" => $mobileNumber,
        ];

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 0,
                CURLOPT_POST => 1,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Basic a29ad3e1-2bb5-4126-9d44-153e96330a54-G4TG8QD",
                    "Content-Type: application/json",
                ),
            )
        );

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
    }
}