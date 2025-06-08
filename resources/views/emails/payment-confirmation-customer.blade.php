<div style="font-family: Arial, sans-serif; background: #fff; padding: 0; margin: 0;">
    <div style="text-align:center; padding: 24px 0 8px 0;">
        <img src="{{ asset('img/9jahotel_logo.png') }}" alt="9jahotel Logo" style="height: 60px;">
    </div>
    <div style="max-width: 650px; margin: 0 auto; background: #fff; padding: 32px;">
        <h1 style="color: #009999; text-align: center; font-size: 2rem; margin-bottom: 0.5rem;">Payment Confirmation</h1>
        <div style="text-align: center; font-size: 1.2rem; margin-bottom: 1.5rem;">
            Dear <b>{{ $customer->name }}</b>,<br>
            We are pleased to confirm that your payment for your booking at <b>{{ $hotel->name }}</b> has been received
            successfully.
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
                    <td><b>Hotel:</b></td>
                    <td>{{ $hotel->name }}</td>
                </tr>
                <tr>
                    <td><b>Address:</b></td>
                    <td>{{ $hotel->address }}</td>
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
            If you have any questions, please contact our support team or reply to this email.<br><br>
            Thank you for choosing 9jahotel!
        </div>
        <div style="margin-top: 32px; color: #888; font-size: 0.95rem; text-align: right;">
            Best regards,<br>
            9jahotel Team
        </div>
    </div>
</div>
