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
        @if (isset($customMessage) && $customMessage)
            <p style="white-space: pre-line;">{!! nl2br(e($customMessage)) !!}</p>
        @endif
        <table width="100%" cellpadding="8" cellspacing="0" border="1"
            style="border-collapse:collapse;margin-top:20px;">
            <thead style="background:#f1f1f1;">
                <tr>
                    <th>Room/Hall</th>
                    <th>Type</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Guests</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bookings as $booking)
                    <tr>
                        <td>
                            @if ($booking->type === 'room')
                                {{ $booking->bookable->number ?? ($booking->bookable->name ?? '-') }}
                            @else
                                {{ $booking->bookable->name ?? '-' }}
                            @endif
                        </td>
                        <td>{{ strtoupper($booking->type) }}</td>
                        <td>{{ $booking->checked_in }}</td>
                        <td>{{ $booking->checked_out }}</td>
                        <td>{{ $booking->guests ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p>If you have any questions, please contact the hotel directly.</p>
        <p>Thank you for choosing us!</p>
        <div class="footer">
            &copy; {{ date('Y') }} 9jahotel. All rights reserved.
        </div>
    </div>
</body>

</html>
