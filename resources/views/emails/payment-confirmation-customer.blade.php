@component('mail::message')
    <div style="text-align:center; margin-bottom: 24px;">
        <img src="{{ asset('img/9jahotel_logo.png') }}" alt="9jaHotel Logo" style="height:60px; margin:auto;">
    </div>

    # Payment Confirmation

    Dear {{ $customer->name }},

    We are pleased to confirm that your payment for your booking at **{{ $hotel->name }}** has been received successfully.

    @component('mail::panel')
        **Booking Details:**
        - **Reference:** {{ $invoice->reference }}
        - **Hotel:** {{ $hotel->name }}
        - **Address:** {{ $hotel->address }}
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

    If you have any questions, please contact our support team or reply to this email.

    Thank you for choosing 9jaHotel!

    Best regards,

    9jaHotel Team
@endcomponent
