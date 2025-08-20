<div class="relative w-full p-0 mx-auto" x-data="{ review: false, modal: @entangle('modal').live }">

    @php
        $days = calculateDateDifferenceInDays(Session::get('checkin'), Session::get('checkout'));
        $discountedPrice = $hall->price - ($hall->price * ($hotel->online_price ?? 0)) / 100;
        $nonRefundablePrice = $hall->price - ($hall->price * ($hotel->non_refundable_price ?? 0)) / 100;
    @endphp

    <div class="grid w-full max-w-screen-xl px-4 py-4 mx-auto space-y-8 rounded-lg">
        <div class="py-5 max-w-7xl">
            @livewire('search-hall')
        </div>
        <h1 class="text-xl font-bold">{{ $hotel->name }}</h1>
        <h1 class="text-xl font-medium">Select Hall</h1>
        <div class="flex flex-col justify-between gap-6 lg:flex-row-reverse ">
            <div class="w-full p-2 border rounded-sm lg:w-1/3 xl:w-1/4 max-h-min">
                <div class="p-2  space-y-4 bg-gray-100 border full">
                    <img src="{{ getCloudinaryUrl($hotel->background_image) }}"
                        class="block object-cover object-center w-full h-auto min-h-[150px] rounded-sm"
                        alt="{{ $hotel->name }}">

                    <div class="space-y-6">
                        <div>
                            <p class="text-xl font-medium capitalize">{{ $hotel->name }}</p>
                            <p class="font-medium uppercase ">{{ formatAsNaira($hall->price) }}</p>
                        </div>
                        <p class="capitalize" style="line-height: 16px">
                            {{ $hotel->address . ', ' . $hotel->city->name }}</p>
                    </div>
                    <div class="p-3 space-y-3 bg-white border rounded">
                        <p class="flex items-center justify-between">
                            <span class="text-sm">Hall Name</span>
                            <span class="text-sm font-bold uppercase">{{ $hall->name }}</span>
                        </p>
                        <<<<<<< HEAD <div class="flex items-center justify-between" x-data="{ editingCheckin: false }">
                            <span class="text-sm">Checkin</span>
                            <div class="flex items-center">
                                <span x-show="!editingCheckin" @click="editingCheckin = true"
                                    class="text-sm font-bold cursor-pointer hover:text-primary hover:underline">
                                    {{ getDateR(Session::get('checkin')) }}
                                </span>
                                <input x-show="editingCheckin" type="date" wire:model.live="checkinDate"
                                    @blur="editingCheckin = false" @keydown.enter="editingCheckin = false"
                                    class="text-sm font-bold border-none p-0 focus:ring-0 w-auto min-w-0">
                            </div>
                    </div>
                    <div class="flex items-center justify-between" x-data="{ editingCheckout: false }">
                        <span class="text-sm">Checkout</span>
                        <div class="flex items-center">
                            <span x-show="!editingCheckout" @click="editingCheckout = true"
                                class="text-sm font-bold cursor-pointer hover:text-primary hover:underline">
                                {{ getDateR(Session::get('checkout')) }}
                            </span>
                            <input x-show="editingCheckout" type="date" wire:model.live="checkoutDate"
                                @blur="editingCheckout = false" @keydown.enter="editingCheckout = false"
                                class="text-sm font-bold border-none p-0 focus:ring-0 w-auto min-w-0">
                            =======
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Checkin</span>
                                <div class="editable-date-container" data-date-type="checkin">
                                    <span
                                        class="editable-date cursor-pointer text-sm font-bold border border-gray-300 rounded px-2 py-1 hover:border-blue-500 transition-colors"
                                        id="checkin-display">{{ getDateR(Session::get('checkin')) }}</span>
                                    <input type="date" class="hidden date-input" id="checkin-input"
                                        value="{{ Session::get('checkin') }}" min="{{ date('Y-m-d') }}">
                                    <svg class="inline-block w-4 h-4 ml-1 text-gray-400 cursor-pointer"
                                        fill="currentColor" viewBox="0 0 20 20">
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
                                    <svg class="inline-block w-4 h-4 ml-1 text-gray-400 cursor-pointer"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    >>>>>>> 827d24c3 (date edit)
                                </div>
                            </div>
                        </div>

                        <p class="flex justify-end px-4 py-[2px] max-w-max border bg-white font-medium">
                            {{ $days }} night
                        </p>
                        <div class="p-0">
                            <p class="flex items-center justify-between text-sm">
                                <span>Walk-in Price</span>
                                <span
                                    class="text-lg font-medium text-gray-500 line-through ">{{ formatAsNaira($hall->price * $days) }}</span>
                            </p>
                            <p class="flex items-center justify-between text-sm">
                                <span>Discounted Price</span>
                                <span class="text-lg font-bold">{{ formatAsNaira($discountedPrice * $days) }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="w-full space-y-8 overflow-hidden rounded-sm lg:flex-1">
                    <div class="gap-4 p-4 md:p-0 space-y-4 md:grid-cols-2 lg:grid-cols-4">
                        <div class="w-full p-4 bg-white border rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-2">Hall Details</h2>
                            <p><span class="font-medium">Name:</span> {{ $hall->name }}</p>
                            <p><span class="font-medium">Capacity:</span> {{ $hall->capacity }}</p>
                            <p><span class="font-medium">Description:</span> {!! $hall->description !!}</p>
                            <p><span class="font-medium">Status:</span> {{ ucfirst($hall->status) }}</p>
                        </div>
                    </div>
                    <div class="w-full space-y-0">
                        <h1 class="w-full px-4 py-3 font-medium bg-gray-100 border-b md:text-xl tex-lg">Your Details
                        </h1>
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
                                <div class="flex justify-between gap-8 flex-col md:flex-row">
                                    @foreach ($paymentMethods as $method)
                                        <label
                                            class="flex w-full items-center p-3 border rounded cursor-pointer transition-all hover:bg-gray-50 {{ $selectedPaymentMethod == $method->code ? 'border-primary bg-primary-50' : 'border-gray-200' }}">
                                            <input type="radio" wire:model.live="selectedPaymentMethod"
                                                value="{{ $method->code }}" class="mr-2 text-primary">
                                            <div class="flex items-center">
                                                @if (in_array($method->code, ['paystack', 'opay']))
                                                    <img src="{{ asset('img/' . $method->code . '.png') }}"
                                                        alt="{{ $method->name }}" class="h-8 mr-2">
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="flex flex-col md:flex-row flex-wrap gap-8 mx-auto justify-center pt-8 w-full">
                            <div class="flex w-full md:max-w-max flex-col justify-center items-center">
                                <button x-data="{ disabled: false }" x-bind:disabled="disabled"
                                    x-on:click="disabled = true"
                                    x-bind:class="disabled ? 'cursor-not-allowed opacity-50' : ''"
                                    data-tooltip-placement="top" data-tooltip-target="guarantee"
                                    wire:click="addBooking('PayNow')" type="button"
                                    class="px-6 py-2 mx-auto font-medium text-white border-2 rounded-sm bg-primary border-primary hover:text-black w-full flex items-center justify-center">
                                    Reserve & Pay Now ( {{ formatAsNaira($discountedPrice * $days) }})
                                </button>
                                <span>Refundable</span>
                            </div>

                            @if ($hotel->non_refundable_price > 0 && !$hotel->non_refundable_price == null)
                                <div class="flex w-full md:max-w-max flex-col justify-center items-center">
                                    <button x-data="{ disabled: false }" x-bind:disabled="disabled"
                                        x-on:click="disabled = true"
                                        x-bind:class="disabled ? 'cursor-not-allowed opacity-50' : ''"
                                        wire:click="addBooking('NonRefundable')" data-tooltip-placement="top"
                                        data-tooltip-target="guarantee"
                                        class="relative flex items-center justify-center font-semibold w-full p-3 px-6 text-primary rounded-sm bg-primary-100 border-primary animate">
                                        Instant Pay
                                        {{ formatAsNaira($nonRefundablePrice * $days) }}
                                    </button>
                                    <span>(NonRefundable)</span>
                                </div>
                            @endif
                            <div class=" w-full md:max-w-max">
                                <button x-data="{ disabled: false }" x-bind:disabled="disabled"
                                    x-on:click="disabled = true"
                                    x-bind:class="disabled ? 'cursor-not-allowed opacity-50' : ''"
                                    data-tooltip-placement="top" data-tooltip-target="not-guarantee"
                                    wire:click="addBooking('PayAtHotel')" type="button"
                                    class="px-6 py-2 mx-auto font-medium rounded-sm text-primary border-2 border-primary bg-white hover:text-black w-full">
                                    Pay on arrival ({{ formatAsNaira($discountedPrice * $days) }})
                                </button>
                            </div>
                        </div>

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
                    }, 10000);
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
