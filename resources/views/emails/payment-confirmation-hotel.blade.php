@component('mail::message')
    <div style="text-align:center; margin-bottom: 24px;">
        <img src="{{ asset('img/9jahotel_logo.png') }}" alt="9jaHotel Logo" style="height:60px; margin:auto;">
    </div>

    # New Booking Payment Received

    Dear {{ $hotel->name }} Team,

    A new payment has been received for a booking at your hotel. Please find the details below:

    @component('mail::panel')
        **Booking Details:**
        - **Reference:** {{ $invoice->reference }}
        - **Customer:** {{ $customer->name }} ({{ $customer->email }})
        - **Check-in:** {{ $invoice->checked_in->format('M d, Y') }}
        - **Check-out:** {{ $invoice->checked_out->format('M d, Y') }}
        - **Amount Paid:** ₦{{ number_format($invoice->amount) }}
    @endcomponent

    @component('mail::table')
        | Room | Type | Guests |
        |------|------|--------|
        @foreach ($bookings as $booking)
            | {{ $booking->bookable->number ?? '-' }} | {{ $bookable->type?->name ?? $bookable->name }} |
            {{ $booking->adults ?? 1 }} |
        @endforeach
    @endcomponent

    Please ensure the room(s) are prepared for the guest's arrival.

    Thank you for partnering with 9jaHotel.

    Best regards,

    9jaHotel Team
@endcomponent
