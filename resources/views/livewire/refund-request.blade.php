<div class="min-h-screen bg-primary-700 flex flex-col items-center py-10">
    <!-- Top Section -->
    <div class="w-full max-w-3xl">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">Cancel Booking</h1>
            <p class="text-xl text-primary-100">Fill the form to request for your booking Cancellation</p>
        </div>

        <!-- Booking Card -->
        <div class="bg-white shadow-lg px-8 py-8 mb-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                <div>
                    <h2 class="text-3xl font-semibold mb-2">{{ $invoice->hotel->name }}</h2>
                    <div class="flex items-center text-gray-600 mb-2">
                        <svg class="w-5 h-5 mr-1 text-primary-600" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.657 16.657L13.414 21m0 0l-4.243-4.243m4.243 4.243V10.828m0 10.172C7.373 21 3 16.627 3 11.5S7.373 2 12 2s9 4.373 9 9.5-4.373 9.5-9 9.5z" />
                        </svg>
                        <span>{{ $invoice->hotel->address ?? 'Address' }}</span>
                    </div>
                    <span
                        class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded text-base">{{ $invoice->bookings->first()->bookable->type?->name . ' Room' ?? $invoice->bookings->first()->bookable->name }}</span>
                </div>
                <div class="mt-4 md:mt-0 md:ml-6">
                    <img src="{{ getCloudinaryUrl($invoice->hotel->background_image) }}"
                        alt="{{ $invoice->hotel->name }}" class="w-40 h-24 object-cover shadow">
                </div>
            </div>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center border-b pb-4 mb-4">
                <div class="mb-2 md:mb-0">
                    <span class="block text-gray-500 text-sm">Check In</span>
                    <span class="font-bold text-lg">{{ $invoice->checked_in->format('M. jS Y') }}</span>
                </div>
                <div class="hidden md:block border-l h-8 mx-6"></div>
                <div class="mb-2 md:mb-0">
                    <span class="block text-gray-500 text-sm">Check Out</span>
                    <span class="font-bold text-lg">{{ $invoice->checked_out->format('M. jS Y') }}</span>
                </div>
            </div>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-4">
                <div class="mb-2 md:mb-0">
                    <span class="block text-gray-500 text-sm">Guest</span>
                    <span class="font-bold text-lg">{{ $invoice->customer->guest ?? '1 Adult' }}</span>
                </div>
                <div class="mb-2 md:mb-0">
                    <span class="block text-gray-500 text-sm">Room Number(s)</span>
                    <span class="font-bold text-lg">
                        @php
                            $rooms = collect($invoice->bookings)->pluck('bookable.number')->filter()->implode(', ');
                        @endphp
                        {{ $rooms ?: 'N/A' }}
                    </span>
                </div>
            </div>
            <div class="flex justify-end">
                <div class="text-right">
                    <span class="block text-gray-500 text-sm">Total Paid</span>
                    <span class="text-primary-700 text-3xl font-bold">₦{{ number_format($invoice->amount ?? 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Refund Form -->
        @php
            $refundApplied = $invoice->refund()->exists();
            $isPaid = $invoice->status === 'paid';
            $checkin = $invoice->checked_in;
            $checkout = $invoice->checked_out;
            $now = now();
            // Calculate hours from now to check-in (positive if in future)
            $hoursToCheckin = $now->diffInHours($checkin, false);
            // Check if check-out date has passed (before today)
            $checkoutPassed = $checkout->lt($now->startOfDay());
        @endphp
        <div class="bg-white shadow-lg px-8 py-8">
            <h3 class="text-2xl font-semibold mb-6 text-primary-700">Cancel Booking</h3>
            @if ($refundApplied)
                <div class="bg-red-100 text-red-800 p-4 rounded text-center mb-4">
                    Refund has already been applied for this booking.
                </div>
            @elseif (!$isPaid)
                <div class="bg-yellow-100 text-yellow-800 p-4 rounded text-center mb-4">
                    Refunds can only be requested for bookings that are <b>paid</b>.
                </div>
            @elseif ($checkoutPassed)
                <div class="bg-yellow-100 text-yellow-800 p-4 rounded text-center mb-4">
                    Refunds can only be requested before the check-out date.
                </div>
            @else
                @if ($hoursToCheckin < 48)
                    <div class="bg-orange-100 text-orange-800 p-4 rounded text-center mb-4">
                        If you cancel this booking, you will receive full refund less 10% charge for processing. Your
                        refund will be sent to the same account you specified within 12 hours of completing the refund
                        process.
                    </div>
                @endif
                @if (session()->has('success'))
                    <div class="bg-green-100 text-green-800 p-2 rounded mb-4">{{ session('success') }}</div>
                @endif
                <form wire:submit="submit" class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-1">Why are you cancelling the booking?</label>
                        <select wire:model.live="reason"
                            class="bg-green-50 border border-primary-200 rounded w-full px-4 py-2 text-lg appearance-none"
                            required>
                            <option value="">Select</option>
                            @foreach ($cancelReasons as $creason)
                                <option value="{{ strtolower($creason) }}">
                                    {{ $creason }}
                                </option>
                            @endforeach
                        </select>
                        @error('reason')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    @if ($reason === 'other')
                        <div>
                            <label class="block text-gray-700 mb-1">Please specify</label>
                            <input type="text" wire:model.defer="other_reason"
                                class="bg-green-50 border border-primary-200 rounded w-full p-4 text-lg" />
                            @error('other_reason')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif
                    <div>
                        <label class="block text-gray-700 mb-1">Account Number</label>
                        <input type="text" wire:model.defer="account_no"
                            class="bg-green-50 border border-primary-200 rounded w-full px-4 py-2 text-lg" required />
                        @error('account_no')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 text-sm">Bank Name</label>
                        <select wire:model="bank_name"
                            class="bg-green-50 border border-primary-200 rounded w-full p-2 text-base appearance-none"
                            required>
                            <option value="">Select Bank</option>
                            @foreach ($nigeriaBanks as $bank)
                                <option value="{{ $bank }}">{{ $bank }}</option>
                            @endforeach
                        </select>
                        @error('bank_name')
                            <span class="text-red-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 text-sm">Account Name</label>
                        <input type="text" wire:model.defer="account_name"
                            class="bg-gray-100 border border-gray-200 rounded w-full p-2 text-base" required readonly />
                        @error('account_name')
                            <span class="text-red-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit"
                        class="w-full bg-primary-700 hover:bg-primary-800 text-white text-base font-semibold py-2 rounded transition">Submit</button>
                </form>
            @endif
        </div>
    </div>
</div>
