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
                        <p class="flex items-center justify-between">
                            <span class="text-sm">Checkin</span>
                            <span class="text-sm font-bold">{{ getDateR(Session::get('checkin')) }}</span>
                        </p>
                        <p class="flex items-center justify-between">
                            <span class="text-sm">Checkout</span>
                            <span class="text-sm font-bold">{{ getDateR(Session::get('checkout')) }}</span>
                        </p>
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
                            <span>Total Price</span>
                            <span
                                class="text-lg font-bold">{{ formatAsNaira(calculateRemainingAmount($category->price1 * $days * count($checked), $hotel->online_price)) }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="w-full space-y-8 overflow-hidden rounded-sm lg:flex-1">
                <div class="gap-4 p-4 md:p-0 space-y-4 md:grid-cols-2 lg:grid-cols-4">

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($category->rooms as $room)
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($paymentMethods as $method)
                                    <label
                                        class="flex items-center p-3 border rounded cursor-pointer transition-all hover:bg-gray-50 {{ $selectedPaymentMethod == $method->code ? 'border-primary bg-primary-50' : 'border-gray-200' }}">
                                        <input type="radio" wire:model.live="selectedPaymentMethod"
                                            value="{{ $method->code }}" class="mr-2 text-primary">
                                        <div>
                                            <div class="font-medium">{{ $method->name }}</div>
                                            <div class="text-sm text-gray-600">{{ $method->description }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div
                        class="flex items-center flex-wrap md:items-start flex-col md:flex-row justify-center gap-8 pt-8 w-full mx-auto">
                        <div class="flex flex-col justify-center items-center w-full md:max-w-max">
                            <button x-data="{ disabled: false }" x-bind:disabled="disabled" x-on:click="disabled = true"
                                x-bind:class="disabled ? 'cursor-not-allowed opacity-50' : ''"
                                data-tooltip-placement="top" data-tooltip-target="guarantee"
                                wire:click="addBooking('PayNow')" type="button"
                                class="px-6 py-2 mx-auto font-medium text-white border-2 rounded-sm bg-primary border-primary hover:text-black md:max-w-max w-full">
                                Reserve & Pay Now ( {{ formatAsNaira($discountedPrice * $days * count($checked)) }})
                            </button>
                            <span>Refundable</span>
                        </div>
                        @if ($hotel->non_refundable_price > 0 && !$hotel->non_refundable_price == null)
                            <div class="flex flex-col justify-center items-center w-full md:max-w-max">
                                <button x-data="{ disabled: false }" x-bind:disabled="disabled"
                                    x-on:click="disabled = true"
                                    x-bind:class="disabled ? 'cursor-not-allowed opacity-50' : ''"
                                    wire:click="addBooking('NonRefundable')" data-tooltip-placement="top"
                                    data-tooltip-target="guarantee"
                                    class="relative block font-semibold p-3 px-6 md:max-w-max w-full text-primary rounded-sm bg-primary-100  border-primary animate ">
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
                                Pay on arrival ({{ formatAsNaira($discountedPrice * $days * count($checked)) }})
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
