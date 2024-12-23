<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendNotificationToVisitedVisitorsForHyd24Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $payload = [];

    /**
     * Create a new job instance.
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (empty($this->payload['send_to'])) {
            return;
        }

        $this->sendNotification();
    }

    public function sendNotification()
    {
        $url = 'https://api.engati.com/whatsapp-api/v1.0/customer/68259/bot/39ee22f9f92c4d07/template';

        try {
            $payload = [
                "payload" => [
                    "name" => "hyd_24_feedback",
                    "components" => [
                        [
                            "type" => "header",
                            "parameters" => [
                                [
                                    "type" => "image",
                                    "image" => [
                                        "link" => "https://crm.medicall.in/images/medicall-hyd-2024.jpg"
                                    ]
                                ],
                            ]
                        ]
                    ],
                    "language" => [
                        "code" => "en_US",
                        "policy" => "deterministic"
                    ],
                    "namespace" => "dd96ee84_2909_4425_86ba_8a7fb56c2d2a"
                ],
                "phoneNumber" => $this->payload['send_to']
            ];

            $curl = curl_init();
            curl_setopt_array($curl, array(
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
            ));

            $response = curl_exec($curl);

            $isCurlError = curl_error($curl);

            curl_close($curl);

            if ($isCurlError) {
                return ['status' => 'error', 'message' => 'CURL ERROR', 'error' => $isCurlError];
            }
            $response = json_decode($response, true);
            $sent = $response['status']['code'] ?? '';
            if ($sent == 1000) {
                return ['status' => 'success', 'message' => 'Sent successfully', 'response' => $response];
            }
            return ['status' => 'error', 'message' => 'Failed to send', 'error' => $response];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Something went wrong to sending notification', 'error' => $e->getMessage()];
        }
    }
}
