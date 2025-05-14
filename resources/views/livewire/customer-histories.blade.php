<div class="pb-32 space-y-6">
    <div class="p-4 space-y-4 bg-white rounded-lg w-ful" x-data="{ step: 1 }">
        <div class="flex items-center justify-between space-x-4 text-2xl ">
            <h1 class="font-medium">
                Customer Booking Histories <span
                    class="px-6 py-2 ml-4 font-medium capitalize border border-gray-300 rounded-md shadow-sm text-primary">{{ $customer->firstname . ' ' . $customer->lastname }}</span>
            </h1>
            <a href="{{ route('configuration.customers') }}"
                class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a>
        </div>
    </div>
    @if ($customer->bookings()->exists())
        <div class="w-full p-4 space-y-6 overflow-y-scroll bg-white rounded-lg bb-20" x-data="{ active: 'hotel' }">
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
                                <th class="p-4 font-medium text-left nowrap">Total Amount paid</th>
                                <th class="p-4 font-medium text-left">Customer</th>
                                <th class="p-4 font-medium">Status</th>
                                <th class="p-4 font-medium text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookings as $reservation)
                                <tr wire:key='{{ $reservation->id }}' class="border-b even:bg-gray-50">
                                    {{-- <td class="px-4 py-2">

                                </td> --}}
                                    <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-2 text-sm capitalize break-keep nowrap">
                                        {{ $reservation->checked_in?->format('d M, Y') }}
                                    </td>

                                    <td class="px-4 py-2 text-sm capitalize break-keep nowrap">
                                        {{ $reservation->checked_out?->format('d M, Y') }}
                                    </td>

                                    <td class="px-4 py-2 ">
                                        {{ formatAsNaira($reservation->bookable->type->price1) }}
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
                                            {{-- <button wire:click="editBooking({{ $reservation->id }})" data-tooltip-target="edit" data-tooltip-placement="left" class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-600 border border-blue-600 rounded-md">
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
                            {{ $bookings?->links() }}
                        </p>
                    </div>
                </div>
            </div>


        </div>
    @else
        <div class="p-4 my-4 bg-white rounded-lg shadow">
            <p class="p-2 text-xl text-yellow-400">Customer has no records of booking yet!</p>
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
                    placeholder: '{{ __('
                                    Select your option ') }}',
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
