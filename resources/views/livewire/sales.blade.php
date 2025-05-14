<div class="w-full p-4 space-y-6 overflow-y-scroll bg-white rounded-lg" x-data="{ active: 'hotel' }">
    <div class="flex items-center justify-between space-x-4 text-2xl ">
        <h1 class="font-medium">
            Sales
        </h1>
        <a href="{{ route('configuration') }}" class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a>
    </div>

    <div class="w-full space-y-4" x-data="{ orderForm: @entangle('orderForm').live }">
        <div class="flex items-center justify-between space-x-4 text-2xl font-medium">
            <button @click="orderForm = true"
                class="px-6 py-2 text-sm text-white rounded-md bg-primary">{{ $updateC ? 'Update Sale' : 'Add Sale' }}</button>

            <x-form.search />
        </div>
        <div class="" x-cloak x-show="orderForm">
            <form wire:submit=@if ($updateC) 'updateSale' @else 'AddSale' @endif
                class="w-full space-y-4">
                <div
                    class="grid grid-cols-1 gap-4 overflow-y-auto text-sm md:gap-6 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                    <x-form.select name="type" wire:model.live='type' class="capitalize" label="Customer Type">
                        <option>Select Item</option>
                        @foreach (['guest', 'in-room guest'] as $ty)
                            <option value="{{ $ty }}" class="capitalize">
                                {{ $ty }}
                            </option>
                        @endforeach
                    </x-form.select>
                    @if ($type != 'guest')
                        <x-form.select name="booking_id" label="Select Room">
                            <option>Select Room</option>
                            {{-- @forelse (\App\Models\Booking::with(['customer:id,firstname,lastname'])->whereIn('status', ['occupied'])->whereRelation('statuses', 'date', now())->get() as $booking) --}}
                            @forelse (\App\Models\Booking::with(['customer:id,firstname,lastname'])
                            ->where('status', 'occupied')
                            ->whereHas('statuses', fn($query)=>$query->whereDate('date','<=', now())->where('status', 'occupied'))
                            ->get() as $booking)
                                <option value="{{ $booking->id }}">
                                    {{ 'Room: ' . strtoupper($booking->bookable->number) . ' ' . $booking->customer->firstname . ' ' . $booking->customer->lastname }}
                                </option>
                            @empty
                                <option>No room is occupied yet</option>
                            @endforelse
                        </x-form.select>
                    @endif
                    <x-form.select name="item_id" label="Items">
                        <option>Select Item</option>
                        @foreach (\App\Models\Item::select('id', 'name', 'cost_price', 'price')->get() as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->name . ' - (' . formatAsNaira($item->price) . ')' }}
                            </option>
                        @endforeach
                    </x-form.select>

                    <x-input.text label="Quantity" name="quantity" />
                    <x-form.select name="mode_of_payment" label="Mode of Payment">
                        <option>Select Item</option>
                        @foreach (['Transfer', 'Cash', 'Credit', 'Online', 'POS'] as $mode)
                            <option value="{{ $mode }}">
                                {{ $mode }}
                            </option>
                        @endforeach
                    </x-form.select>
                    <x-input.text label="Discount (%)" name="discount" />

                </div>
                <button class="px-6 py-2 text-sm text-white rounded-md bg-primary max-w-max">Save</button>
            </form>
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
                        <th class="p-4 font-medium text-left nowrap">Sales Code</th>
                        <th class="p-4 font-medium text-left nowrap">Payment Mode</th>
                        <th class="p-4 font-medium text-left nowrap">Item Sold</th>
                        <th class="p-4 font-medium text-left nowrap">Discount (%)</th>
                        <th class="p-4 font-medium text-left nowrap">Sales Quantity</th>
                        <th class="p-4 font-medium text-left nowrap">Unit Price</th>
                        <th class="p-4 font-medium text-left nowrap">Total Price</th>
                        <th class="p-4 font-medium text-left nowrap">Sales Person</th>
                        <th class="p-4 font-medium text-left nowrap">Sales Date</th>
                        <th class="p-4 font-medium text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr wire:key='{{ $sale->id }}' class="border-b even:bg-gray-50">

                            <td class="px-4 py-2 text-sm font-bold uppercase break-keep nowrap">
                                {{ $sale->code }}
                            </td>
                            <td class="px-4 py-2 capitalize">
                                {{ $sale->mode_of_payment }}
                            </td>
                            <td class="px-4 py-2 capitalize nowrap">{{ $sale->item->name }}</td>
                            <td class="px-4 py-2 text-center">
                                {{ $sale->discount }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                {{ $sale->quantity }}
                            </td>
                            <td class="px-4 py-2 text-sm">
                                {{ formatAsNaira($sale->item->price) }}
                            </td>

                            <td class="px-4 py-2 text-sm">
                                {{ formatAsNaira($sale->total_price) }}
                            </td>

                            <td class="px-4 py-2 text-sm nowrap">
                                {{ $sale->agent->firstname . ' ' . $sale->agent->lastname }}
                            </td>
                            <td class="px-4 py-2 text-sm nowrap">
                                {{ $sale->created_at?->format('d M, Y') }}</td>
                            <td class="px-4 py-2">
                                <p class="flex items-center justify-start space-x-2 text-sm">
                                    <a href="{{ route('sales.receipt', $sale->code) }}" target="_balnk"
                                        data-tooltip-target="print" data-tooltip-placement="left"
                                        class="flex items-center justify-center px-2 py-1 space-x-2 text-green-500 border border-green-500 rounded-md">
                                        <i class="fa-solid fa-print"></i> <span>Print</span>
                                    </a>

                                    <button wire:click="editSale({{ $sale->id }})" data-tooltip-target="edit"
                                        data-tooltip-placement="left"
                                        class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-600 border border-blue-600 rounded-md">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <button wire:click="deleteSale({{ $sale->id }})"
                                        wire:confirm="Sales and all associated finaces will be deleted as well. Are you sure you want to perform this action?"
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

                    {{-- print tooltip --}}
                    <x-tooltip id="print" content="Print order receipt" />

                    {{-- edit tooltip --}}
                    <x-tooltip id="delete" content="Change Status" />
                </tbody>
            </table>
            <div class="px-4">
                <p class="my-2">
                    {{ $sales->links() }}
                </p>
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
@endpush
