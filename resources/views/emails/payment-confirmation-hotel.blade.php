<div style="font-family: Arial, sans-serif; background: #fff; padding: 0; margin: 0;">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('emails.responsive-email-styles')

    <div style="text-align:center; padding: 24px 0 8px 0;">
        <img src="{{ asset('img/9jahotel_logo.png') }}" alt="9jahotel Logo" style="height: 60px;">
    </div>
    <div class="email-container">
        <h1 style="color: #009999;">New Booking Payment Received</h1>
        <div class="thank-you">
            Dear <b>{{ $hotel->name }}</b> Team,<br>
            A new payment has been received for a booking at your hotel. Please find the details below:
        </div>
        <div
            style="border: 1px solid #eee; border-radius: 8px; padding: 18px 24px; margin-bottom: 18px; background: #f8fdfa;">
            <div style="font-weight: bold; color: #009999; margin-bottom: 8px;">Booking Details</div>
            <table style="width: 100%; font-size: 1rem;">
                <tr>
                    <td><b>Reference:</b></td>
                    <td>{{ $invoice->reference }}</td>
                </tr>
                <tr>
                    <td><b>Customer:</b></td>
                    <td>{{ $customer->name }} ({{ $customer->email }})</td>
                </tr>
                <tr>
                    <td><b>Check-in:</b></td>
                    <td>{{ $invoice->checked_in->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td><b>Check-out:</b></td>
                    <td>{{ $invoice->checked_out->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td><b>Amount Paid:</b></td>
                    <td>₦{{ number_format($invoice->amount) }}</td>
                </tr>
            </table>
        </div>
        <div style="margin-bottom: 18px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #009999; color: #fff;">
                        <th style="padding: 8px; text-align: left;">Room</th>
                        <th style="padding: 8px; text-align: left;">Type</th>
                        <th style="padding: 8px; text-align: left;">Guests</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $booking)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 8px;">{{ $booking->bookable->number ?? '-' }}</td>
                            <td style="padding: 8px;">
                                {{ $booking->bookable->type->name ?? ($booking->bookable->name ?? '-') }}</td>
                            <td style="padding: 8px;">{{ $booking->guest ?? 1 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top: 24px; font-size: 1rem;">
            Please ensure the room(s) are prepared for the guest's arrival.<br><br>
            Thank you for partnering with 9jahotel.
        </div>
        <div style="margin-top: 32px; color: #888; font-size: 0.95rem; text-align: right;">
            Best regards,<br>
            9jahotel Team
        </div>
    </div>
</div>
