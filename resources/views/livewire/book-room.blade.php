<div class="relative w-full p-0 mx-auto" x-data="{ review: false, modal: @entangle('modal').live }">
    <div class="grid w-full max-w-screen-xl px-4 py-4 mx-auto space-y-8 rounded-lg">
        <h1 class="text-xl font-bold">{{ $category->hotel->name }}</h1>

        <div class="flex flex-col justify-between gap-6 lg:flex-row-reverse ">
            <div class="w-full p-2 border rounded-sm lg:w-1/3 xl:w-1/4 max-h-min">
                @php
                    // $firstImage = $category->rooms()->where('image', '!=', null)->first();
                    $firstImage = $category->rooms()->whereNotNull('image')->first()?->image;
                @endphp
                <div class="p-2 space-y-2 bg-gray-100 border full">
                    <img src="{{ getCloudinaryImage($category->hotel->background_image) }}"
                        class="block object-cover object-center w-full h-auto min-h-[150px] rounded-sm"
                        alt="{{ $category->hotel->name }}">

                    <div class="space-y-4">
                        <div>
                            <p class="text-xl font-medium capitalize">{{ $category->hotel->name }}</p>
                            <p class="font-medium uppercase ">{{ formatAsNaira($category->price1) }}</p>
                        </div>
                        <p class="capitalize" style="line-height: 16px">
                            {{ $category->hotel->address . ', ' . $category->hotel->city->name }}</p>
                        <p class="flex flex-wrap w-full gap-2">
                            @foreach ($category->hotel->amenities as $amenity)
                                <span style="white-space:nowrap"
                                    class="px-2 py-[2px] text-sm bg-white border rounded shadow-sm">{{ $amenity->name }}</span>
                            @endforeach
                        </p>
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
                            <span class="text-sm font-bold">{{ count($this->checked) }}</span>
                        </p>

                    </div>
                    @php
                        $days = calculateDateDifferenceInDays(Session::get('checkin'), Session::get('checkout'));
                    @endphp
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
                                class="text-lg font-bold">{{ formatAsNaira(calculateRemainingAmount($category->price1 * $days * count($checked), $category->hotel->online_price)) }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="w-full space-y-8 overflow-hidden border rounded-sm lg:flex-1">
                <div class="gap-4 p-4 space-y-4 md:grid-cols-2 lglg:grid-cols-4">
                    <h1 class="text-xl font-medium">Select Room</h1>
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

                                        <div class="absolute z-30 w-full bottom-3">
                                            <span
                                                class="block w-[90%] p-2 mx-auto bg-black bg-opacity-50 text-white font-semibold uppercase text-center rounded-lg">
                                                Room
                                                {{ $room->number }}</span>
                                        </div>
                                        <!-- Item 1 -->
                                        @if ($room->image)
                                            <div class="duration-700">
                                                <img src="{{ getCloudinaryImage($room->image) }}"
                                                    class="absolute block object-cover w-full h-full"
                                                    alt="{{ $room->hotel->name . ' 9jahotel' }}">
                                            </div>
                                        @else
                                            <div class="duration-700">
                                                <img src="{{ asset('img/bg-one.jpg') }}"
                                                    class="absolute block object-cover w-full h-full"
                                                    alt="{{ $room->hotel->name . ' 9jahotel' }}">
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="w-full space-y-4">
                    <h1 class="w-full px-4 py-3 font-medium bg-gray-100 border-b md:text-xl tex-lg">Your Details</h1>
                    @if (auth()->check())
                        <div class="w-full">

                        </div>
                    @else
                        <div class="w-full p-4 space-y-4">
                            {{ $this->form }}
                            <div class="flex items-center justify-center gap-8 py-3 mx-auto max-w-max">
                                <button wire:click="addBooking('online')" type="button"
                                    class="px-6 py-2 mx-auto font-medium text-white border-2 rounded-full bg-primary border-primary hover:text-black max-w-max">Reserve
                                    &
                                    Pay Now</button>
                                <button wire:click="addBooking('payAtTheHotel')" type="button"
                                    class="px-6 py-2 mx-auto font-medium bg-white border-2 rounded-full text-primary border-primary hover:bg-secondary hover:text-black max-w-max">Reserve
                                    & Pay at Hotel
                                </button>
                            </div>
                        </div>
                    @endif
                    <div class="p-4">
                        <button wire:click="addBooking('non-refundable')"
                            class="relative block w-full p-3 text-white bg-red-600 border rounded-md hover:bg-red-700 animate ">Save
                            an
                            additional {{ $category->hotel->non_refundable_price }}% on this
                            booking. Click here to pay NON-REFUNDABLE
                            <span class="text-xl font-bold">
                                {{ formatAsNaira(calculateRemainingAmount($category->price1 * $days * count($this->checked), $category->hotel->non_refundable_price)) }}</span>
                            <span
                                class="absolute w-4 h-4 rounded-full bg-primary-500 top-2 right-2 animate-ping"></span>
                        </button>
                    </div>
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
