@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
<div class="gap-6 space-y-6">
    <div class="w-full p-4 space-y-6 overflow-y-scroll bg-white rounded-lg bb-20" x-data="{ active: 'hotel' }">
        <div class="flex items-center justify-between space-x-4 text-2xl ">
            <h1 class="font-medium">
                Hall Reservation Histories for
                <span class="p-2 ml-4 text-lg text-blue-500 rounded shadow bg-gray-50">
                    {{ $hall->name . ' ' . formatAsNaira($hall->price ?? $hall->type->price1) }}
                </span>
            </h1>
            <a href="{{ route('configuration.halls') }}"
                class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a>
        </div>

        <div class="w-full space-y-4" x-data="{ bookingForm: @entangle('bookingForm').live }">
            <div class="flex items-center justify-between space-x-4 text-2xl font-medium">
                <button @click="bookingForm = true"
                    class="px-6 py-2 text-sm text-white rounded-md bg-primary">{{ $updateC ? 'Update Reservation' : 'Add Reservation' }}</button>

                <x-form.search />
            </div>
            <div class="" x-cloak x-show="bookingForm">
                <form wire:submit=@if ($updateC) 'updateBooking' @else 'addBooking' @endif
                    class="w-full space-y-4">
                    <div
                        class="grid grid-cols-1 gap-4 overflow-y-auto text-sm md:gap-6 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                        <div class="w-full min-w-max" wire:ignore>
                            <select wire:model.live='customer_id' name="customer_id" id="customer"
                                class="w-full px-2 py-3 bg-gray-100 rounded-lg min-w-max focus:bg-white">
                                <option> Select Customer</option>
                                @foreach (\App\Models\Customer::select('id', 'firstname', 'lastname')->get() as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->firstname . ' ' . $customer->lastname }}</option>
                                @endforeach
                            </select>
                        </div>

                        <x-input.text wire:model.live.blur='checked_in' label="Check-In Date" name="checked_in"
                            type="date" />
                        <x-input.text wire:model.live.blur='checked_out' label="Check-Out Date" name="checked_out"
                            type="date" />
                        @if ($checked_out)
                            @php
                                $price = \App\Models\Hall::find($bookable_id)->price;
                                $days = calculateDateDifferenceInDays($checked_in, $checked_out);
                            @endphp
                            <div class="p-2 bg-gray-100 rounded-lg shadow-sm">
                                <p>Room Rate:
                                    <span>{{ formatAsNaira($price) }}</span>
                                </p>
                                <p>Day(s): {{ $days }}</p>
                                <p>Total Amount: {{ formatAsNaira($price * $days) }}</p>
                            </div>
                        @endif
                        <x-input.text label="Discount" name="discount" />
                        <x-input.text label="Amount Paid" name="amount_paid" />
                    </div>

                    <button class="px-6 py-2 text-sm text-white rounded-md bg-primary max-w-max">Save</button>
                </form>
            </div>
        </div>
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
                        <th class="p-4 font-medium text-left nowrap">Room Price</th>
                        <th class="p-4 font-medium text-left">Discount(%)</th>
                        <th class="p-4 font-medium text-left nowrap">Total Due</th>
                        <th class="p-4 font-medium text-left">Total Amount paid</th>
                        <th class="p-4 font-medium text-left">Customer</th>
                        <th class="p-4 font-medium text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($histories as $reservation)
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

                            <td class="px-4 py-2">
                                <p class="flex items-center justify-start space-x-2 text-sm">
                                    {{-- <button wire:click="editBooking({{ $reservation->id }})" data-tooltip-target="edit"
                                        data-tooltip-placement="left"
                                        class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-600 border border-blue-600 rounded-md">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button> --}}
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
                    {{-- {{ $histories->links() }} --}}
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    {{-- <script>
$(document).ready(function() {
    $('#category').select2();
});
</script> --}}
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
