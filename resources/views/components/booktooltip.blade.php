@props(['room', 'id', 'date'])
<div id="{{ $id }}" role="tooltip"
    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-left text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
    {{-- {!! $content !!} --}}

    <div class="">
        <p class='space-y-1 text-left nowrap'>

            <span class='font-bold underline uppercase nowrap'>{{ 'Room ' . $room->number . ' Details: ' }}</span>
            <br>
            {{-- <span class='uppercase'>{{ 'Room TYPE: ' . $room->type->name }}</span> <br> --}}
            <span class='inline-block capitalize'>{{ 'Room Rate: ' . formatAsNaira($room->getPrice()) }}</span>
            <br>
            <span class='inline-block capitalize'>Status:
                <span class="{{ 'text-' . $room->status }} px-2 py-1 rounded">
                    {{ roomStatus($room, $date) }}</span>
            </span> <br>
            <span class='inline-block capitalize'>{{ 'Bed Type: ' . $room->bed_type }}</span><br>
            <span class='inline-block capitalize'>{{ 'Room Floor: ' . $room->floor->name }}</span> <br>
            @php
                $filteredBooking = $room->dateStatus($date)?->booking;
                // $filteredBooking = $room->getBookingByDate($date);

                $stat = $room->statuses()->whereDate('date', $date)->first();
                // $agentName = $filteredBooking->agent->firstname . ' ' . $filteredBooking->agent->lastname;
            @endphp
            @if ($filteredBooking)
                <span
                    class='inline-block capitalize'>{{ 'Occupant:    ' . $filteredBooking->customer?->firstname . ' ' . $filteredBooking->customer?->lastname }}</span>
                <br>
                <span class='inline-block capitalize'>{{ 'Telephone:    ' . $filteredBooking->customer?->phone }}</span>
                <br>
                <span
                    class='inline-block capitalize'>{{ 'Checked-In Date:  ' . $filteredBooking->checked_in?->format('d-M-Y') }}</span>
                <br>
                <span
                    class='inline-block capitalize'>{{ 'Checked-Out Date:  ' . $filteredBooking->checked_out?->format('d-M-Y') }}</span>
                <br>
                @if ($filteredBooking->agent)
                    <span
                        class='capitalize'>{{ 'Booked By: ' . ucfirst($filteredBooking->agent->firstname . ' ' . $filteredBooking->agent->lastname) }}</span>
                @else
                    <span class='capitalize'>Booked By: Guest</span>
                @endif
            @endif

        </p>
    </div>
    <div class="tooltip-arrow" data-popper-arrow></div>
</div>
