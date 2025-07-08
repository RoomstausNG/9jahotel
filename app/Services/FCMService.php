<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FCMService
{
  private $serverKey;
  private $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

  public function __construct()
  {
    $this->serverKey = config('services.fcm.server_key');
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

    try {
      $response = Http::withHeaders([
        'Authorization' => 'key=' . $this->serverKey,
        'Content-Type' => 'application/json',
      ])->post($this->fcmUrl, [
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
        Log::info('FCM notification sent successfully', [
          'success_count' => $result['success'],
          'failure_count' => $result['failure'] ?? 0
        ]);

        return [
          'success' => true,
          'success_count' => $result['success'],
          'failure_count' => $result['failure'] ?? 0
        ];
      }

      Log::error('FCM notification failed', [
        'response' => $result,
        'status' => $response->status()
      ]);

      return [
        'success' => false,
        'message' => 'Failed to send notification',
        'details' => $result
      ];
    } catch (\Exception $e) {
      Log::error('FCM Service Error: ' . $e->getMessage());

      return [
        'success' => false,
        'message' => 'Exception occurred',
        'error' => $e->getMessage()
      ];
    }
  }
}
