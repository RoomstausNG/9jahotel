@component('mail::message')
    # Refund Request Received

    Dear {{ $customer->name }},

    We have received your refund request for your booking at {{ $hotel->name }} (Reference: {{ $invoice->reference }}).

    Our customer care team is processing your request and your refund will be sent to the specified account within 12 hours
    of completing the refund process.

    If you have any questions, please reply to this email or contact our support team.

    Thank you for choosing 9jaHotel.

    @component('mail::panel')
        **Booking Details:**
        - Hotel: {{ $hotel->name }}
        - Check-in: {{ $invoice->checked_in->format('M d, Y') }}
        - Check-out: {{ $invoice->checked_out->format('M d, Y') }}
        - Amount Paid: ₦{{ number_format($invoice->amount) }}
    @endcomponent

    Best regards,

    9jaHotel Customer Care
@endcomponent
