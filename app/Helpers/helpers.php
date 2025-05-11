<?php

use Carbon\Carbon;
use App\Models\Hall;
use App\Models\Item;
use App\Models\Room;
use App\Models\Type;
use App\Models\User;
use App\Models\Hotel;
use App\Models\CityCopy;
use Illuminate\Support\Str;
use Filament\Facades\Filament;
use Nnjeim\World\Models\State;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

function getFirstLetters(string $inputString): string
{
    $words = explode(' ', trim($inputString));
    $firstWord = $words[0] ?? '';

    // Take the first 3 letters and make them uppercase
    $code = strtoupper(substr($firstWord, 0, 3));

    // Optionally add randomness or timestamp to ensure uniqueness
    return $code . strtoupper(Str::random(3)) . time();
}


function currentUser()
{
    return auth()->user();
}

function getCloudinaryUrl($publicId, $extension = "webp")
{
    $cloudName = env('CLOUDINARY_CLOUD_NAME');
    return $publicId ? "https://res.cloudinary.com/{$cloudName}/image/upload/{$publicId}" : null;
}

function getCloudinaryUrlViaStorage($publicId, $extension = "webp")
{
    $publicId ? Storage::disk('cloudinary')->url($publicId) : null;
}

function profilePicture(object $user): string
{
    return $user->signature ? asset('storage/' . $user->signature) : asset('img/avatar.png');
}

function getColorCode($status)
{
    switch (strtolower($status)) {
        case 'available':
            return "#ddd"; // white
        case 'pending':
            return "#ffcc00"; // yellow
        case 'unpaid':
            return "#ffcc00"; // yellow
        case 'booked':
            return "#008000"; // green
        case 'paid':
            return "#008000"; // green
        case 'occupied':
            return "#FF0000"; // red
        case 'late':
            return "#FFA500"; // orange
        case 'dirty':
            return "#A52A2A"; // brown
        case 'repair':
            return "#000000"; // black
        case 'income':
            return "#05cc0a"; // green
        case 'expenditure':
            return "#ee0016"; // red
        default:
            return null; // or any default color code
    }
}

function myHotel(): Hotel|null
{
    return auth()->check() ? auth()->user()->hotel : null;
}

function getNigeriaStates()
{
    return \App\Models\StateCopy::where('country_id', 160)->get();
}

function sweetAlert($text, $title, $icon = "success", $button = false, $timer = 5000): array
{
    return [
        'icon' => $icon,
        'text' => $text,
        'title' => $title,
        'showConfirmButton' => $button,
        'timer' => $timer,
    ];
}

function formatAsNaira($amount)
{
    // Format the amount with Nigerian Naira currency symbol and commas for thousands separator
    return "₦" . number_format($amount, 2);
}

function getDateRange($checked_in, $checked_out)
{
    $dates = [];

    // Convert start and end dates to DateTime objects
    $startDate = new \DateTime($checked_in);
    $endDate = new \DateTime($checked_out);

    // Iterate through the date range and add each date to the array
    while ($startDate < $endDate) {
        $dates[] = $startDate->format('Y-m-d');
        $startDate->modify('+1 day');
    }

    return $dates;
}

function formatAsDollar($amount)
{
    // Format the amount with Nigerian Naira currency symbol and commas for thousands separator
    return "$" . number_format($amount, 2);
}

function getCloudinaryImage($public_id)
{
    if (!empty($public_id)) {
        // Remove file extension using regex
        $cleanPublicId = preg_replace('/\.(jpg|jpeg|png|webp|gif)$/i', '', $public_id);

        // Return the Cloudinary URL for the cleaned public_id
        return Cloudinary::getUrl($cleanPublicId);
    }
    return null;
}


function getFilenameFromArray($array)
{
    foreach ($array as $key => $value) {
        if (is_object($value) && property_exists($value, 'filename')) {
            return $value->filename;
        }
    }
    return null;
}

function getStatusColor($status)
{
    return match ($status) {
        "available" => "#aaaaaa",   // white
        "pending" => "#FFFF00",     // yellow
        "unpaid" => "#FFFF00",     // yellow
        "paid" => "#008000",      // green
        "occupied" => "#FF0000",        // red
        "late" => "#FFA500",      // orange
        "dirty" => "#A52A2A",       // brown
        "repair" => "#000000",
        'income' => '#05cc0a',
        'expenditure' => 'ee0016',
        default => "#808080"        // gray for unknown status
    };
}

function calculateDateDifferenceInDays($date1, $date2)
{
    // Parse the date strings into Carbon instances
    $carbonDate1 = Carbon::parse($date1);
    $carbonDate2 = Carbon::parse($date2);

    // Calculate the difference in days
    $diffInDays = $carbonDate1->diffInDays($carbonDate2);

    return $diffInDays;
}

function getCloudinaryItemImage($itemId): string|null
{
    $item = Item::find($itemId);
    $public_id = $item->generalItem?->image;
    try {
        if (!empty($public_id) && is_string($public_id)) {
            // Remove file extension using regex
            $cleanPublicId = preg_replace('/\.(jpg|jpeg|png|webp|gif)$/i', '', $public_id);

            // Ensure we have a valid public ID after cleaning
            if (!empty($cleanPublicId)) {
                $cloudinaryUrl = cloudinary()->getUrl($cleanPublicId);
                // Check if we got a valid URL back
                if (!empty($cloudinaryUrl)) {
                    return $cloudinaryUrl;
                }
            }
        }
    } catch (\Exception $e) {
        \Log::error('Cloudinary image error: ' . $e->getMessage(), [
            'public_id' => $public_id
        ]);
    }
    $cleanPublicId = preg_replace('/\.(jpg|jpeg|png|webp|gif)$/i', '', $item->image);
    // Return default avatar if anything goes wrong
    return cloudinary()->getUrl($cleanPublicId);
}

function getBookingDays($start, $end): array|null
{
    return \App\Models\Booking::whereDay('checked_in', $start)->whereMonth('checked_out', $end)->select('checked_in', 'checked_out')->get()->toArray();
}

function getLogoOrBg($hotel): string
{
    if ($hotel->background) {
        return env('LOGO_URL') . "/storage/" . $hotel->background;
    } else if ($hotel->logo) {
        return env('LOGO_URL') . "/storage/" . $hotel->logo;
    } else {
        return asset('img/9jahotel.png');
    }
}

function roomStatus(Room|Hall $room, $date): string
{
    $roomSt = $room->statuses()->where('date', $date)->orderByDesc('id')->first();

    // return $roomSt ? $roomSt->status : 'available';
    if (in_array($room->status, ['dirty', 'repair', 'occupied'])) {
        return $room->status;
    } elseif ($room->bookings()->exists() && $room->statuses()->where('date', $date)->orderByDesc('id')->first()) {
        return $roomSt->status;
    } else {
        return $roomSt?->status ?? 'available';
        // return 'available';
    }
}

function countStatus($date, $status): int
{
    $rooms = Room::all();
    $count = 0;
    foreach ($rooms as $room) {
        if (roomStatus($room, $date) == $status) {
            $count++;
        }
    }

    return $count;
}

function countBasedOnCategory($category, $start, $end, $status = 'available'): int
{
    $rooms = $category->rooms;
    $count = 0;
    foreach ($rooms as $room) {
        if (roomStatus($room, $start) == $status) {
            $count++;
        }
        if (roomStatus($room, $end) == $status) {
            $count++;
        }
    }

    return $count;
}

function getCurrentTenant()
{
    return Filament::getTenant();
}


function displayImage($image): string|null
{
    if ($image) {
        return env('APP_URL') . "/storage/" . $image;
    } else {
        return null;
    }
}

function getDateR($dat)
{

    $endDate = new \DateTime($dat);

    // Iterate through the date range and add each date to the array

    return $endDate->format('D d M, Y');
}

function myLogo(): string
{
    return myHotel()->logo ? asset('storage/' . myHotel()->logo) : asset('img/9jahotel.png');
}


function associateCustomer($customerId)
{
    $customer = \App\Models\Customer::find($customerId);

    if (!$customer->hotels()->find(auth()->user()->hotel_id)) {
        $customer->hotels()->attach(auth()->user()->hotel_id);
    }
}

function cities()
{
    return CityCopy::where('country_id', 160)->get();
}

function hotels()
{
    return Hotel::all();
}


function calculateRemainingAmount($originalNumber, $percentage)
{
    // Calculate the amount to be deducted
    $deduction = ($percentage / 100) * $originalNumber;

    // Calculate the remaining amount
    $remainingAmount = $originalNumber - $deduction;

    return $remainingAmount;
}

function getDatesFromRange($startDate, $endDate): array
{
    $dates = [];
    $currentDate = strtotime($startDate);
    $endDate = strtotime($endDate);

    while ($currentDate < $endDate) {
        $dates[] = date('Y-m-d', $currentDate);
        $currentDate = strtotime("+1 day", $currentDate);
    }
    return $dates;
}

function hallStatus(Hall $hall, $date): string
{
    $hallSt = $hall->statuses()->where('date', $date)->first();

    return $hallSt ? $hallSt->status : 'available';
    // if (in_array($hall->status, ['dirty', 'repair'])) {
    //     return $hall->status;
    // } elseif ($hall->bookings()->exists() && in_array($date, $hall->getBookingDates())) {
    //     return $hall->getBookingByDate($date)->status;
    // } else {
    //     return 'available';
    // }
}

function flushSearch()
{
    // delete all sessions
    session()->forget('city');
    session()->forget('checkin');
    session()->forget('checkout');
}

function getRatingColor(float $rating): string
{
    if ($rating >= 9) {
        return '#00FF00'; // Green for Excellent
    } elseif ($rating >= 8) {
        return '#00FF00'; // LightGreen for Very Good
    } elseif ($rating >= 7) {
        return '#ADFF2F'; // Yellow for Good
    } elseif ($rating >= 6) {
        return '#A5FF00'; // Orange for Fair
    } elseif ($rating >= 5) {
        return '#808080'; // Red for Poor
    } else {
        return '#FF0000'; // Gray for ratings below 5
    } #00FF00
}

function getRatingDescription(float $rating): string
{
    if ($rating >= 9) {
        return 'Outstanding';
    } elseif ($rating >= 8) {
        return 'Excellent';
    } elseif ($rating >= 7) {
        return 'Very Good';
    } elseif ($rating >= 6) {
        return 'Good';
    } elseif ($rating >= 5) {
        return 'Fair';
    } elseif ($rating >= 3) {
        return 'Poor';
    } elseif ($rating >= 2) {
        return 'Very Poor';
    } else {
        return 'Worst';
    }
}

function getAllBookingsDateRange($bookable)
{
    $allDates = [];
    foreach ($bookable->bookings as $booking) {
        $date = $booking->getDateRange();
        array_merge($allDates, $date);
    }
    return $allDates;
}


/**
 * Update the lowest price for rooms and halls for a specific hotel
 *
 * @param int $hotelId The ID of the hotel to update
 * @return bool Whether the update was successful
 */

function updateHotelLowestPrices(int $hotelId): bool
{
    try {
        $hotel = Hotel::find($hotelId);

        if (!$hotel) {
            return false;
        }

        // Update room lowest price
        $lowestRoomPrice = Type::where('hotel_id', $hotelId)
            ->min('price1') ?? 0;

        // Update hall lowest price
        $lowestHallPrice = Hall::where('hotel_id', $hotelId)
            ->min('price') ?? 0;

        // Update the hotel record
        $hotel->update([
            'room_lowest_price' => $lowestRoomPrice,
            'hall_lowest_price' => $lowestHallPrice
        ]);

        return true;
    } catch (\Exception $e) {
        \Log::error('Error updating hotel lowest prices: ' . $e->getMessage(), [
            'hotel_id' => $hotelId
        ]);
        return false;
    }
}