@push('styles')
    <style>
        .no-wrap {
            word-wrap: unset;
            word-break: keep-all;
            white-space: nowrap;
        }
    </style>
@endpush

<div class="w-full p-4 pb-20 space-y-8 overflow-y-scroll bg-white rounded-lg">
    <div class="p-4 space-y-6 overflow-y-scroll border rounded-lg shadow-sm">
        <div class="flex items-center justify-between space-x-4 text-2xl ">
            <h1 class="font-medium">
                Revenue Items Setup
            </h1>
            <a href="{{ route('configuration') }}" class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a>
        </div>
        <div class="w-full space-y-4" x-data="{ itemForm: @entangle('itemForm').live }">
            <div class="flex items-center justify-between space-x-4 text-2xl font-medium">
                <button @click="itemForm = true"
                    class="px-6 py-2 text-sm text-white rounded-md bg-primary">{{ $updateI ? 'Update Item' : 'Add Item' }}</button>

                <x-form.search />
            </div>
            <div class="" x-cloak x-show="itemForm">
                <form wire:submit=@if ($updateI) 'updateItem' @else 'AddItem' @endif
                    class="w-full space-y-4">
                    <div
                        class="grid grid-cols-1 gap-4 overflow-y-auto text-sm md:gap-6 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                        <x-form.select name="revenue_id" label="Revenue Point">
                            <option>Select Revenue Source</option>
                            @foreach (\App\Models\Revenue::select('id', 'source')->get() as $revenue)
                                <option value="{{ $revenue->id }}" class="capitalize">
                                    {{ $revenue->source }}
                                </option>
                            @endforeach
                        </x-form.select>
                        <x-input.text label="Item Name" name="name" />
                        <x-input.text label="quantity" name="quantity" />
                        <x-input.text label="price*" name="price" />
                        <x-input.text label="Cost Price" name="cost_price" />
                        <x-input.text label="Re Order limit" name="limit" />
                        <x-input.text label="Item Image" name="image" type="file" />
                    </div>
                    <button class="px-6 py-2 text-sm text-white rounded-md bg-primary max-w-max">Save</button>
                </form>
            </div>
        </div>

        <div id="items" class="w-full pb-8 overflow-y-auto">
            <div
                class="mb-4 overflow-x-auto overflow-y-scroll bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <table id="" class="w-full overflow-x-auto overflow-y-auto rounded bord">
                    <thead class="w-full overflow-x-auto rounded">
                        <tr class="w-full font-medium bg-gray-100 rounded">
                            {{-- <th class="p-4 font-medium text-left">
                            <input type="checkbox" value="" wire:model="all" class="select-input">
                        </th> --}}
                            <th class="p-4 font-medium text-left">S/N</th>
                            <th class="p-4 font-medium text-left nowrap">Item Image</th>
                            <th class="p-4 font-medium text-left nowrap">Item Name</th>
                            <th class="p-4 font-medium text-left nowrap">Cost Price</th>
                            <th class="p-4 font-medium text-left">Item Price</th>
                            <th class="p-4 font-medium text-left nowrap">Revenue Point</th>
                            <th class="p-4 font-medium">Quantity
                            </th>
                            <th class="p-4 font-medium">Re-Order Limit</th>
                            <th class="p-4 font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr wire:key='{{ $item->id }}' class="border-b even:bg-gray-50">

                                <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2 text-sm capitalize break-keep nowrap">
                                    <img src=" {{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                                        class="object-cover w-20 h-16">
                                </td>
                                <td class="px-4 py-2 text-sm capitalize break-keep nowrap">
                                    {{ $item->name }}
                                </td>
                                <td class="px-4 py-2">
                                    <span>{{ $item->cost_price ? formatAsNaira($item->cost_price) : '' }}</span>
                                </td>
                                <td class="px-4 py-2">
                                    <span>{{ formatAsNaira($item->price) }}</span>
                                </td>

                                <td class="px-4 py-2 capitalize">
                                    {{ $item->revenueSource?->source }}
                                </td>

                                </td>
                                <td class="px-4 py-2 text-center">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    {{ $item->limit }}
                                </td>
                                </td>

                                <td class="px-4 py-2">
                                    <p class="flex items-center justify-center space-x-2 text-sm">

                                        <button wire:click="editItem({{ $item->id }})" data-tooltip-target="edit"
                                            data-tooltip-placement="left"
                                            class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-600 border border-blue-600 rounded-md">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        <button wire:click="deleteItem({{ $item->id }})"
                                            wire:confirm="Orders and associated Revenues will be deleted as well. Are you sure you want to perform this action?"
                                            data-tooltip-target="delete" data-tooltip-placement="left"
                                            class="flex items-center justify-center px-2 py-1 space-x-2 text-red-600 border border-red-600 rounded-md">
                                            Delete
                                        </button>
                                    </p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-8 text-center" colspan="8">No data...</td>
                            </tr>
                        @endforelse
                        {{-- edit tooltip --}}
                        <x-tooltip id="edit" content="Edit item data" />

                        {{-- edit tooltip --}}
                        <x-tooltip id="delete" content="Delete item" />
                    </tbody>
                </table>
                <div class="px-4">
                    <p class="my-2">
                        {{ $items->links(data: ['scrollTo' => '#items']) }}
                    </p>
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
@endpush
