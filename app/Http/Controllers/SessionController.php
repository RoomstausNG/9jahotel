<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class SessionController extends Controller
{
    /**
     * Update session data for booking dates
     */
    public function updateBookingDates(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|in:checkin,checkout',
            'value' => 'required|date|after_or_equal:today'
        ]);

        $key = $request->key;
        $value = $request->value;

        // Additional validation for checkout date
        if ($key === 'checkout' && session('checkin')) {
            $checkinDate = Carbon::parse(session('checkin'));
            $checkoutDate = Carbon::parse($value);

            if ($checkoutDate->lte($checkinDate)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Checkout date must be after checkin date'
                ], 400);
            }
        }

        // Additional validation for checkin date
        if ($key === 'checkin' && session('checkout')) {
            $checkinDate = Carbon::parse($value);
            $checkoutDate = Carbon::parse(session('checkout'));

            if ($checkinDate->gte($checkoutDate)) {
                // Auto-adjust checkout date to be one day after checkin
                $newCheckout = $checkinDate->copy()->addDay()->format('Y-m-d');
                session(['checkout' => $newCheckout]);
            }
        }

        session([$key => $value]);

        return response()->json([
            'status' => 'success',
            'message' => 'Session updated successfully',
            'data' => [
                'checkin' => session('checkin'),
                'checkout' => session('checkout')
            ]
        ]);
    }

    /**
     * Get current booking dates from session
     */
    public function getBookingDates(): JsonResponse
    {
        return response()->json([
            'checkin' => session('checkin'),
            'checkout' => session('checkout')
        ]);
    }
}
