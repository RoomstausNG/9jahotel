<x-mail::message>
    ROOMSTATUS PAYMENT REMINDER

    # New Invoice: {{ $invoice->reference }}

    Dear {{ $hotel->name }},

    A new invoice has been created for your account.

    **Invoice Details:**
    - Reference: {{ $invoice->reference }}
    - Amount: {{ number_format($invoice->amount) }} NGN
    - Subscription: {{ $invoice->subscription }}
    - Valid From: {{ $invoice->from->format('M d, Y') }}
    - Valid To: {{ $invoice->to->format('M d, Y') }}
    - Description: {{ $invoice->description }}

    ** Name: ** RoomStatus LTD
    ** Bank: ** Sterling Bank
    ** Account Number: ** 0116006836

    {{-- <x-mail::button :url="$paymentUrl">
        Make Payment
    </x-mail::button> --}}

    Please make your payment before the due date to ensure uninterrupted service.

    Thank you,<br>
    Natsat Siyaka(Ms)<br>
    09068389486

    ROOMSTATUS HOTEL MANAGEMENT SOFTWARE IS PROMOTED BY 9JAHOTEL.COM LTD AND VIA OPAY NIGERIA DEVICES.
</x-mail::message>
