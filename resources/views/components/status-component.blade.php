<div class="w-full p-0 m-0 text-white bg-primary">
    <div class="flex flex-col justify-between w-full px-4 py-3 mx-auto max-w-7xl lg:px-0 md:flex-row">
        <div class="flex items-center space-x-4 text-center md:text-left">

            @php
                $repair = \App\Models\Room::where('status', 'repair')->count();

                $unpaid = \App\Models\Room::whereHas('statuses', function ($query) use ($date) {
                    // $query->whereBetween('date', [$startDate, $endDate]);
                    $query->where('status', 'unpaid')->whereDate('date', $date);
                })->count();

                $paid = \App\Models\Room::whereHas('statuses', function ($query) use ($date) {
                    // $query->whereBetween('date', [$startDate, $endDate]);
                    $query->where('status', 'paid')->whereDate('date', $date);
                })->count();

                $occupied = \App\Models\Room::where('status', 'occupied')
                    // ->whereHas('statuses', function ($query) use ($date) {
                    //     // $query->whereBetween('date', [$startDate, $endDate]);
                    //     $query->where('status', 'occupied')->whereDate('date', $date);
                    // })
                    ->count();

                $dirty = \App\Models\Room::where('status', 'dirty')
                    // ->whereHas('statuses', function ($query) use ($date) {
                    //     // $query->whereBetween('date', [$startDate, $endDate]);
                    //     $query->where('status', 'dirty')->whereDate('date', $date);
                    // })
                    ->count();

                $available = \App\Models\Room::whereNotIn('status', ['occupied', 'dirty', 'repair'])
                    ->whereDoesntHave('statuses', function ($query) use ($date) {
                        // $query->whereBetween('date', [$startDate, $endDate]);
                        $query->whereDate('date', $date);
                    })
                    ->count();
            @endphp

            @if (\App\Models\Room::count() > 0)
                <p class="text-lg font-medium">Room Summary:</p>
                {{-- @foreach (\App\Models\Room::selectRaw('status, COUNT(*) as count')->groupBy('status')->get() as $room) --}}
                <p class="text-sm font-medium uppercase">
                    <span class="px-3 py-2 font-semibold text-black bg-gray-100 rounded">Available:
                        {{ $available }}</span>
                </p>
                <p class="text-sm font-medium uppercase">
                    <span class="px-3 py-2 text-white bg-black rounded">Repair: {{ $repair }}</span>
                </p>
                <p class="text-sm font-medium uppercase">
                    <span class="px-3 py-2 text-white rounded bg-unpaid">Unpaid: {{ $unpaid }}</span>
                </p>
                <p class="text-sm font-medium uppercase">
                    <span class="px-3 py-2 text-white rounded bg-paid">Paid: {{ $paid }}</span>
                </p>
                <p class="text-sm font-medium uppercase">
                    <span class="px-3 py-2 text-white rounded bg-occupied">Occupied:
                        {{ $occupied }}</span>
                </p>
                <p class="text-sm font-medium uppercase">
                    <span class="px-3 py-2 text-white rounded bg-dirty">Dirty:
                        {{ $dirty }}</span>
                </p>
                {{-- @endforeach
                <div class=""></div>
            @else
                <div>No room yet</div> --}}
            @endif
        </div>
        <p class="text-center md:text-left">
            <span>{{ date('h:ia') }}</span>
            <span>{{ date('l, d F Y') }}</span>
        </p>
    </div>
</div>
