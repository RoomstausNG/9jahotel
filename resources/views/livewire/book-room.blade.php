<div class="relative w-full p-0 mx-auto" x-data="{ review: false, modal: @entangle('modal').live }">
    @php
        $days = calculateDateDifferenceInDays(Session::get('checkin'), Session::get('checkout'));
        $discountedPrice = $category->price1 - ($category->price1 * ($hotel->online_price ?? 0)) / 100;
        $nonRefundablePrice = $category->price1 - ($category->price1 * ($hotel->non_refundable_price ?? 0)) / 100;
    @endphp

    <div class="grid w-full max-w-screen-xl px-4 py-4 mx-auto space-y-8 rounded-lg">
        <div class="w-full py-6">
            {{-- search result --}}
            @livewire('search-city')
        </div>
        <h1 class="text-xl font-bold">{{ $hotel->name }}</h1>
        <h1 class="text-xl font-medium">Select Room</h1>
        <div class="flex flex-col justify-between gap-6 lg:flex-row-reverse ">

            <div class="w-full p-2 border rounded-sm lg:w-1/3 xl:w-1/4 max-h-min">
                @php
                    // $firstImage = $category->rooms()->where('image', '!=', null)->first();
                    $firstImage = $category->rooms()->whereNotNull('image')->first()?->image;
                @endphp
                <div class="p-2  space-y-4 bg-gray-100 border full">
                    <img src="{{ getCloudinaryUrl($hotel->background_image) }}"
                        class="block object-cover object-center w-full h-auto min-h-[150px] rounded-sm"
                        alt="{{ $hotel->name }}">

                    <div class="space-y-6">
                        <div>
                            <p class="text-xl font-medium capitalize">{{ $hotel->name }}</p>
                            <p class="font-medium uppercase ">{{ formatAsNaira($category->price1) }}</p>
                        </div>
                        <p class="capitalize" style="line-height: 16px">
                            {{ $hotel->address . ', ' . $hotel->city->name }}</p>
                        {{-- <p class="flex flex-wrap w-full gap-2">
                            @foreach ($hotel->amenities as $amenity)
                                <span style="white-space:nowrap"
                                    class="px-2 py-[2px] text-sm bg-white border rounded shadow-sm">{{ $amenity->name }}</span>
                            @endforeach
                        </p> --}}
                    </div>
                    <div class="p-3 space-y-3 bg-white border rounded">
                        <p class="flex items-center justify-between">
                            <span class="text-sm">Room Category</span>
                            <span class="text-sm font-bold uppercase">{{ $category->name }}</span>
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm">Checkin</span>
                            <div class="editable-date-container" data-date-type="checkin">
                                <span
                                    class="editable-date cursor-pointer text-sm font-bold border border-gray-300 rounded px-2 py-1 hover:border-blue-500 transition-colors"
                                    id="checkin-display">{{ getDateR(Session::get('checkin')) }}</span>
                                <input type="date" class="hidden date-input" id="checkin-input"
                                    value="{{ Session::get('checkin') }}" min="{{ date('Y-m-d') }}">
                                <svg class="inline-block w-4 h-4 ml-1 text-gray-400 cursor-pointer" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm">Checkout</span>
                            <div class="editable-date-container" data-date-type="checkout">
                                <span
                                    class="editable-date cursor-pointer text-sm font-bold border border-gray-300 rounded px-2 py-1 hover:border-blue-500 transition-colors"
                                    id="checkout-display">{{ getDateR(Session::get('checkout')) }}</span>
                                <input type="date" class="hidden date-input" id="checkout-input"
                                    value="{{ Session::get('checkout') }}"
                                    min="{{ Session::get('checkin') ?: date('Y-m-d') }}">
                                <svg class="inline-block w-4 h-4 ml-1 text-gray-400 cursor-pointer" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="flex items-center justify-between">
                            <span class="text-sm">No of Rooms</span>
                            <span class="text-sm font-bold">{{ count($checked) }}</span>
                        </p>

                    </div>

                    <p class="flex justify-end px-4 py-[2px] max-w-max border bg-white font-medium">
                        {{ $days }} night
                    </p>
                    {{-- <p class="text-xl font-medium">{{ formatAsNaira($category->price1) }}</p> --}}
                    <div class="p-0">
                        <p class="flex items-center justify-between text-sm">
                            <span>Walk-in Price</span>
                            <span
                                class="text-lg font-medium text-gray-500 line-through ">{{ formatAsNaira($category->price1 * $days * count($checked)) }}</span>
                        </p>
                        <p class="flex items-center justify-between text-sm">
                            <span>Online Discount ({{ $hotel->online_price ?? 0 }}%)</span>
                            <span
                                class="text-lg font-medium text-green-600">-{{ formatAsNaira(($category->price1 * $days * count($checked) * ($hotel->online_price ?? 0)) / 100) }}</span>
                        </p>
                        @if ($appliedCoupon && $couponDiscount > 0)
                            <p class="flex items-center justify-between text-sm">
                                <span>Coupon Discount ({{ $appliedCoupon->percent }}%)</span>
                                <span
                                    class="text-lg font-medium text-green-600">-{{ formatAsNaira($couponDiscount) }}</span>
                            </p>
                        @endif
                        <hr class="my-2">
                        <p class="flex items-center justify-between text-sm">
                            <span class="font-semibold">Final Price</span>
                            <span class="text-lg font-bold">
                                {{ $appliedCoupon ? formatAsNaira($this->getFinalPrice()) : formatAsNaira(calculateRemainingAmount($category->price1 * $days * count($checked), $hotel->online_price)) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="w-full space-y-8 overflow-hidden rounded-sm lg:flex-1">
                <div class="gap-4 p-4 md:p-0 space-y-4 md:grid-cols-2 lg:grid-cols-4">

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($category->rooms->take(10) as $room)
                            @if (
                                !$room->isAvailable(Session::get('checkin'), Session::get('checkout')) ||
                                    in_array($room->status, ['occupied', 'repair', 'dirty']))
                                @continue
                            @endif
                            @php
                                $total = $room->images?->count();
                            @endphp
                            <label for="{{ $room->id }}"
                                class="block overflow-hidden rounded-lg shadow cursor-pointer">
                                <div id="default-carousel" class="relative w-full" data-carousel="slide">

                                    <div class="relative h-48 overflow-hidden md:h-56">
                                        <input id="{{ $room->id }}" type="checkbox" value="{{ $room->id }}"
                                            wire:model.live="checked"
                                            class="absolute z-40 flex items-center justify-center w-8 h-8 text-center rounded-full cursor-pointer text-primary focus:bg-primary focus:border-none focus:ring-0 border-primary top-3 right-2" />
                                        <!-- Carousel wrapper -->

                                        <div class="absolute z-30 w-full bottom-2">
                                            <span
                                                class="block w-[90%] p-2 mx-auto bg-black bg-opacity-70 text-white font-semibold uppercase text-center rounded-lg">
                                                Room
                                                {{ $room->number }}</span>
                                        </div>
                                        <div class="duration-700">
                                            <img src="{{ getCloudinaryUrl($room->image ?? $room->hotel->background_image) }}"
                                                class="absolute block object-cover w-full h-full"
                                                alt="{{ $room->hotel->name . ' 9jahotel' }}">
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="w-full space-y-0">
                    <h1 class="w-full px-4 py-3 font-medium bg-gray-100 border-b md:text-xl tex-lg">Your Details</h1>
                    @if (auth()->guard('customers')->check())
                        <div class="w-full">

                        </div>
                    @else
                        <div class="w-full p-4 space-y-4">
                            {{ $this->form }}

                        </div>
                    @endif

                    @if (count($paymentMethods) > 0)
                        <div class="w-full p-4 space-y-4">
                            <h2 class="font-medium text-lg">Select Payment Method</h2>
                            <div class="flex gap-8 justify-between items-center">
                                @foreach ($paymentMethods as $method)
                                    <label
                                        class="flex w-full md:w-[45%] lg:w-[30%] items-center p-3 border rounded cursor-pointer transition-all hover:bg-gray-50 {{ $selectedPaymentMethod == $method->code ? 'border-primary bg-primary-50' : 'border-gray-200' }}">
                                        <input type="radio" wire:model.live="selectedPaymentMethod"
                                            value="{{ $method->code }}" class="mr-2 text-primary">
                                        <div class="flex items-center">
                                            @if (in_array($method->code, ['paystack', 'opay']))
                                                <img src="{{ asset('img/' . $method->code . '.png') }}"
                                                    alt="{{ $method->name }}" class="h-8 mr-2">
                                            @endif
                                            {{-- <div>
                                                <div class="font-medium">{{ $method->name }}</div>
                                                <div class="text-sm text-gray-600">{{ $method->description }}</div>
                                            </div> --}}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Coupon Code Section --}}
                    <div class="w-full p-4 space-y-4 border-t">
                        <h2 class="font-medium text-lg">Have a Coupon Code?</h2>

                        @if (!$appliedCoupon)
                            <div class="flex gap-2">
                                <div class="flex-1">
                                    <input type="text" wire:model.live="couponCode"
                                        placeholder="Enter coupon code"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        @keydown.enter="$wire.applyCoupon()">
                                </div>
                                <button type="button" wire:click="applyCoupon"
                                    class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                                    Apply
                                </button>
                            </div>
                        @else
                            <div
                                class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-md">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-green-800 font-medium">
                                        Coupon "{{ $appliedCoupon->code }}" applied!
                                        You save {{ formatAsNaira($couponDiscount) }}
                                    </span>
                                </div>
                                <button type="button" wire:click="removeCoupon"
                                    class="text-red-500 hover:text-red-700 transition-colors" title="Remove coupon">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif

                        @if ($couponError)
                            <div class="p-3 bg-red-50 border border-red-200 rounded-md">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-red-800">{{ $couponError }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div
                        class="flex items-center flex-wrap md:items-start flex-col md:flex-row justify-center gap-8 pt-8 w-full mx-auto">
                        <div class="flex flex-col justify-center items-center w-full md:max-w-max">
                            <button x-data="{ disabled: false }" x-bind:disabled="disabled" x-on:click="disabled = true"
                                x-bind:class="disabled ? 'cursor-not-allowed opacity-50' : ''"
                                data-tooltip-placement="top" data-tooltip-target="guarantee"
                                wire:click="addBooking('PayNow')" type="button"
                                class="px-6 py-2 mx-auto font-medium text-white border-2 rounded-sm bg-primary border-primary hover:text-black md:max-w-max w-full flex items-center justify-center">
                                Reserve & Pay Now (
                                {{ $appliedCoupon ? formatAsNaira($this->getFinalPrice()) : formatAsNaira($discountedPrice * $days * count($checked)) }}
                                )
                            </button>
                            <span>Refundable</span>
                        </div>
                        @if ($hotel->non_refundable_price > 0 && !$hotel->non_refundable_price == null && !$appliedCoupon)
                            <div class="flex flex-col justify-center items-center w-full md:max-w-max">
                                <button x-data="{ disabled: false }" x-bind:disabled="disabled"
                                    x-on:click="disabled = true"
                                    x-bind:class="disabled ? 'cursor-not-allowed opacity-50' : ''"
                                    wire:click="addBooking('NonRefundable')" data-tooltip-placement="top"
                                    data-tooltip-target="guarantee"
                                    class="relative font-semibold p-3 px-6 md:max-w-max w-full text-primary rounded-sm bg-primary-100 border-primary animate flex items-center justify-center">
                                    Instant Pay
                                    {{ formatAsNaira($nonRefundablePrice * $days * count($checked)) }}
                                </button>
                                <span>(NonRefundable)</span>
                            </div>
                        @endif
                        <div class="w-full md:max-w-max">
                            <button x-data="{ disabled: false }" x-bind:disabled="disabled" x-on:click="disabled = true"
                                x-bind:class="disabled ? 'cursor-not-allowed opacity-50' : ''"
                                data-tooltip-placement="top" data-tooltip-target="not-guarantee"
                                wire:click="addBooking('PayAtHotel')" type="button"
                                class="px-6 py-2 mx-auto font-medium rounded-sm text-primary border-2 border-primary bg-white hover:text-black md:max-w-max w-full">
                                Pay on arrival (
                                {{ $appliedCoupon ? formatAsNaira($this->getFinalPrice()) : formatAsNaira($discountedPrice * $days * count($checked)) }}
                                )
                            </button>
                        </div>
                    </div>

                    {{-- <div class="flex items-center justify-center -pt-8 p-0">
                        <button wire:click="addBooking('NonRefundable')"
                            class="relative block font-bold w-full p-3 px-6 max-w-max text-white bg-red-600 border rounded-md hover:bg-red-700 animate ">Save
                            More! Pay NON REFUNDABLE
                            <span class="text-xl font-bold">
                                {{ formatAsNaira(calculateRemainingAmount($category->price1 * $days * count($this->checked), $hotel->non_refundable_price)) }}</span>
                            Now!
                        </button>
                    </div> --}}

                    <x-tooltip id="guarantee" content="Booking is GUARANTEED " />
                    <x-tooltip id="not-guarantee" content="Booking is subject to Cancellation" />
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('swal:success', (event) => {
                Swal.fire(event.details)
                setTimeout(() => {
                    location.reload();
                }, 5000);
            });
        });

        // Editable dates functionality
        document.addEventListener('DOMContentLoaded', function() {
            const checkinContainer = document.querySelector('[data-date-type="checkin"]');
            const checkoutContainer = document.querySelector('[data-date-type="checkout"]');

            function initializeDateEditor(container, dateType) {
                const display = container.querySelector('.editable-date');
                const input = container.querySelector('.date-input');
                const icon = container.querySelector('svg');

                // Show input when clicking on display or icon
                [display, icon].forEach(element => {
                    element.addEventListener('click', function() {
                        display.classList.add('hidden');
                        input.classList.remove('hidden');
                        input.focus();
                    });
                });

                // Handle date changes
                input.addEventListener('change', function() {
                    updateSession(dateType, this.value);
                });

                // Hide input on blur or enter
                input.addEventListener('blur', hideInput);
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        hideInput();
                    }
                });

                function hideInput() {
                    input.classList.add('hidden');
                    display.classList.remove('hidden');
                }
            }

            function updateSession(key, value) {
                // Add loading state
                const container = document.querySelector(`[data-date-type="${key}"]`);
                container.classList.add('date-updating');

                fetch('{{ route('session.update-booking-dates') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            key: key,
                            value: value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        container.classList.remove('date-updating');

                        if (data.status === 'success') {
                            // Update displays with formatted dates
                            updateDateDisplays(data.data);
                            updateMinDates(data.data);

                            // Show success feedback
                            showFeedback('Date updated successfully!', 'success');

                            // Reload page to refresh calculations
                            setTimeout(() => {
                                window.location.reload();
                            }, 800);
                        } else {
                            showFeedback(data.message || 'Error updating date', 'error');
                            // Revert input value
                            loadDatesFromSession();
                        }
                    })
                    .catch(error => {
                        container.classList.remove('date-updating');
                        console.error('Error:', error);
                        showFeedback('Error updating date', 'error');
                        loadDatesFromSession();
                    });
            }

            function showFeedback(message, type) {
                // Remove existing feedback
                const existingFeedback = document.querySelector('.date-feedback');
                if (existingFeedback) {
                    existingFeedback.remove();
                }

                // Create feedback element
                const feedback = document.createElement('div');
                feedback.className =
                    `date-feedback fixed top-4 right-4 px-4 py-2 rounded z-50 ${type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}`;
                feedback.textContent = message;
                document.body.appendChild(feedback);

                // Remove after 3 seconds
                setTimeout(() => {
                    feedback.remove();
                }, 3000);
            }

            function updateDateDisplays(dates) {
                if (dates.checkin) {
                    const checkinDate = new Date(dates.checkin);
                    document.getElementById('checkin-display').textContent = checkinDate.toLocaleDateString(
                        'en-GB');
                    document.getElementById('checkin-input').value = dates.checkin;
                }
                if (dates.checkout) {
                    const checkoutDate = new Date(dates.checkout);
                    document.getElementById('checkout-display').textContent = checkoutDate.toLocaleDateString(
                        'en-GB');
                    document.getElementById('checkout-input').value = dates.checkout;
                }
            }

            function updateMinDates(dates) {
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('checkin-input').min = today;

                if (dates.checkin) {
                    document.getElementById('checkout-input').min = dates.checkin;
                }
            }

            function loadDatesFromSession() {
                fetch('{{ route('session.get-booking-dates') }}')
                    .then(response => response.json())
                    .then(data => {
                        updateDateDisplays(data);
                        updateMinDates(data);
                    })
                    .catch(error => console.error('Error loading session dates:', error));
            }

            // Initialize both date editors
            if (checkinContainer) initializeDateEditor(checkinContainer, 'checkin');
            if (checkoutContainer) initializeDateEditor(checkoutContainer, 'checkout');

            // Load initial dates
            loadDatesFromSession();
        });
    </script>

    <script>
        function makePayment(dat) {
            const paystack = new PaystackPop();
            paystack.newTransaction({
                    key: 'pk_domain_xxxxxxxxxx',
                    email: data.email,
                    amount: data.amount,
                    phone: data.phone,
                    firstName: data.customer.firstName,
                    lastName: data.customer.lastName,
                    currency: 'NGN'
                },
                onSuccess: (transaction) => {
                    // Payment complete! Reference: transaction.reference
                },
                onCancel: () => {
                    // user closed popup
                });
        }
    </script>
@endpush
