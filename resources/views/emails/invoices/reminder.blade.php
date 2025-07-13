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


    Please make your payment as soon as possible to avoid any service interruptions.
    kindly call us at <a href="+23407007002424" class="+0700 700 2424"></a> or email us at
    <a href="mailto:ask@9jahotel.com" class="">ask@9jahotel.com</a> . if you have any
    questions or need assistance with the payment process.

    Thank you,<br>
    {{ config('app.name') }}
</x-mail::message>
