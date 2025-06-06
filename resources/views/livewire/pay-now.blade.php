<div class="max-w-xl mx-auto bg-white shadow rounded p-8 mt-10">
    <h2 class="text-2xl font-bold mb-4">Pay for Booking</h2>
    <div class="mb-4">
        <div><strong>Hotel:</strong> {{ $invoice->hotel->name }}</div>
        <div><strong>Customer:</strong> {{ $invoice->customer->firstname }} {{ $invoice->customer->lastname }}</div>
        <div><strong>Booking Reference:</strong> {{ $invoice->reference }}</div>
        <div><strong>Amount:</strong> ₦{{ number_format($invoice->amount, 2) }}</div>
        <div><strong>Status:</strong> {{ ucfirst($invoice->status) }}</div>
    </div>
    <div class="mb-4">
        <strong>Rooms:</strong>
        <ul class="list-disc ml-6">
            @foreach ($invoice->bookings as $booking)
                <li>{{ $booking->bookable->number ?? $booking->bookable->name }}</li>
            @endforeach
        </ul>
    </div>
    @if (session('error'))
        <div class="bg-red-100 text-red-700 p-2 rounded mb-4">{{ session('error') }}</div>
    @endif
    <form wire:submit.prevent="pay">
        <button type="submit" class="bg-primary-700 hover:bg-primary-800 text-white px-6 py-2 rounded font-semibold">Pay
            Now</button>
    </form>
</div>
