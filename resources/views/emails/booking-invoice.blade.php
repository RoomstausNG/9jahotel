{{-- filepath: resources/views/emails/booking-invoice.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Your Booking Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            color: #222;
            margin: 0;
            padding: 0;
        }

        .container {
            background: #fff;
            max-width: 600px;
            margin: 30px auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px #e0e0e0;
            padding: 32px;
        }

        h2 {
            color: #007bff;
        }

        ul {
            padding-left: 20px;
        }

        li {
            margin-bottom: 8px;
        }

        .footer {
            margin-top: 32px;
            font-size: 13px;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Booking Invoice</h2>
        <p>Hello {{ $customer->firstname }} {{ $customer->lastname }},</p>
        <p>Thank you for your booking via <strong>9jahotel</strong>.</p>
        <ul>
            <li><strong>Hotel:</strong> {{ $hotel->name }}</li>
            <li><strong>Booking Reference:</strong> {{ $invoice->reference }}</li>
            <li><strong>Status:</strong> {{ ucfirst($invoice->status) }}</li>
            <li><strong>Total Amount:</strong> ₦{{ number_format($invoice->amount, 2) }}</li>
            <li><strong>Booking Type:</strong>
                @if ($bookings->first() && $bookings->first()->type === 'room')
                    Room
                @else
                    Hall
                @endif
            </li>
            @if ($bookings->first() && $bookings->first()->type === 'room')
                <li><strong>Number of Rooms:</strong> {{ $bookings->count() }}</li>
                <li><strong>Number of Nights:</strong>
                    @php
                        $first = $bookings->first();
                        $nights = $first
                            ? \Carbon\Carbon::parse($first->checked_in)->diffInDays(
                                \Carbon\Carbon::parse($first->checked_out),
                            )
                            : 1;
                    @endphp
                    {{ $nights }}
                </li>
            @else
                <li><strong>Hall Name:</strong> {{ $bookings->first() ? $bookings->first()->bookable->name : '' }}</li>
                <li><strong>Event Date:</strong> {{ $bookings->first() ? $bookings->first()->checked_in : '' }}</li>
            @endif
        </ul>
        <p>If you have any questions, please contact the hotel directly.</p>
        <p>Thank you for choosing us!</p>
        <div class="footer">
            &copy; {{ date('Y') }} 9jahotel. All rights reserved.
        </div>
    </div>
</body>

</html>
