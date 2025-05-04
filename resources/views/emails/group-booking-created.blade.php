<!-- ...existing code... -->
@php
    // Arrange by importance: hotels, city, dates, options, booleans, created_at
    $hotelsList = $hotels->pluck('name')->join(', ');
@endphp

<h2>New Group Booking Created</h2>

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%;">
    <tr>
        <th align="left">Code</th>
        <td>{{ $booking->code }}</td>
    </tr>
    <tr>
        <th align="left">Organisation</th>
        <td>{{ $booking->organisation }}</td>
    </tr>
    <tr>
        <th align="left">Phone</th>
        <td>{{ $booking->phone }}</td>
    </tr>
    <tr>
        <th align="left">Email</th>
        <td>{{ $booking->email }}</td>
    </tr>
    <tr>
        <th align="left">Expected Guests</th>
        <td>{{ $booking->expected_guest }}</td>
    </tr>
    <tr>
        <th align="left">Hotels</th>
        <td>{{ $hotelsList }}</td>
    </tr>
    <tr>
        <th align="left">City</th>
        <td>{{ $city?->name }}</td>
    </tr>
    <tr>
        <th align="left">Room Needed</th>
        <td>{{ $booking->room_needed }}</td>
    </tr>
    <tr>
        <th align="left">Room Type</th>
        <td>{{ $booking->room_type }}</td>
    </tr>
    <tr>
        <th align="left">Room Dates</th>
        <td>{{ $booking->room_from }} to {{ $booking->room_to }}</td>
    </tr>
    <tr>
        <th align="left">Room Budget</th>
        <td>{{ $booking->room_budget }}</td>
    </tr>
    <tr>
        <th align="left">Need Hall?</th>
        <td>{{ $booking->need_hall ? 'Yes' : 'No' }}</td>
    </tr>
    <tr>
        <th align="left">Hall Capacity</th>
        <td>{{ $booking->hall_capacity }}</td>
    </tr>
    <tr>
        <th align="left">Hall Dates</th>
        <td>{{ $booking->hall_from }} to {{ $booking->hall_to }}</td>
    </tr>
    <tr>
        <th align="left">Hall Budget</th>
        <td>{{ $booking->hall_budget }}</td>
    </tr>
    <tr>
        <th align="left">Need Meal?</th>
        <td>{{ $booking->need_meal ? 'Yes' : 'No' }}</td>
    </tr>
    <tr>
        <th align="left">Meal Attendees</th>
        <td>{{ $booking->meal_attendees }}</td>
    </tr>
    <tr>
        <th align="left">Meal Dates</th>
        <td>{{ $booking->meal_from }} to {{ $booking->meal_to }}</td>
    </tr>
    <tr>
        <th align="left">Meal Budget</th>
        <td>{{ $booking->meal_budget }}</td>
    </tr>
    <tr>
        <th align="left">Meal Options</th>
        <td>{{ is_array($booking->meal_options) ? implode(', ', $booking->meal_options) : $booking->meal_options }}
        </td>
    </tr>
    <tr>
        <th align="left">Allow External Bids?</th>
        <td>{{ $booking->allow_external_bids ? 'Yes' : 'No' }}</td>
    </tr>
    <tr>
        <th align="left">Created At</th>
        <td>{{ $booking->created_at }}</td>
    </tr>
</table>

<p>Thank you.</p>
<!-- ...existing code... -->
