@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .reserve {
            background-image: url({{ asset('img/reserve-bg.jpg') }});
            background-size: cover;
        }
    </style>
@endpush

<div class="space-y-6" x-data="{ type: @entangle('type').live }" wire:poll.5s>
    <div class="flex w-full overflow-hidden font-medium uppercase bg-white rounded-lg">
        <a href="{{ route('reservations') }}"
            class="w-1/2 py-3 text-center {{ request()->is('bookings/reservations') ? 'bg-paid text-white' : 'bg-white text-black' }}">Room
            Reservation</a>
        <a href="{{ route('hall-reservations') }}"
            class="w-1/2 py-3 text-center {{ request()->is('bookings/hall-reservations') ? 'bg-paid text-white' : 'bg-white text-black' }}">Hall
            Reservation</a>
    </div>
    <div class="w-full p-4 space-y-6 overflow-y-scroll rounded-lg reserve">
        <div class="flex items-end justify-between space-x-4 ">
            @php
                $newDate = new \DateTime($date);
            @endphp
            <h1 class="text-2xl font-medium text-white md:text-3xl">
                Room Reservation
            </h1>
            <form class="" wire:submit='changeDate' class="flex flex-col justify-end">
                <p class="p-1 text-lg text-white">Click to change date</p>
                <div class="flex flex-wrap space-x-2">
                    <input type="date" wire:model='date'
                        class="w-[200px] rounded-md focus:ring-0 focus:border-primary">
                    <button class="p-2 px-3 text-white border rounded-md hover:bg-primary-700 bg-primary">
                        <i class="text-normal fa-solid fa-paper-plane"></i>
                    </button>

                </div>
            </form>
        </div>
        <span class="inline-block w-full -mt-8 text-right">Showing status for
            <span class="font-bold"> {{ $newDate->format('l, d M Y') }}</span>
        </span>

        <div
            class="flex flex-wrap w-full gap-6 p-4 overflow-x-auto overflow-y-visible rounded-lg shadow-sm md:gap-8 lg:gap-12">
            @forelse (\App\Models\Room::with(['floor','type'])->get() as $room)
                <a href="{{ route('room.reservation', $room->id) }}" data-tooltip-target="{{ $room->id }}"
                    data-tooltip-placement="bottom" wire:click="selectBookable({{ $room->id }})"
                    class="inline-block hover:-translate-y-2 transform duration-300  text-center w-[150px] py-4 {{ roomStatus($room, $date) == 'available' ? 'text-black' : 'text-white' }}  {{ 'bg-' . roomStatus($room, $date) }} rounded-lg shadow">
                    <div class="text-center">
                        <span
                            class="text-sm font-medium uppercase">{{ $room->type ? $room->type?->name : $room->number }}</span>
                        <br>
                        <span class="font-bold uppercase">{{ $room->number }}</span>
                    </div>
                </a>
                <x-booktooltip id="{{ $room->id }}" :date="$date" :room="$room" />
            @empty
                <p>No room yet</p>
            @endforelse
        </div>
        <div class="w-full pb-8 overflow-y-auto">
            <div class="flex items-center justify-end pb-6 space-x-4 text-2xl font-medium">
                <x-form.search />
            </div>
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
                                    {{ formatAsNaira($reservation->bookable?->type?->price1) }}
                                </td>

                                <td class="px-4 py-2 text-sm text-center">
                                    {{ $reservation->discount ?? 0 }}
                                </td>

                                <td class="px-4 py-2 text-sm">
                                    {{ formatAsNaira($reservation->due) }}
                                </td>

                                <td class="px-4 py-2 text-sm text-center">
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
                                        {{--  <a href="" data-tooltip-placement="left"
                                            class="flex items-center justify-center px-2 py-1 space-x-2 text-green-600 border border-green-600 rounded-md">
                                            <i class="mr-1 fa-solid fa-lock"></i> Create Key Card
                                        </a> --}}

                                        <a href="{{ route('booking.receipt', $reservation->code) }}" target="_balnk"
                                            data-tooltip-target="print" data-tooltip-placement="left"
                                            class="flex items-center justify-center px-2 py-1 space-x-2 text-green-500 border border-green-500 rounded-md">
                                            <i class="fa-solid fa-print"></i> <span>Print</span>
                                        </a>
                                        {{-- <button wire:click="changeStatus({{ $reservation->id }})"
                                    wire:confirm="Are you sure you want to perform this action?"
                                    data-tooltip-target="delete" data-tooltip-placement="left"
                                    class="border flex items-center justify-center space-x-2 {{ $reservation->can_login ? 'text-red-600 border-red-600' : 'text-green-600 border-green-600' }} rounded-md px-2 py-1">
                                    @if ($reservation->can_login)
                                    <i class="fa-solid fa-times"></i>
                                    @else
                                    <i class="fa-solid fa-check"></i>
                                    @endif
                                    <span>{{ $reservation->can_login ? 'Disable' : 'Enable' }}</span>
                                    </button> --}}


                                        <button wire:click="deleteBooking({{ $reservation->id }})"
                                            wire:confirm="Booking will be deleted. Are you sure you want to perform this action?"
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
                        {{ $reservations->links() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- status component --}}
    <x-status-component :date="$date" />

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
