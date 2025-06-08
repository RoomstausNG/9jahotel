<div style="font-family: Arial, sans-serif; background: #f7fafc; padding: 32px;">
    <div
        style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #0001; overflow: hidden;">
        <div style="background: #d60000; color: #fff; padding: 32px 24px 20px 24px; text-align: center;">
            <h2 style="margin: 0; font-size: 2rem; font-weight: bold; letter-spacing: 1px;">A Booking Has Been Cancelled
            </h2>
            <div style="margin-top: 8px; font-size: 1.1rem; opacity: 0.95;">Reference:
                <strong>{{ $invoice->reference }}</strong>
            </div>
        </div>
        <div style="padding: 28px 24px 10px 24px;">
            <h3 style="color: #d60000; font-size: 1.2rem; margin-bottom: 8px; margin-top: 0;">Customer Details</h3>
            <table style="width: 100%; font-size: 1rem; margin-bottom: 18px;">
                <tr>
                    <td style="padding: 4px 0; color: #222;"><strong>Name:</strong></td>
                    <td style="padding: 4px 0; color: #444;">{{ $invoice->customer->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #222;"><strong>Email:</strong></td>
                    <td style="padding: 4px 0; color: #444;">{{ $invoice->customer->email ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #222;"><strong>Phone:</strong></td>
                    <td style="padding: 4px 0; color: #444;">{{ $invoice->customer->phone ?? '-' }}</td>
                </tr>
            </table>
            <h3 style="color: #d60000; font-size: 1.2rem; margin-bottom: 8px; margin-top: 24px;">Booking Details</h3>
            <table style="width: 100%; font-size: 1rem; margin-bottom: 18px; border-collapse: collapse;">
                <thead>
                    <tr style="background: #fbeaea; color: #d60000;">
                        <th style="padding: 6px 8px; text-align: left; font-size: 1rem;">Room</th>
                        <th style="padding: 6px 8px; text-align: left; font-size: 1rem;">Check-in</th>
                        <th style="padding: 6px 8px; text-align: left; font-size: 1rem;">Check-out</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->bookings as $booking)
                        <tr style="border-bottom: 1px solid #e0e7ef;">
                            <td style="padding: 6px 8px;">
                                {{ $booking->bookable_type == 'App\\Models\\Room' ? $booking->bookable->number : $booking->bookable->name }}
                            </td>
                            <td style="padding: 6px 8px;">{{ $booking->checked_in->format('M d, Y h:iA') }}</td>
                            <td style="padding: 6px 8px;">{{ $booking->checked_out->format('M d, Y h:iA') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-bottom: 18px;">
                <strong>Total Paid:</strong> <span
                    style="color: #d60000; font-size: 1.1rem;">₦{{ number_format($invoice->amount ?? 0) }}</span>
            </div>
            <h3 style="color: #d60000; font-size: 1.2rem; margin-bottom: 8px; margin-top: 24px;">Cancellation Reason
            </h3>
            <div style="margin-bottom: 18px;">
                <strong>Reason:</strong> <span>{{ $other_reason ?: $reason }}</span>
            </div>
            <div style="margin-bottom: 18px; color: #d60000;">
                Please update your records. If you have any questions, contact 9jahotel support.
            </div>
        </div>
        <div
            style="background: #fbeaea; color: #d60000; text-align: center; padding: 18px 24px; font-size: 1rem; border-top: 1px solid #e0e7ef;">
            <strong>9jahotel Notification</strong><br>
            <span style="font-size: 0.95rem; color: #d60000;">This is an automated notification. The above booking has
                been cancelled by the customer.</span>
        </div>
    </div>
</div>
