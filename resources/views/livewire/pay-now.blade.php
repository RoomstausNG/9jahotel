<div class="min-h-screen bg-primary-700 flex flex-col items-center py-10">
    <div class="w-full max-w-3xl">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">Pay for Booking</h1>
            <p class="text-xl text-primary-100">Review your booking and proceed to payment</p>
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
                    <span class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded text-base">
                        {{ $invoice->bookings->first()->bookable->type?->name . ' Room' ?? $invoice->bookings->first()->bookable->name }}
                    </span>
                </div>
                <div class="mt-4 md:mt-0 md:ml-6">
                    <img src="{{ getCloudinaryUrl($invoice->hotel->background_image) }}"
                        alt="{{ $invoice->hotel->name }}" class="w-40 h-24 object-cover shadow rounded">
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
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-4">
                <div class="mb-2 md:mb-0">
                    <span class="block text-gray-500 text-sm">Customer</span>
                    <span class="font-bold text-lg">{{ $invoice->customer->firstname }}
                        {{ $invoice->customer->lastname }}</span>
                </div>
                <div class="mb-2 md:mb-0">
                    <span class="block text-gray-500 text-sm">Email</span>
                    <span class="font-bold text-lg">{{ $invoice->customer->email }}</span>
                </div>
                <div class="mb-2 md:mb-0">
                    <span class="block text-gray-500 text-sm">Phone</span>
                    <span class="font-bold text-lg">{{ $invoice->customer->phone ?? '-' }}</span>
                </div>
            </div>
            <div class="flex justify-end">
                <div class="text-right">
                    <span class="block text-gray-500 text-sm">Total Amount</span>
                    <span
                        class="text-primary-700 text-3xl font-bold">₦{{ number_format($invoice->amount ?? 0, 2) }}</span>
                </div>
            </div>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mt-4">
                <div class="mb-2 md:mb-0">
                    <span class="block text-gray-500 text-sm">Booking Reference</span>
                    <span class="font-mono text-lg">{{ $invoice->reference }}</span>
                </div>
                <div class="mb-2 md:mb-0">
                    <span class="block text-gray-500 text-sm">Status</span>
                    <span class="font-mono text-lg">{{ ucfirst($invoice->status) }}</span>
                </div>
            </div>
        </div>

        @if (session('error'))
            <div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-center font-medium">{{ session('error') }}</div>
        @endif
        @if ($invoice->status === 'unpaid')
            <div class="bg-white shadow-lg px-8 py-8 mb-6">
                <h3 class="text-xl font-semibold mb-4">Select Payment Method</h3>

                @if (count($paymentMethods) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        @foreach ($paymentMethods as $method)
                            <label
                                class="flex items-center p-4 border rounded-lg cursor-pointer transition-all hover:bg-gray-50 {{ $selectedPaymentMethod == $method->code ? 'border-primary-500 bg-primary-50' : 'border-gray-200' }}">
                                <input type="radio" wire:model.live="selectedPaymentMethod"
                                    value="{{ $method->code }}" class="mr-2 text-primary-600">
                                <div class="flex items-center">
                                    @if (in_array($method->code, ['paystack', 'opay']))
                                        <img src="{{ asset('img/' . $method->code . '.png') }}" alt="{{ $method->name }}"
                                            class="h-8 mr-2">
                                    @endif
                                    <div>
                                        <div class="font-medium">{{ $method->name }}</div>
                                        <div class="text-sm text-gray-600">{{ $method->description }}</div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="text-red-500 mb-4">No payment methods available</div>
                @endif

                <div class="flex justify-center">
                    <button wire:click="payNow" x-data="{ loading: false }" x-bind:disabled="loading"
                        x-on:click="loading = true"
                        :class="loading ? 'bg-gray-200 text-gray-400 cursor-not-allowed opacity-60' :
                            'text-green-600 bg-white hover:bg-green-50'"
                        class="text-green-600 bg-white border border-green-600 px-8 py-3 rounded-lg font-bold text-lg shadow transition flex items-center">
                        @if (in_array($selectedPaymentMethod, ['paystack', 'opay']))
                            <img src="{{ asset('img/' . $selectedPaymentMethod . '.png') }}"
                                alt="Pay with {{ $selectedPaymentMethod }}" class="h-8 mr-2">
                        @endif
                        <span x-show="!loading">Pay Now (₦{{ number_format($invoice->amount) }})</span>
                        <span x-show="loading">Processing...</span>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
