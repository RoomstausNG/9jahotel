<div style="font-family: 'Segoe UI', Arial, sans-serif; background: #f7fafc; padding: 32px;">
    <div
        style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #0001; overflow: hidden;">
        <div style="background: #009999; color: #fff; padding: 32px 24px 20px 24px; text-align: center;">
            <h2 style="margin: 0; font-size: 2rem; font-weight: bold; letter-spacing: 1px;">Refund Request for Booking
                Invoice</h2>
            <div style="margin-top: 8px; font-size: 1.1rem; opacity: 0.95;">Reference:
                <strong>{{ $data['invoice']->reference }}</strong>
            </div>
            <div style="margin-top: 4px; font-size: 1rem;">Refund Status:
                <strong>{{ $data['refund']->status ?? '-' }}</strong>
            </div>
        </div>
        <div style="padding: 28px 24px 10px 24px;">
            <h3 style="color: #009999; font-size: 1.2rem; margin-bottom: 8px; margin-top: 0;">Hotel & Customer Details
            </h3>
            <table style="width: 100%; font-size: 1rem; margin-bottom: 18px;">
                <tr>
                    <td style="padding: 4px 0; color: #222;"><strong>Hotel:</strong></td>
                    <td style="padding: 4px 0; color: #444;">{{ $data['invoice']->hotel->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #222;"><strong>Customer:</strong></td>
                    <td style="padding: 4px 0; color: #444;">{{ $data['invoice']->customer->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #222;"><strong>Customer Email:</strong></td>
                    <td style="padding: 4px 0; color: #444;">{{ $data['invoice']->customer->email ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #222;"><strong>Customer Phone:</strong></td>
                    <td style="padding: 4px 0; color: #444;">{{ $data['invoice']->customer->phone ?? '-' }}</td>
                </tr>
            </table>

            <h3 style="color: #009999; font-size: 1.2rem; margin-bottom: 8px; margin-top: 24px;">Booking Details</h3>
            <table style="width: 100%; font-size: 1rem; margin-bottom: 18px; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f0fdfa; color: #009999;">
                        <th style="padding: 6px 8px; text-align: left; font-size: 1rem;">Room</th>
                        <th style="padding: 6px 8px; text-align: left; font-size: 1rem;">Check-in</th>
                        <th style="padding: 6px 8px; text-align: left; font-size: 1rem;">Check-out</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['invoice']->bookings as $booking)
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
                    style="color: #009999; font-size: 1.1rem;">₦{{ number_format($data['original_amount'] ?? ($data['invoice']->amount ?? 0)) }}</span>
            </div>
            <div style="margin-bottom: 18px;">
                <strong>Refund Amount (10% processing fee deducted):</strong> <span
                    style="color: #009999; font-size: 1.1rem;">₦{{ number_format($data['refund_amount'] ?? ($data['invoice']->amount * 0.9 ?? 0)) }}</span>
            </div>

            <h3 style="color: #009999; font-size: 1.2rem; margin-bottom: 8px; margin-top: 24px;">Refund Request Details
            </h3>
            <table style="width: 100%; font-size: 1rem;">
                <tr>
                    <td style="padding: 4px 0; color: #222;"><strong>Bank Name:</strong></td>
                    <td style="padding: 4px 0; color: #444;">{{ $data['bank_name'] }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #222;"><strong>Account Number:</strong></td>
                    <td style="padding: 4px 0; color: #444;">{{ $data['account_no'] }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #222;"><strong>Account Name:</strong></td>
                    <td style="padding: 4px 0; color: #444;">{{ $data['account_name'] }}</td>
                </tr>

                @if (isset($data['refund']->other_reason) && $data['refund']->other_reason)
                    <tr>
                        <td style="padding: 4px 0; color: #222;"><strong>Reason:</strong></td>
                        <td style="padding: 4px 0; color: #444;">{{ $data['refund']->other_reason }}</td>
                    </tr>
                @else
                    <tr>
                        <td style="padding: 4px 0; color: #222;"><strong>Reason:</strong></td>
                        <td style="padding: 4px 0; color: #444;">{{ $data['reason'] }}</td>
                    </tr>
                @endif
            </table>
        </div>
        <div
            style="background: #f0fdfa; color: #009999; text-align: center; padding: 18px 24px; font-size: 1rem; border-top: 1px solid #e0e7ef;">
            <strong>9jahotel Refund Desk</strong><br>
            <span style="font-size: 0.95rem; color: #009999;">This is an automated notification. Please review and
                process the refund request promptly.</span>
        </div>
    </div>
</div>
