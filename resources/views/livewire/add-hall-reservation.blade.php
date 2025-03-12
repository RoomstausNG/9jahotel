<div class="pb-32 space-y-6">
    <div class="p-4 space-y-4 bg-white rounded-lg w-ful" x-data="{ step: 1 }">
        <div class="flex items-center justify-between space-x-4 text-2xl ">
            <h1 class="font-medium">
                Hall Reservation
            </h1>
            <a href="{{ route('reservations') }}" class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a>
        </div>
        <div class="">
            <div class="flex flex-wrap gap-4">
                <p class="uppercase">Hall name: <span class="font-bold">{{ $hall->name }}</span></p>
                {{-- <p class="uppercase">Price: <span
                        class="px-2 py-1 font-medium text-white bg-green-500 rounded">{{ formatAsNaira($hall->price) }}</span>
                </p> --}}
                @if ($hall->isPromoPrice())
                    <p class="uppercase">Promo Price:
                        <span class="px-2 py-1 font-medium text-white bg-green-500 rounded">
                            {{ formatAsNaira($hall->getPrice()) }}</span>
                    </p>
                @else
                    <p class="uppercase">Price:
                        <span class="px-2 py-1 font-medium text-white bg-green-500 rounded">
                            {{ formatAsNaira($hall->price) }}</span>
                    </p>
                @endif
            </div>
        </div>
    </div>
    @if (!$bookingForm)
        <form wire:submit="bookHall"
            class="flex flex-wrap w-full gap-8 p-4 space-y-4 bg-gray-100 border rounded-lg shadow-lg">
            <h1 class="w-full text-2xl font-semibold">Add Booking</h1>
            <div class="w-full space-y-6 px-4 rounded-lg lg:w-[45%] xl:w-[30%] sm:gap-6 lg:gap-8">
                <x-input.text wire:model.live.blur='checked_in' label="Check-In Date" name="checked_in"
                    type="date" />
                <x-input.text wire:model.live.blur='checked_out' label="Check-Out Date" name="checked_out"
                    type="date" />
                <x-input.text label="Discount (%)" wire:model.live='discount' type="number" name="discount" />
                @if ($checked_out)
                    @php
                        $hall = \App\Models\Hall::find($bookable_id);
                        $price = $hall->getPrice();
                        $days = calculateDateDifferenceInDays($checked_in, $checked_out);

                        $discountPrice = ($price * $days * intval($discount)) / 100;

                        if (auth()->user()->hotel->vat) {
                            $due = ($price * $days - $discountPrice) * 1.075;
                        } else {
                            $due = $price * $days - $discountPrice;
                        }
                        $finalPrice = $discount > 0 ? $price * $days - $discountPrice : $price * $days;
                    @endphp
                    <div class="w-full p-4 bg-white border rounded-lg shadow">
                        <p>Hall Rate:
                            <span>{{ formatAsNaira($price) }}</span>
                        </p>
                        <p>Day(s): {{ $days }}</p>
                        <p>Total Amount: {{ formatAsNaira($price * $days) }}</p>
                        @if (auth()->user()->hotel->vat)
                            <p>Due:
                                {{ formatAsNaira($finalPrice) }}
                            </p>
                            <p>VAT: {{ formatAsNaira($due - $finalPrice) }}</p>
                            <p>Total Due {{ formatAsNaira($due) }}</p>
                        @else
                            <p>Due:
                                {{ formatAsNaira($finalPrice) }}
                            </p>
                        @endif
                    </div>
                @endif


                <x-input.text label="Amount Paid" name="amount_paid" />
            </div>
            <div class="w-full lg:w-[45%] xl:w-[65%] border-l border-gray-500 pl-8">
                <div class="w-full gap-4 p-4 ">
                    <div class="justify-start w-full p-4 space-y-2 border rounded-lg bg-gray-50">
                        <p class="">
                            <span class="text-lg font-medium text-gray-600">
                                Find customer</span>

                        </p>
                        <div class="relative overflow-hidden border rounded-md max-w-[300px] shadow-sm">
                            <input type="text" wire:model='phone'
                                class="z-10 w-full border-0 rounded-md focus:border-black focus:border focus:bg-white focus-within:ring-0 placeholder:text-gray-400"
                                placeholder="e.g 070813532229">
                            <button wire:click="findCustomer" type="button"
                                class="absolute top-0 right-0 z-20 h-full px-4 py-1 text-gray-600 bg-gray-200 hover:text-black test-sm hover:font-medium">Load</button>

                        </div>
                        @error('phone')
                            <span class="w-full text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div
                        class="grid grid-cols-1 gap-4 my-3 overflow-y-auto text-sm md:gap-6 md:grid-cols-2 2xl:grid-cols-3">
                        <x-input.text label="First Name" name="firstname" />
                        <x-input.text label="Last Name" name="lastname" />
                        <x-input.text label="Telephone" name="phone" />
                        <x-input.text label="Email Address" name="email" />
                        <x-form.select name="gender" label="Customer's Gender">
                            <option>Select Gender</option>
                            @foreach (['Male', 'Female'] as $sex)
                                <option {{ $sex == $gender ? 'selected' : '' }} value="{{ $sex }}">
                                    {{ $sex }}
                                </option>
                            @endforeach
                        </x-form.select>
                        <x-form.select name="state_id" label="State">
                            <option>Select State</option>
                            @foreach (\Nnjeim\World\Models\State::where('country_id', 160)->get() as $state)
                                <option value="{{ $state->id }}" {{ $state_id == $state->id ? 'selected' : '' }}>
                                    {{ $state->name }}
                                </option>
                            @endforeach
                        </x-form.select>
                        <x-form.select name="city_id" label="City/Town">
                            <option>Select City</option>
                            @if ($state_id)
                                @foreach (\Nnjeim\World\Models\City::select('id', 'name')->where('state_id', $state_id)->get() as $city)
                                    <option value="{{ $city->id }}" {{ $city_id == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            @else
                                @php
                                    $city = \Nnjeim\World\Models\City::find($city_id);
                                @endphp
                                <option value="{{ $city_id }}">
                                    {{ $city?->name }}
                                </option>
                            @endif
                        </x-form.select>

                        <x-input.text label="Address" name="address" />
                        <x-form.select name="business_id" label="If Credit Customer, select Company">
                            <option>Select Company</option>
                            @foreach (\App\Models\Business::select('id', 'name')->get() as $business)
                                <option {{ $business->id == $business_id ? 'selected' : '' }}
                                    value="{{ $business->id }}">
                                    {{ $business->name }}
                                </option>
                            @endforeach
                        </x-form.select>
                    </div>
                </div>
            </div>
            <div class="w-full pl-4">
                <button class="px-4 py-2 text-white bg-green-500 rounded-md">Book Reservation</button>
            </div>
        </form>
    @else
        <form wire:submit="updateBooking"
            class="flex flex-wrap w-full gap-8 p-4 space-y-4 bg-gray-100 border rounded-lg shadow-lg lg:space-y-8">
            <div class="w-full space-y-6 p-4 rounded-lg lg:w-[40%] xl:w-[35%] sm:gap-6 lg:gap-8">
                <x-form.select class="uppercase" name="bookable_id" label="Changed Hall">
                    {{-- <option value="{{ $hall->id }}" {{ $bookable_id == $hall->id ? 'selected' : '' }}
                        class="capitalize">{{ strtoupper($hall->number . ' ' . $hall->type->name) }}</option> --}}
                    @foreach (\App\Models\Hall::select('id', 'name', 'price', 'status')->get() as $hall)
                        <option class="text-sm uppercase" value="{{ $hall->id }}">
                            <span> {{ strtoupper($hall->name) }}
                                {{ formatAsNaira($hall->price) . ' (' . $hall->status . ')' }}</span>
                        </option>
                    @endforeach
                </x-form.select>
                <x-input.text wire:model.live.blur='checked_in' label="Check-In Date" name="checked_in"
                    type="date" />
                <x-input.text wire:model.live.blur='checked_out' label="Check-Out Date" name="checked_out"
                    type="date" />
                <x-input.text label="Discount (%)" wire:model.live='discount' type="number" name="discount" />
                @if ($checked_out)
                    @php
                        $hall = \App\Models\Hall::find($bookable_id);
                        $price = $hall->getPrice();
                        $days = calculateDateDifferenceInDays($checked_in, $checked_out);

                        $discountPrice = ($price * $days * intval($discount)) / 100;

                        if (auth()->user()->hotel->vat) {
                            $due = ($price * $days - $discountPrice) * 1.075;
                        } else {
                            $due = $price * $days - $discountPrice;
                        }
                        $finalPrice = $discount > 0 ? $price * $days - $discountPrice : $price * $days;
                    @endphp
                    <div class="w-full p-4 bg-white border rounded-lg shadow">
                        <p>Hall Rate:
                            <span>{{ formatAsNaira($price) }}</span>
                        </p>
                        <p>Day(s): {{ $days }}</p>
                        <p>Total Amount: {{ formatAsNaira($price * $days) }}</p>
                        @if (auth()->user()->hotel->vat)
                            <p>Due:
                                {{ formatAsNaira($finalPrice) }}
                            </p>
                            <p>VAT: {{ formatAsNaira($due - $finalPrice) }}</p>
                            <p>Total Due {{ formatAsNaira($due) }}</p>
                        @else
                            <p>Due:
                                {{ formatAsNaira($finalPrice) }}
                            </p>
                        @endif
                    </div>
                @endif


                <x-input.text label="Total Due" name="due" />
                <x-input.text label="Amount Paid" name="amount_paid" />
            </div>
            <div class="w-full lg:w-[50%] xl:w-[60%] border-l border-gray-500 pl-8">
                <div class="w-full gap-4 p-4 ">
                    <div class="justify-start w-full p-4 space-y-2 border rounded-lg bg-gray-50">
                        <p class="">
                            <span class="text-lg font-medium text-gray-600">
                                Find customer</span>
                        </p>
                        <div class="relative overflow-hidden border rounded-md max-w-[300px] shadow-sm">
                            <input type="text" wire:model='phone'
                                class="z-10 w-full border-0 rounded-md focus:border-black focus:border focus:bg-white focus-within:ring-0 placeholder:text-gray-400"
                                placeholder="e.g 070813532229">
                            <button wire:click="findCustomer" type="button"
                                class="absolute top-0 right-0 z-20 h-full px-4 py-1 text-gray-600 bg-gray-200 hover:text-black test-sm hover:font-medium">Load</button>

                        </div>
                        @error('phone')
                            <span class="w-full text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div
                        class="grid grid-cols-1 gap-4 my-3 overflow-y-auto text-sm md:gap-6 md:grid-cols-2 2xl:grid-cols-3">
                        <x-input.text label="First Name" name="firstname" />
                        <x-input.text label="Last Name" name="lastname" />
                        <x-input.text label="Telephone" name="phone" />
                        <x-input.text label="Email Address" name="email" />
                        <x-form.select wire:model='gender' name="gender" label="Customer's Gender">
                            <option>Select Gender</option>
                            @foreach (['Male', 'Female'] as $sex)
                                <option value="{{ $sex }}">
                                    {{ $sex }}
                                </option>
                            @endforeach
                        </x-form.select>
                        <x-form.select name="state_id" label="State">
                            <option>Select State</option>
                            @foreach (\Nnjeim\World\Models\State::select('id', 'name')->where('country_id', $country_id)->get() as $state)
                                <option value="{{ $state->id }}">
                                    {{ $state->name }}
                                </option>
                            @endforeach
                        </x-form.select>
                        <x-form.select name="city_id" label="City/Town">
                            <option>Select City</option>
                            @if ($state_id)
                                @foreach (\Nnjeim\World\Models\City::select('id', 'name')->where('state_id', $state_id)->get() as $city)
                                    <option value="{{ $city->id }}"
                                        {{ $city_id == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            @else
                                @php
                                    $city = \Nnjeim\World\Models\City::find($city_id);
                                @endphp
                                <option value="{{ $city_id }}">
                                    {{ $city?->name }}
                                </option>
                            @endif
                        </x-form.select>

                        <x-input.text label="Address" name="address" />
                        <x-form.select name="business_id" label="If Credit Customer, select Company">
                            <option>Select Company</option>
                            @foreach (\App\Models\Business::select('id', 'name')->get() as $business)
                                <option {{ $business->id == $business_id ? 'selected' : '' }}
                                    value="{{ $business->id }}">
                                    {{ $business->name }}
                                </option>
                            @endforeach
                        </x-form.select>
                    </div>
                </div>
            </div>
            <div class="flex items-center w-full pl-4 space-x-3">
                @if ($status == 'dirty')
                    <button wire:click='cleanHall' type="button"
                        class="px-4 py-2 text-white bg-green-500 rounded-md">Change to Available</button>
                @else
                    @if ($status == 'paid' || $status == 'unpaid')
                        <button wire:click='checkedIn' type="button"
                            class="px-4 py-2 text-white bg-green-500 rounded-md">Confirm Reservation</button>
                        <button class="px-4 py-2 text-white rounded-md bg-primary">Update</button>
                    @elseif($status == 'occupied')
                        <button wire:click='checkedOut' type="button"
                            class="px-4 py-2 text-white bg-gray-500 rounded-md">Check Out</button>
                    @endif

                @endif
            </div>
        </form>
    @endif
    @if ($hall->bookings()->exists())
        <div class="w-full p-4 space-y-6 overflow-y-scroll bg-white rounded-lg bb-20" x-data="{ active: 'hotel' }">
            <div class="flex items-center justify-between space-x-4 text-2xl ">
                <h1 class="font-medium">
                    Booking Histories
                    <span class="p-2 ml-4 text-lg text-blue-500 rounded shadow bg-gray-50">
                        {{ strtoupper($hall->name) . ' ' . formatAsNaira($hall->price) }}
                    </span>
                </h1>
                <a href="{{ route('reservations') }}"
                    class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a>
            </div>

            <div class="w-full pb-8 overflow-y-auto">
                <div
                    class="mb-4 overflow-x-auto overflow-y-scroll bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <table id="" class="w-full overflow-x-auto overflow-y-auto rounded bord">
                        <thead class="w-full overflow-x-auto rounded">
                            <tr class="w-full font-medium bg-gray-100 rounded">
                                {{-- <th class="p-4 font-medium text-left">
                                <input type="checkbox" value="" wire:model="all" class="select-input">
                            </th> --}}
                                <th class="p-4 font-medium text-left">No</th>
                                <th class="p-4 font-medium text-left nowrap">Checked-In Date</th>
                                <th class="p-4 font-medium text-left nowrap">Checked-Out Date</th>
                                <th class="p-4 font-medium text-left nowrap">Hall Price</th>
                                <th class="p-4 font-medium text-left">Discount(%)</th>
                                <th class="p-4 font-medium text-left nowrap">Total Due</th>
                                <th class="p-4 font-medium text-left nowrap">Total Amount paid</th>
                                <th class="p-4 font-medium text-left">Customer</th>
                                <th class="p-4 font-medium">Status</th>
                                <th class="p-4 font-medium text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reservations as $reservation)
                                <tr wire:key='{{ $reservation->id }}' class="border-b even:bg-gray-50">
                                    {{-- <td class="px-4 py-2">

                                </td> --}}
                                    <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-2 text-sm capitalize break-keep nowrap">
                                        {{ $reservation->checked_in->format('d M, Y') }}
                                    </td>

                                    <td class="px-4 py-2 text-sm capitalize break-keep nowrap">
                                        {{ $reservation->checked_out->format('d M, Y') }}
                                    </td>

                                    <td class="px-4 py-2 ">
                                        {{ formatAsNaira($reservation->bookable->price) }}
                                    </td>

                                    <td class="px-4 py-2 text-sm">
                                        {{ $reservation->discount ?? 0 }}
                                    </td>

                                    <td class="px-4 py-2 text-sm">
                                        {{ formatAsNaira($reservation->due) }}
                                    </td>

                                    <td class="px-4 py-2 text-sm">
                                        {{ formatAsNaira($reservation->amount_paid) }}</td>



                                    <td class="px-4 py-2 text-sm">
                                        <span href="" class="nowrap">
                                            <span>{{ $reservation->customer->firstname . ' ' . $reservation->customer->lastname }}</span>
                                            <br>
                                            <span>{{ $reservation->customer->phone }}</span> <br>
                                            <span>{{ $reservation->customer->email }}</span>
                                        </span>
                                    </td>

                                    <td class="px-4 py-2 text-sm text-center">
                                        <span
                                            class="{{ 'bg-' . $reservation->status }}  text-white uppercase px-3 py-1 rounded-full">{{ $reservation->status }}</span>
                                    </td>

                                    <td class="px-4 py-2">
                                        <p class="flex items-center justify-start space-x-2 text-sm">
                                            <button wire:click="editBooking({{ $reservation->id }})"
                                                data-tooltip-target="edit" data-tooltip-placement="left"
                                                class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-600 border border-blue-600 rounded-md">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </button>
                                            <button wire:click="deleteBooking({{ $reservation->id }})"
                                                wire:confirm="Booking will be deleted including finance records. Are you sure you want to perform this action?"
                                                data-tooltip-target="delete" data-tooltip-placement="left"
                                                class="flex items-center justify-center px-2 py-1 space-x-2 text-red-600 border border-red-600 rounded-md">
                                                Delete
                                            </button>
                                        </p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="p-4 text-center" colspan="8">No data...</td>
                                </tr>
                            @endforelse
                            {{-- edit tooltip --}}
                            <x-tooltip id="edit" content="Edit business data" />

                            {{-- edit tooltip --}}
                            <x-tooltip id="delete" content="Change Status" />
                        </tbody>
                    </table>
                    <div class="px-4">
                        <p class="my-2">
                            {{ $reservations?->links() }}
                        </p>
                    </div>
                </div>
            </div>


        </div>
    @endif
</div>

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('livewire:initialized', () => {
            let el = $('#customer');
            initSelect();

            Livewire.hook('message.processed', (message, component) => {
                initSelect();
            })

            Livewire.on('setCustomerSelect', values => {
                el.val($values).trigger('change.select2');
            })

            el.on('change', function(e) {
                @this.set('customer_id', el.select2("val"))
            })

            function initSelect() {
                el.select2({
                    placeholder: '{{ __('Select your option') }}',
                    allowClear: !el.attr('required'),
                })
            }
        });
    </script>
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
@endpush
