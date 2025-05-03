<div class="relative w-full p-0 mx-auto" x-data="{ review: false, modal: @entangle('modal').live }">
    <div class="grid w-full max-w-screen-xl px-4 py-4 mx-auto space-y-8 rounded-lg">
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
                        <p class="flex items-center justify-between">
                            <span class="text-sm">Checkin</span>
                            <span class="text-sm font-bold">{{ getDateR(Session::get('checkin')) }}</span>
                        </p>
                        <p class="flex items-center justify-between">
                            <span class="text-sm">Checkout</span>
                            <span class="text-sm font-bold">{{ getDateR(Session::get('checkout')) }}</span>
                        </p>
                    </div>
                    @php
                        $days = calculateDateDifferenceInDays(Session::get('checkin'), Session::get('checkout'));
                        $discountedPrice = $hall->price - ($hall->price * ($hotel->online_price ?? 0) / 100);
                    @endphp
                    <p class="flex justify-end px-4 py-[2px] max-w-max border bg-white font-medium">
                        {{ $days }} night
                    </p>
                    <div class="p-0">
                        <p class="flex items-center justify-between text-sm">
                            <span>Walk-in Price</span>
                            <span class="text-lg font-medium text-gray-500 line-through ">{{ formatAsNaira($hall->price * $days) }}</span>
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
                        <p><span class="font-medium">Description:</span> {{ $hall->description }}</p>
                        <p><span class="font-medium">Status:</span> {{ ucfirst($hall->status) }}</p>
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
                            <div class="flex items-center justify-center gap-8 py-3 mx-auto max-w-max">
                                <button data-tooltip-placement="top" data-tooltip-target="guarantee"
                                    wire:click="addBooking('online')" type="button"
                                    class="px-6 py-2 mx-auto font-medium text-white border-2 rounded-full bg-primary border-primary hover:text-black max-w-max">Reserve
                                    &
                                    Pay Now</button>
                                <button data-tooltip-placement="top" data-tooltip-target="not-guarantee"
                                    wire:click="addBooking('payAtTheHotel')" type="button"
                                    class="px-6 py-2 mx-auto font-medium bg-white border-2 rounded-full text-primary border-primary hover:bg-secondary hover:text-black max-w-max">Reserve
                                    & Pay at Hotel
                                </button>
                            </div>
                        </div>
                    @endif
                    <div class="flex items-center justify-center -pt-8 p-0">
                        <button wire:click="addBooking('non-refundable')"
                            class="relative block font-bold w-full p-3 px-6 max-w-max text-white bg-red-600 border rounded-md hover:bg-red-700 animate ">Save
                            More! Pay NON REFUNDABLE
                            <span class="text-xl font-bold">
                                {{ formatAsNaira($discountedPrice * $days) }}</span>
                            Now!
                        </button>
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
