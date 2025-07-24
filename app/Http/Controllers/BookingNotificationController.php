<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\FCMService;
use App\Models\BookingNotification;
use App\Models\User;
use Carbon\Carbon;

class BookingNotificationController extends Controller
{

    use ApiResponse;
    public function __construct(private FCMService $fcmService) {}

    /**
     * Receive booking notification from Web App or Mobile App
     */
    public function notifyNewBooking(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'hotel_id' => 'required|integer|exists:hotels,id',
            'booking_id' => 'required|integer',
            'guest_name' => 'required|string|max:255',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'room_type' => 'nullable|string|max:100',
            'total_amount' => 'required|numeric|min:0',
            'source' => 'required|in:web_app,mobile_app',
        ]);

        DB::beginTransaction();
        try {
            // 1. Store the notification
            $notification = BookingNotification::create($validated);
            // 2. Increment unread counter
            $unreadCount = $this->incrementUnreadCount($validated['hotel_id']);
            // 3. Get all hotel owner FCM tokens
            $tokens = User::where('hotel_id', $validated['hotel_id'])
                ->whereNotNull('fcm_token')
                ->pluck('fcm_token')
                ->toArray();
            // 4. Prepare notification content
            $checkInDate = Carbon::parse($validated['check_in'])->format('M d, Y');
            $title = '🎉 New Booking!';
            $body = "{$validated['guest_name']} - Check-in: {$checkInDate}";
            $notificationData = [
                'type' => 'new_booking',
                'notification_id' => (string) $notification->id,
                'booking_id' => (string) $validated['booking_id'],
                'hotel_id' => (string) $validated['hotel_id'],
                'unread_count' => (string) $unreadCount,
                'timestamp' => now()->toIso8601String(),
            ];
            // 5. Send FCM notification
            $fcmResult = $this->fcmService->sendNotification(
                $tokens,
                $title,
                $body,
                $notificationData
            );
            DB::commit();
            $data = [
                'notification_id' => $notification->id,
                'unread_count' => $unreadCount,
                'fcm_result' => $fcmResult,
            ];

            return $this->successResponse(
                $data,
                "Notification sent successfully",
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unread notifications count for a hotel
     */
    public function getUnreadCount(Request $request, $hotelId)
    {
        // Verify user has access to this hotel
        if ($request->user()->hotel_id != $hotelId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $count = Cache::remember(
            "hotel:{$hotelId}:unread_count",
            300, // 5 minutes
            function () use ($hotelId) {
                return BookingNotification::where('hotel_id', $hotelId)
                    ->where('is_read', false)
                    ->count();
            }
        );
        $recentNotifications = BookingNotification::where('hotel_id', $hotelId)
            ->latest()
            ->take(10)
            ->get();
        return response()->json([
            'success' => true,
            'unread_count' => $count,
            'recent_notifications' => $recentNotifications,
        ]);
    }

    /**
     * Mark all notifications as read for a hotel
     */
    public function markAllAsRead(Request $request, $hotelId)
    {
        // Verify user has access
        if ($request->user()->hotel_id != $hotelId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        BookingNotification::where('hotel_id', $hotelId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        // Clear cache
        Cache::forget("hotel:{$hotelId}:unread_count");
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Update user's FCM token
     */
    public function updateFcmToken(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string|max:500',
        ]);
        $request->user()->update([
            'fcm_token' => $validated['fcm_token'],
        ]);
        return $this->successResponse(
            null,
            "FCM token updated successfully"
        );
    }

    /**
     * Get notification details
     */
    public function getNotificationDetails(Request $request, $notificationId)
    {
        $notification = BookingNotification::findOrFail($notificationId);
        // Verify access
        if ($request->user()->hotel_id != $notification->hotel_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        // Mark as read
        if (!$notification->is_read) {
            $notification->update(['is_read' => true]);
            $this->decrementUnreadCount($notification->hotel_id);
        }
        $data = [
            'notification' => $notification,
        ];

        return $this->successResponse($data, 'Notification details retrieved successfully');
    }

    /**
     * Helper: Increment unread count
     */
    private function incrementUnreadCount($hotelId)
    {
        Cache::forget("hotel:{$hotelId}:unread_count");
        return BookingNotification::where('hotel_id', $hotelId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Helper: Decrement unread count
     */
    private function decrementUnreadCount($hotelId)
    {
        Cache::forget("hotel:{$hotelId}:unread_count");
    }
}