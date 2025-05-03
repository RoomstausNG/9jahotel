<!-- resources/views/emails/booking-payment.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8fafc;
            color: #222;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #e2e8f0;
            padding: 32px;
        }

        h1 {
            color: #2563eb;
        }

        .details {
            margin: 24px 0;
        }

        .details th,
        .details td {
            padding: 8px 12px;
            text-align: left;
        }

        .details th {
            background: #f1f5f9;
        }

        .footer {
            margin-top: 32px;
            font-size: 0.95em;
            color: #64748b;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Payment Confirmation</h1>
        <p>Dear {{ $customer->name ?? 'Customer' }},</p>
        <p>We have received your payment for the booking below. Thank you for choosing
            {{ $hotel->name ?? 'our hotel' }}!</p>
        <table class="details" width="100%">
            <tr>
                <th>Invoice Reference</th>
                <td>{{ $invoice->reference }}</td>
            </tr>
            <tr>
                <th>Amount Paid</th>
                <td>&#8358;{{ number_format($invoice->amount, 2) }}</td>
            </tr>
            <tr>
                <th>Hotel</th>
                <td>{{ $hotel->name }}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ $invoice->created_at->format('F j, Y') }}</td>
            </tr>
            <tr>
                <th>Check-In Date</th>
                <td>
                    @if(isset($bookings) && count($bookings))
                        {{ $bookings[0]->check_in ?? '' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Check-Out Date</th>
                <td>
                    @if(isset($bookings) && count($bookings))
                        {{ $bookings[0]->check_out ?? '' }}
                    @endif
                </td>
            </tr>
        </table>
        <h3>Booking Details</h3>
        <ul>
            @foreach ($bookings as $booking)
                <li>
                    @if (!empty($booking->bookable->name))
                        {{ $booking->bookable->name }}
                    @else
                        Room Number {{ $booking->bookable->number ?? '' }}
                    @endif
                    ({{ $booking->check_in }} to {{ $booking->check_out }})
                </li>
            @endforeach
        </ul>
        <div class="footer">
            If you have any questions, please contact us at {{ $hotel->email }}.<br>
            &copy; {{ date('Y') }} {{ $hotel->name }}. All rights reserved.
        </div>
    </div>
</body>

</html>
