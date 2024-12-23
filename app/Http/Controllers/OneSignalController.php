<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Berkayk\OneSignal\OneSignalClient;

class OneSignalController extends Controller
{
    protected $oneSignalClient;
    public function __construct()
    {
        $this->oneSignalClient = new OneSignalClient(
            config('onesignal.app_id'),
            config('onesignal.rest_api_key'),
            config('onesignal.user_auth_key')
        );
    }

    public function sendNotification($title, $message, $userIds, $imageUrl = null)
    {
        try {
            $userIds = is_array($userIds) ? $userIds : [$userIds];

            $notificationData = [
                'headings' => ['en' => $title],
                'contents' => ['en' => $message],
                'include_external_user_ids' => $userIds,
                'target_channel' => 'push'
            ];

            if ($imageUrl) {
                $notificationData['ios_attachments'] = ['id1' => $imageUrl];
                $notificationData['big_picture'] = $imageUrl;
            }

            $response = $this->oneSignalClient->sendNotificationCustom($notificationData);

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully.',
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
