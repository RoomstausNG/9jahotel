<x-mail::message>
    # Special Coupon for You!

    {{ $message }}

    **Coupon Details:**
    - **Code:** {{ strtoupper($coupon->code) }}
    - **Discount:** {{ $coupon->percent }}%
    - **Valid From:** {{ $coupon->valid_from->format('M d, Y') }}
    - **Valid Until:** {{ $coupon->valid_till->format('M d, Y') }}
    @if ($coupon->highest_value)
        - **Maximum Discount:** ₦{{ number_format($coupon->highest_value, 2) }}
    @endif
    @if ($coupon->limit_usage != -1)
        - **Usage Limit:** {{ $coupon->limit_usage }} times
    @endif

    Use this coupon code during checkout to enjoy your discount!

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
