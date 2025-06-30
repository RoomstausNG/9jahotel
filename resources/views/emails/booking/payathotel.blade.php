<div style="font-family: Arial, sans-serif; background: #fff; padding: 0; margin: 0;">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('emails.responsive-email-styles')

    <div style="text-align:center; padding: 24px 0 8px 0;">
        <img src="{{ asset('img/9jahotel_logo.png') }}" alt="9jahotel Logo" style="height: 60px;">
    </div>
    <div class="email-container">
        <h1>Enjoy your stay at {{ $invoice->hotel->name }}</h1>
        <div class="booking-number">
            Booking number is <span>{{ $invoice->reference }}</span>
        </div>
        <div style="text-align: center; margin-bottom: 1.5rem;">
            Thank you <b style="text-transform:uppercase">{{ $invoice->customer->name }}</b>! Your booking at <span
                style="color:#d60000">{{ $invoice->hotel->city->name }}</span> is <b>received</b>.
        </div>
        <div style="display: flex; gap: 24px; align-items: flex-start;">
            <img src="{{ getCloudinaryUrl($invoice->hotel->background_image) ?? 'https://9jahotel.com/default-hotel.jpg' }}"
                alt="Hotel Image" style="width: 220px; height: 150px; object-fit: cover; border-radius: 8px;">
            <div style="flex:1;  border-radius: 8px; padding: 12px 18px; font-size: 18px;">
                <div style="font-weight: bold; text-align: center;">*** UNCONFIRMED BOOKING ***</div>
                <div style="font-weight: bold;">{{ $invoice->hotel->name }}</div>
                <div>{{ $invoice->hotel->address }}</div>
                <div style="margin-top: 8px;">Booking Assistance: <b>0700 700 2424</b></div>
            </div>
        </div>
        <div style="margin-top: 24px; display: flex; justify-content: space-between;">
            <div>
                <div>Check-In<br><span
                        style="color:#d60000; font-weight:bold;">{{ $invoice->checked_in->format('l, F j, Y') }}</span><br>After
                    12.00 NOON</div>
                <div style="margin-top: 12px;">Room Reserved: <span
                        style="color:#d60000;">{{ $invoice->bookings->pluck('bookable.number')->implode(', ') }}</span>
                </div>
                <div>Booked by: <span style="color:#d60000;">{{ $invoice->customer->name }}</span></div>
                <div>Amount Paid: <span style="color:#d60000;">₦{{ number_format($invoice->amount, 2) }}</span></div>
            </div>
            <div>
                <div>Check-Out<br><span
                        style="color:#d60000; font-weight:bold;">{{ $invoice->checked_out->format('l, F j, Y') }}</span><br>By
                    or Before 11.00AM</div>
                <div style="margin-top: 12px;">Amount Due on Arrival at Hotel: <span
                        style="color:#d60000; font-weight:bold;">₦{{ number_format($invoice->amount_due, 2) }}</span>
                </div>
            </div>
        </div>
        <div style="margin-top: 24px;">
            <b>RESERVATION NOTE:</b><br>
            <span style="font-size: 0.98rem;">
                This booking has been received by the hotel. This is an <b>unsecured reservation</b> and may be
                subjected to random cancellation by the hotel. At 9jahotel.com, we wanted you to avoid possible
                disappointments during your travel, we will therefore suggest you make payment for this booking to
                confirm your travel itinerary to the hotel. To pay for this booking now, <a
                    href="{{ route('paynow', $invoice->reference) }}" style="color:#d60000;">click
                    here</a>. If your travel plans change, be assured that you will be entitled to refund of payment
                already made within 12 hours of cancellation. Cancellation is still possible after your first night stay
                if you do not love the accommodation. A token may be charged for refund processing. For more details on
                refundable bookings, see <a href="{{ route('terms') }}"
                    style="color:#0066cc;">{{ route('terms') }}</a>
            </span>
        </div>
        <div style="margin-top: 16px; color:#d60000;">
            <a href="{{ route('cancel.booking', $invoice->reference) }}" style="color:#d60000;">Click here</a>
            to cancel this booking.
        </div>
        <div style="margin-top: 32px; text-align: center; color: #d60000; font-style: italic; font-size: 1.1rem;">
            Thank you and best wishes during your stay
        </div>
    </div>
</div>
