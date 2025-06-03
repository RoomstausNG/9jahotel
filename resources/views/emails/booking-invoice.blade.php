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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        th {
            background: #f1f1f1;
            text-align: left;
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

        {{-- Booking summary table --}}
        <table width="100%" cellpadding="8" cellspacing="0" border="1"
            style="border-collapse:collapse;margin-top:20px;">
            <tbody>
                <tr>
                    <th align="left">Room Reservation</th>
                    <td>
                        @if (isset($roomSummary))
                            {{ $roomSummary }}
                        @else
                            {{ $bookings->count() }}
                            @php
                                $types = $bookings
                                    ->map(function ($b) {
                                        return strtoupper(optional($b->bookable->type)->name ?? ($b->type ?? 'ROOM'));
                                    })
                                    ->unique()
                                    ->implode(', ');
                            @endphp
                            {{ $types }} Room(s)
                        @endif
                    </td>
                </tr>
                <tr>
                    <th align="left">Details</th>
                    <td>
                        @php
                            $details = $bookings
                                ->map(function ($b) {
                                    if ($b->type === 'room') {
                                        return $b->bookable->number ?? ($b->bookable->name ?? '-');
                                    } else {
                                        return $b->bookable->name ?? '-';
                                    }
                                })
                                ->implode(', ');
                        @endphp
                        {{ $details }}
                    </td>
                </tr>
                <tr>
                    <th align="left">Check-in</th>
                    <td>
                        @if (isset($checked_in))
                            {{ $checked_in }}
                        @else
                            {{ $invoice->checked_in->format('D d M, Y') }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th align="left">Check-out</th>
                    <td>
                        @if (isset($checked_out))
                            {{ $checked_out }}
                        @else
                            {{ $invoice->checked_out->format('D d M, Y') }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th align="left">Booking number</th>
                    <td>{{ $reference ?? ($invoice->reference ?? '-') }}</td>
                </tr>
                <tr>
                    <th align="left">Booked By</th>
                    <td>{{ $customer->firstname }} {{ $customer->lastname }}</td>
                </tr>
            </tbody>
        </table>

        <p>If you have any questions, please contact us via <a
                href="mailto:support@9jahotel.com">support@9jahotel.com</a> .</p>
        <p>Thank you for choosing us!</p>
        <div class="footer">
            &copy; {{ date('Y') }} 9jahotel. All rights reserved.
        </div>
    </div>
</body>

</html>
