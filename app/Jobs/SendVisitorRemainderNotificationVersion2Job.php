<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendVisitorRemainderNotificationVersion2Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $visitor;

    public $event;

    public $mobileNumber;

    /**
     * Create a new job instance.
     */
    public function __construct($mobileNumber, $visitor = null, $event = null)
    {
        $this->mobileNumber = $mobileNumber;
        $this->visitor = $visitor;
        $this->event = $event;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $mobileNumber = $this->visitor->mobile_number ?? $this->mobileNumber;

        if (!$mobileNumber || empty($mobileNumber)) {
            return;
        }

        $url = 'https://api.engati.com/whatsapp-api/v1.0/customer/68259/bot/b6ac87a70aa24210/template';

        $payload = [
            "payload" => [
                "name" => "visitor_remainder_template_for_evening",
                "components" => [],
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
