<x-mail::message>
    # Payment Reminder: Invoice {{ $invoice->reference }}

    Dear {{ $hotel->name }},

    This is a friendly reminder about your pending invoice.

    **Invoice Details:**
    - Reference: {{ $invoice->reference }}
    - Amount: {{ number_format($invoice->amount) }} NGN
    - Subscription: {{ $invoice->subscription }}
    - Valid From: {{ $invoice->from->format('M d, Y') }}
    - Valid To: {{ $invoice->to->format('M d, Y') }}
    - Description: {{ $invoice->description }}

    <x-mail::button :url="$paymentUrl">
        Make Payment Now
    </x-mail::button>

    Please make your payment as soon as possible to avoid any service interruptions.

    Thank you,<br>
    {{ config('app.name') }}
</x-mail::message>
