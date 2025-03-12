@props(['hall', 'id', 'date'])
<div id="{{ $id }}" role="tooltip"
    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-left text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
    {{-- {!! $content !!} --}}

    <div class="">
        <p class='space-y-1 text-left nowrap'>

            <span class='font-bold underline uppercase nowrap'>{{ 'Hall ' . $hall->number . ' Details: ' }}</span>
            <br>
            <span class='inline-block capitalize'>{{ 'Hall Rate: ' . formatAsNaira($hall->price) }}</span>
            <br>
            <span class='inline-block capitalize'>Status:
                <span class="{{ 'text-' . $hall->status }} px-2 py-1 rounded">
                    {{ hallStatus($hall, $date) }}</span>
            </span> <br>

            @php
                $filteredBooking = $hall->getBookingByDate($date);
            @endphp
            @if ($filteredBooking)
                <span
                    class='inline-block capitalize'>{{ 'Occupant:    ' . $filteredBooking->customer?->firstname . ' ' . $filteredBooking->customer?->filteredname }}</span>
                <br>
                <span class='inline-block capitalize'>{{ 'Telephone:    ' . $filteredBooking->customer?->phone }}</span>
                <br>
                <span
                    class='inline-block capitalize'>{{ 'Checked-In Date:  ' . $filteredBooking->checked_in->format('d-M-Y') }}</span>
                <br>
                <span
                    class='inline-block capitalize'>{{ 'Checked-Out Date:  ' . $filteredBooking->checked_out->format('d-M-Y') }}</span>
                <br>
            @endif
            {{-- <span class='capitalize'>{{ 'Hall Character: ' . $hall->character }}</span> --}}
        </p>
    </div>
    <div class="tooltip-arrow" data-popper-arrow></div>
</div>
