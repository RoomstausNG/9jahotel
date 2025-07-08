<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FCMService;
use Illuminate\Http\Request;

class FCMTestController extends Controller
{
  /**
   * Test sending FCM notification with a custom token
   */
  public function testFcm(Request $request)
  {
    $validated = $request->validate([
      'token' => 'required|string',
      'title' => 'required|string',
      'body' => 'required|string',
      'data' => 'nullable|array'
    ]);

    $fcm = new FCMService();
    $result = $fcm->sendNotification(
      [$validated['token']],
      $validated['title'],
      $validated['body'],
      $validated['data'] ?? []
    );

    return response()->json([
      'success' => true,
      'message' => 'FCM test executed',
      'result' => $result
    ]);
  }
}
