<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FCMService
{
    private $messaging;
    private $legacyServerKey;

    public function __construct()
    {
        try {
            // Try the Admin SDK approach first
            if (config('services.firebase.credentials')) {
                $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
                $this->messaging = $factory->createMessaging();
            }
            // Fall back to legacy approach if credentials not found
            else {
                $this->legacyServerKey = config('services.fcm.server_key');
            }
        } catch (\Exception $e) {
            Log::error('FCM initialization failed: ' . $e->getMessage());
            // Fall back to legacy approach
            $this->legacyServerKey = config('services.fcm.server_key');
        }
    }

    public function sendNotification(array $tokens, string $title, string $body, array $data = [])
    {
        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No tokens provided'];
        }

        // Remove any null or empty tokens
        $tokens = array_filter($tokens);

        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No valid tokens'];
        }

        // Use Admin SDK if available
        if (isset($this->messaging)) {
            return $this->sendWithAdminSdk($tokens, $title, $body, $data);
        }
        // Fall back to legacy HTTP API
        else {
            return $this->sendWithLegacyApi($tokens, $title, $body, $data);
        }
    }

    private function sendWithAdminSdk(array $tokens, string $title, string $body, array $data = [])
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData(array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ]));

            $sendReport = $this->messaging->sendMulticast($message, $tokens);

            Log::info('FCM notification sent with Admin SDK', [
                'success_count' => $sendReport->successes()->count(),
                'failure_count' => $sendReport->failures()->count()
            ]);

            return [
                'success' => true,
                'success_count' => $sendReport->successes()->count(),
                'failure_count' => $sendReport->failures()->count()
            ];
        } catch (\Exception $e) {
            Log::error('FCM Admin SDK Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage()
            ];
        }
    }

    private function sendWithLegacyApi(array $tokens, string $title, string $body, array $data = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->legacyServerKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'registration_ids' => array_values($tokens),
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => $data['unread_count'] ?? 1
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ]),
                'priority' => 'high'
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['success']) && $result['success'] > 0) {
                Log::info('FCM notification sent with Legacy API', [
                    'success_count' => $result['success'],
                    'failure_count' => $result['failure'] ?? 0
                ]);

                return [
                    'success' => true,
                    'success_count' => $result['success'],
                    'failure_count' => $result['failure'] ?? 0
                ];
            }

            Log::error('FCM notification failed with Legacy API', [
                'response' => $result,
                'status' => $response->status()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send notification',
                'details' => $result
            ];
        } catch (\Exception $e) {
            Log::error('FCM Legacy API Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage()
            ];
        }
    }
}
