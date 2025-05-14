<div class="relative grid gap-4 pb-40 overflow-y-auto grid-col-1">
    <div class="flex items-center justify-between w-full space-y-2">
        <h1 class="text-2xl font-semibold text-white">Revenues</h1>
        <a href="{{ route('report') }}" class="px-6 py-2 text-gray-700 rounded bg-gray-50">Back</a>
    </div>
    <div class="grid grid-cols-1 p-4 bg-white rounded-lg space-2">
        <div x-data="{ type: @entangle('type').live }" class="flex flex-col justify-center w-full gap-6 rounded-lg md:flex-row">
            <button @click="$wire.selectType('room')"
                :class="type == 'room' ? 'bg-green-500  text-white' : 'shadow border bg-gray-100'"
                class="block px-8 py-2 text-center rounded shadow-sm hover:bg-green-500 hover:text-white">Revenue from
                Room</button>
            <button @click="$wire.selectType('hall')"
                :class="type == 'hall' ? 'bg-green-500  text-white' : 'shadow border bg-gray-100'"
                class="block px-8 py-2 text-center rounded shadow-sm hover:bg-green-500 hover:text-white">Revenue from
                Hall</button>
            <button @click="$wire.selectType('order')"
                :class="type == 'order' ? 'bg-green-500  text-white' : 'shadow border bg-gray-100'"
                class="block px-8 py-2 text-center rounded shadow-sm hover:bg-green-500 hover:text-white">Revenue from
                Sales</button>
            <button @click="$wire.selectType('corporate')"
                :class="type == 'corporate' ? 'bg-green-500  text-white' : 'shadow border bg-gray-100'"
                class="block px-8 py-2 text-center rounded shadow-sm hover:bg-green-500 hover:text-white">Corporate /
                Collection Accounts</button>
        </div>
        <form
            wire:submit=@if ($type == 'order') 'checkSales' @elseif($type == 'room') 'checkRooms' @elseif($type == 'hall') 'checkHalls' @else 'checkCustomer' @endif
            class="flex flex-col flex-wrap items-end w-full gap-4 p-4 bg-white rounded-lg shadow-sm lg:flex-row">
            @if ($type == 'room')
                <div class="flex-1">
                    <x-form.select name="bookable_id" label="Room">
                        <option>Select Room</option>
                        <option value="all">All Room</option>
                        @foreach (\App\Models\Room::with('type:id,name')->get() as $room)
                            <option value="{{ $room->id }}">
                                {{ strtoupper($room->number . ' - ' . $room->type->name . ' (' . formatAsNaira($room->price) . ')') }}
                            </option>
                        @endforeach

                    </x-form.select>
                </div>
            @elseif($type == 'hall')
                <div class="flex-1">
                    <x-form.select name="bookable_id" label="Hall">
                        <option>Select Hall</option>
                        <option value="all">All Hall</option>
                        @foreach (\App\Models\Hall::with('type:id,name')->get() as $hall)
                            <option value="{{ $hall->id }}">
                                {{ strtoupper($hall->name . ' (' . formatAsNaira($hall->price) . ')') }}
                            </option>
                        @endforeach

                    </x-form.select>
                </div>
            @elseif($type == 'corporate')
                {{-- <div class="flex-1">
                    <x-form.select name="business_id" label="Business">
                        <option>Select Business</option>
                        @foreach (\App\Models\Business::select('id', 'name')->get() as $business)
                            <option value="{{ $business->id }}">
                                {{ ucfirst($business->name) }}
                            </option>
                        @endforeach
                        <option value="all">All Business</option>
                    </x-form.select>
                </div> --}}
                <div class="w-full pb-8 overflow-y-auto">
                    <div
                        class="mb-4 overflow-x-auto overflow-y-scroll bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <table id="" class="w-full overflow-x-auto overflow-y-auto rounded bord">
                            <thead class="w-full overflow-x-auto rounded">
                                <tr class="w-full font-medium bg-gray-100 rounded">

                                    <th class="p-4 font-medium text-left"></th>
                                    <th class="p-4 font-medium text-left">Business</th>
                                    <th class="p-4 font-medium text-left nowrap">Amount Owing</th>
                                    <th class="p-4 font-medium text-left nowrap">Credit Limit</th>
                                    <th class="p-4 font-medium text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (\App\Models\Business::all() as $biz)
                                    <tr class="border-b even:bg-gray-50">
                                        {{-- <td class="px-4 py-2">

                                </td> --}}
                                        <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                        <td class="px-4 py-2">{{ $biz->name }}</td>

                                        <td class="px-4 py-2 uppercase nowrap">
                                            {{ formatAsNaira($biz->balance) }}
                                        </td>

                                        <td class="px-4 py-2 text-sm">
                                            {{ formatAsNaira($biz->limit) }}
                                        </td>

                                        <td class="px-4 py-2 text-sm">
                                            <p class="flex items-center justify-start space-x-2 text-sm">

                                                <a href="{{ route('business.payment', $biz->id) }}"
                                                    data-tooltip-target="edit" data-tooltip-placement="left"
                                                    class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-600 border border-blue-600 rounded-md">
                                                    <i class="fa-solid fa-pen-to-square"></i> Update Payment
                                                </a>
                                                <button wire:click="notifyBusiness({{ $biz->id }})"
                                                    data-tooltip-target="delete" data-tooltip-placement="left"
                                                    class="flex items-center justify-center px-2 py-1 space-x-2 text-green-500 border border-green-500 rounded-md">
                                                    Notify
                                                </button>
                                                <a href="{{ route('business.histories', $biz->id) }}"
                                                    data-tooltip-target="history" data-tooltip-placement="left"
                                                    class="flex items-center justify-center px-2 py-1 space-x-2 border border-red-600 rounded-md text-primary">
                                                    histories
                                                </a>
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
                    </div>
                </div>
            @else
                <div class="flex-1">
                    <x-form.select name="item_id" label="Items">
                        <option>Select Item</option>
                        <option value="all">All Items</option>
                        @foreach (\App\Models\Item::select('id', 'name', 'price')->get() as $item)
                            <option value="{{ $item->id }}">
                                {{ strtoupper($item->name . ' (' . formatAsNaira($item->price) . ')') }}
                            </option>
                        @endforeach

                    </x-form.select>
                </div>
            @endif

            @if ($type != 'corporate')
                <div class="w-full max-w-[300px]">
                    <x-input.text type="date" label="start Date" name="start" />
                </div>
                <div class="w-full max-w-[300px]">
                    <x-input.text type="date" label="end Date" name="end" />
                </div>
                <div class="">
                    <button
                        class="inline-block px-6 py-2 text-white align-bottom rounded-lg shadow-sm bg-primary-700 nowrap max-w-max">View
                        Report</button>
                </div>
            @endif
        </form>
    </div>
    @if ($showTable)
        @if ($type == 'order')
            <div class="w-full p-4 space-y-2 overflow-y-auto bg-white">
                <h1 class="text-xl font-semibold">Items Revenue Report</h1>
                <div class="flex w-full gap-4">
                    <p class="text-xl">
                        <span></span>
                    </p>
                </div>
                <div
                    class="mb-4 overflow-x-auto overflow-y-scroll bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <table id="" class="w-full overflow-x-auto overflow-y-auto rounded bord">
                        <thead class="w-full overflow-x-auto rounded">
                            <tr class="w-full font-medium bg-gray-100 rounded">
                                {{-- <th class="p-4 font-medium text-left">
                            <input type="checkbox" value="" wire:model="all" class="select-input">
                        </th> --}}
                                {{-- <th class="p-4 font-medium text-left nowrap">Sales Code</th> --}}
                                <th class="p-4 font-medium text-left nowrap">Payment Mode</th>
                                <th class="p-4 font-medium text-left nowrap">Item Sold</th>
                                <th class="p-4 font-medium text-left nowrap">Unit Price</th>
                                <th class="p-4 font-medium text-left nowrap">Sales Quantity</th>
                                <th class="p-4 font-medium text-left nowrap">Discount(%)</th>
                                <th class="p-4 font-medium text-left nowrap">Total Price</th>
                                <th class="p-4 font-medium text-left nowrap">Sales Person</th>
                                <th class="p-4 font-medium text-left nowrap">Sales Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- <td></td> --}}
                            <td></td>
                            <td></td>
                            <td></td>
                            {{-- <td>{{ $item_id ? $order->first->item->name : '' }}</td> --}}
                            <td class="p-4 text-center">
                                <span class="text-xl font-bold">{{ $order->sum('quantity') }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <span
                                    class="text-xl font-bold">{{ number_format($order->sum('discount') / $order->count(), 2) }}</span>
                            </td>
                            <td class="p-4">
                                <span class="text-xl font-bold">{{ formatAsNaira($order->sum('total_price')) }}</span>

                            </td>
                            <td></td>
                            @forelse ($order as $sale)
                                <tr class="border-b even:bg-gray-50">

                                    {{-- <td class="px-4 py-2 text-sm font-bold uppercase break-keep nowrap">
                                        {{ $sale->code }}
                        </td> --}}
                                    <td class="px-4 py-2 capitalize">
                                        {{ $sale->mode_of_payment }}
                                    </td>
                                    <td class="px-4 py-2 capitalize nowrap">{{ $sale->item->name }}</td>

                                    <td class="px-4 py-2 text-sm">
                                        {{ formatAsNaira($sale->item->cost_price ?? $sale->item->price) }}
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        {{ $sale->quantity }}
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        {{ $sale->discount }}
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        {{ formatAsNaira(($sale->item->price ?? $sale->item->cost_price) * $sale->quantity) }}
                                    </td>

                                    <td class="px-4 py-2 text-sm nowrap">
                                        {{ $sale->agent->firstname . ' ' . $sale->agent->lastname }}
                                    </td>
                                    <td class="px-4 py-2 text-sm nowrap">
                                        {{ $sale->created_at?->format('d M, Y') }}</td>
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
                    {{-- <div class="px-4">
                        <p class="my-2">
                            {{ $order->links() }}
            </p>
        </div> --}}
                </div>
            </div>
        @elseif($type == 'room')
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
                                <th class="p-4 font-medium text-left nowrap">Room Info</th>
                                <th class="p-4 font-medium text-left">Discount(%)</th>
                                <th class="p-4 font-medium text-left nowrap">Total Due</th>
                                <th class="p-4 font-medium text-left nowrap">Total Received</th>
                                <th class="p-4 font-medium text-left">Customer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rooms as $reserv)
                                <tr class="border-b even:bg-gray-50">
                                    {{-- <td class="px-4 py-2">

                                </td> --}}
                                    <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-2 text-sm capitalize break-keep nowrap">
                                        {{ $reserv->checked_in?->format('d M, Y') }}
                                    </td>

                                    <td class="px-4 py-2 text-sm capitalize break-keep nowrap">
                                        {{ $reserv->checked_out?->format('d M, Y') }}
                                    </td>

                                    <td class="px-4 py-2 uppercase nowrap">
                                        {{ $reserv->bookable->number . ' ' . $reserv->bookable->type->name . ' (' . formatAsNaira($reserv->bookable->price) . ')' }}
                                    </td>

                                    <td class="px-4 py-2 text-sm">
                                        {{ $reserv->discount ?? 0 }}
                                    </td>

                                    <td class="px-4 py-2 text-sm">
                                        {{ formatAsNaira($reserv->due) }}
                                    </td>

                                    <td class="px-4 py-2 text-sm">
                                        {{ formatAsNaira($reserv->amount_paid) }}</td>

                                    <td class="px-4 py-2 text-sm">
                                        <span href="" class="nowrap">
                                            <span>{{ $reserv->customer->firstname . ' ' . $reserv->customer->lastname }}</span>
                                            <br>
                                            <span>{{ $reserv->customer->phone }}</span> <br>
                                            <span>{{ $reserv->customer->email }}</span>
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="p-4 text-center" colspan="8">No data...</td>
                                </tr>
                            @endforelse
                            <tr>
                                <td class="p-4"></td>
                                <td class="p-4"></td>
                                <td class="p-4"></td>
                                <td class="p-4"></td>
                                <td class="p-4"></td>
                                <td class="p-4 text-lg font-medium">{{ formatAsNaira($rooms->sum('due')) }}</td>
                                <td class="p-4 text-lg font-medium">{{ formatAsNaira($rooms->sum('amount_paid')) }}
                                </td>
                                <td class="p-4 text-lg font-medium">
                                    {{ 'Balance: ' . formatAsNaira($rooms->sum('due') - $rooms->sum('amount_paid')) }}
                                </td>
                            </tr>
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
        @else
            @if ($halls?->count())
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
                                    <th class="p-4 font-medium text-left nowrap">Hall Info</th>
                                    <th class="p-4 font-medium text-left">Discount(%)</th>
                                    <th class="p-4 font-medium text-left nowrap">Total Due</th>
                                    <th class="p-4 font-medium text-left nowrap">Total Received</th>
                                    <th class="p-4 font-medium text-left">Customer</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($halls as $reserv)
                                    <tr class="border-b even:bg-gray-50">
                                        {{-- <td class="px-4 py-2">

                            </td> --}}
                                        <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                        <td class="px-4 py-2 text-sm capitalize break-keep nowrap">
                                            {{ $reserv->checked_in?->format('d M, Y') }}
                                        </td>

                                        <td class="px-4 py-2 text-sm capitalize break-keep nowrap">
                                            {{ $reserv->checked_out?->format('d M, Y') }}
                                        </td>

                                        <td class="px-4 py-2 uppercase nowrap">
                                            {{ $reserv->bookable->name . ' (' . formatAsNaira($reserv->bookable->price) . ')' }}
                                        </td>

                                        <td class="px-4 py-2 text-sm">
                                            {{ $reserv->discount ?? 0 }}
                                        </td>

                                        <td class="px-4 py-2 text-sm">
                                            {{ formatAsNaira($reserv->due) }}
                                        </td>

                                        <td class="px-4 py-2 text-sm">
                                            {{ formatAsNaira($reserv->amount_paid) }}</td>

                                        <td class="px-4 py-2 text-sm">
                                            <span href="" class="nowrap">
                                                <span>{{ $reserv->customer->firstname . ' ' . $reserv->customer->lastname }}</span>
                                                <br>
                                                <span>{{ $reserv->customer->phone }}</span> <br>
                                                <span>{{ $reserv->customer->email }}</span>
                                            </span>
                                        </td>


                                    </tr>
                                @empty
                                    <tr>
                                        <td class="p-4 text-center" colspan="8">No data...</td>
                                    </tr>
                                @endforelse
                                <tr>
                                    <td class="p-4"></td>
                                    <td class="p-4"></td>
                                    <td class="p-4"></td>
                                    <td class="p-4"></td>
                                    <td class="p-4"></td>
                                    <td class="p-4 text-lg font-medium">{{ formatAsNaira($halls->sum('due')) }}</td>
                                    <td class="p-4 text-lg font-medium">
                                        {{ formatAsNaira($halls->sum('amount_paid')) }}
                                    </td>
                                    <td class="p-4 text-lg font-medium">
                                        {{ 'Balance: ' . formatAsNaira($halls->sum('due') - $halls->sum('amount_paid')) }}
                                    </td>
                                </tr>
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
            @endif
        @endif
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
