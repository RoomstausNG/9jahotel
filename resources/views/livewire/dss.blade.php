<div class="relative grid gap-4 pb-40 overflow-y-auto grid-col-1">
    <div class="flex items-center justify-between w-full space-y-2">
        <h1 class="text-2xl font-semibold text-white">Guest List</h1>
        <a href="{{ route('report') }}" class="px-6 py-2 text-gray-700 rounded bg-gray-50">Back</a>
    </div>
    <div class="grid grid-cols-1 p-4 bg-white rounded-lg space-2">
        <form wire:submit="generateList"
            class="flex flex-col flex-wrap items-end w-full gap-4 p-4 bg-white rounded-lg shadow-sm lg:flex-row">
            <div class="flex items-end space-x-4">
                <x-input.text type="date" label="Date" name="date" />
                <button class="px-6 py-2 text-white rounded-lg bg-primary">Submit</button>
                {{-- <x-form.select name="bookable_id" label="Room">
                    <option>Select Room</option>
                    <option value="all">All Room</option>
                    @foreach (\App\Models\Room::with('type:id,name')->get() as $room)
                        <option value="{{ $room->id }}">
                            {{ strtoupper($room->number . ' - ' . $room->type->name . ' (' . formatAsNaira($room->price) . ')') }}
                        </option>
                    @endforeach
                </x-form.select> --}}
            </div>
        </form>
    </div>
    @if ($showTable)
        <div class="w-full p-4 space-y-2 overflow-y-auto bg-white">
            <div class="flex justify-start gap-6 py-2 space-x-8">
                <h1 class="text-xl font-semibold">Guest Lists as of {{ $date }} </h1>
                @if ($bookings->count() > 0)
                    <div class="flex gap-4">
                        <div class="">
                            {{-- <label for="email" class="mb-2 text-sm">New Password</label><br> --}}
                            <input type="text" wire:model='email' placeholder="email address"
                                class="px-2 py-1 border rounded focus:ring-0 focus:border-primary"> <br>
                            @error('email')
                                <small class="text-red-600">{{ $message }}</small>
                            @enderror
                        </div>
                        <button wire:click="sendList()" class="px-4 py-2 text-sm text-white bg-green-500 rounded">Send
                            to
                            mail</button>
                        <a type="button" href="{{ route('guest-report', $date) }}"
                            class="px-4 py-2 text-sm text-white bg-blue-500 border-0 rounded">View
                            List</a>
                    </div>
                @endif
            </div>
            <div
                class="mb-4 overflow-x-auto overflow-y-scroll bg-white border border-gray-200 rounded-lg shadow dark:border-gray-700 dark:bg-gray-900">
                <table id="" class="w-full overflow-x-auto overflow-y-auto rounded bord">
                    <thead class="w-full overflow-x-auto rounded">
                        <tr class="w-full font-medium bg-gray-100 rounded">
                            {{-- <th class="p-4 font-medium text-left">
                            <input type="checkbox" value="" wire:model="all" class="select-input">
                        </th> --}}
                            {{-- <th class="p-4 font-medium text-left nowrap">Sales Code</th> --}}
                            <th class="p-4 font-medium text-left nowrap">No</th>
                            <th class="p-4 font-medium text-left nowrap">Guest Name</th>
                            <th class="p-4 font-medium text-left nowrap">Gender</th>
                            <th class="p-4 font-medium text-left nowrap">ID Card Type</th>
                            <th class="p-4 font-medium text-left nowrap">ID Number</th>
                            <th class="p-4 font-medium text-left nowrap">Room Number</th>
                            <th class="p-4 font-medium text-left nowrap">Checked in Date</th>
                            <th class="p-4 font-medium text-left nowrap">Checked Out Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr class="border-b even:bg-gray-50">

                                <td class="px-4 py-2 text-sm font-bold uppercase break-keep nowrap">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-4 py-2 capitalize">
                                    {{ $booking->customer->name }}
                                </td>
                                <td class="px-4 py-2 capitalize">
                                    {{ $booking->customer->gender }}
                                </td>
                                <td class="px-4 py-2 capitalize">
                                    {{ $booking->customer->id_type }}
                                </td>
                                <td class="px-4 py-2 capitalize">
                                    {{ $booking->customer->id_number }}
                                </td>
                                <td class="px-4 py-2 uppercase">
                                    {{ $booking->bookable->number }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $booking->checked_in->format('D d M, Y') }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $booking->checked_out->format('D d M, Y') }}
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
            </div>
        </div>
    @endif
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
@endpush
