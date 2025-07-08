<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FCMService;

class TestFcmNotification extends Command
{
  protected $signature = 'fcm:test {token?}';
  protected $description = 'Test FCM notification sending';

  public function handle()
  {
    $token = $this->argument('token');

    if (!$token) {
      $token = $this->ask('Enter a test FCM token');
    }

    $this->info('Sending test notification to token: ' . $token);

    $fcmService = app(FCMService::class);
    $result = $fcmService->sendNotification(
      [$token],
      'Test Notification',
      'This is a test notification from your Laravel app',
      ['test_data' => 'value']
    );

    $this->info('Result: ' . json_encode($result, JSON_PRETTY_PRINT));

    if ($result['success']) {
      $this->info('✅ FCM notification sent successfully!');
    } else {
      $this->error('❌ FCM notification failed!');
    }

    return 0;
  }
}
