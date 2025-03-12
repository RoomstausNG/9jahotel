<div class="grid w-full grid-cols-1 gap-6 pb-40 overflow-x-auto bg-white">
    <div class="flex w-full p-6 overflow-x-auto">
        <div class="w-full overflow-x-auto">
            <table>
                <thead class="">
                    <tr class="relative overflow-x-auto border">
                        <th class="max-w-[100px] p-4 absolute top-0 left-0 bg-gray-50 text-primary">Rooms</th>
                        @for ($i = date('d'); $i <= $numberOfDays; $i++)
                            <th class="p-4 overflow-x-auto border">{{ $i }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach (\App\Models\Room::all() as $room)
                        <tr class="border">
                            <th class="px-4 py-2 text-white uppercase border bg-primary">{{ $room->number }}</th>
                            @for ($i = date('d'); $i <= $numberOfDays; $i++)
                                <th
                                    class="px-4 py-2 {{ $room->status == 'available' ? 'border-black' : 'border-gray-900' }} overflow-x-auto border {{ count(getBookingDays($i, $currentMonth)) > 0 ? 'bg-' . $room->status : '' }}">
                                </th>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
