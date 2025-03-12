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
                Halls
            </h1>
            <a href="{{ route('configuration') }}" class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a>
        </div>
        <div class="w-full space-y-4" x-data="{ hallForm: @entangle('hallForm').live }">
            <div class="flex items-center justify-between space-x-4 text-2xl font-medium">
                <button @click="hallForm = true"
                    class="px-6 py-2 text-sm text-white rounded-md bg-primary">{{ $updateH ? 'Update Hall' : 'Add Hall' }}</button>

                <x-form.search />
            </div>
            <div class="" x-cloak x-show="hallForm">
                <form wire:submit=@if ($updateH) 'updateHall' @else 'AddHall' @endif
                    class="w-full space-y-4">
                    <div
                        class="grid grid-cols-1 gap-4 overflow-y-auto text-sm md:gap-6 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                        <x-input.text label="Hall Name" name="name" />
                        <x-input.text label="Capacity" name="capacity" />
                        <x-input.text label="description" name="description" />
                        <x-input.text label="price*" name="price" />
                        <x-input.text label="deposit" name="deposit" />
                        <x-input.text label="promo price" name="promo_price" />
                        <x-input.text type="date" label="Promo start Date" name="promo_start_day" />
                        <x-input.text type="date" label="Promo end Date" name="promo_end_day" />

                        <x-form.select name="status" label="Hall Status">
                            <option>Select Status</option>
                            @foreach (['available', 'pending', 'unpaid', 'paid', 'occupied', 'late', 'dirty', 'repair'] as $status)
                                <option value="{{ $status }}" class="capitalize">
                                    {{ $status }}
                                </option>
                            @endforeach
                        </x-form.select>

                    </div>
                    <button class="px-6 py-2 text-sm text-white rounded-md bg-primary max-w-max">Save</button>
                </form>
            </div>
        </div>

        <div id="halls" class="w-full pb-8 overflow-y-auto">
            <div
                class="mb-4 overflow-x-auto overflow-y-scroll bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <table id="" class="w-full overflow-x-auto overflow-y-auto rounded bord">
                    <thead class="w-full overflow-x-auto rounded">
                        <tr class="w-full font-medium bg-gray-100 rounded">
                            {{-- <th class="p-4 font-medium text-left">
                            <input type="checkbox" value="" wire:model="all" class="select-input">
                        </th> --}}
                            <th class="p-4 font-medium text-left">S/N</th>
                            <th class="p-4 font-medium text-left nowrap">Hall Name</th>
                            <th class="p-4 font-medium text-left nowrap">Hall Description</th>
                            {{-- <th class="p-4 font-medium text-left nowrap">Room Type</th> --}}
                            <th class="p-4 font-medium text-left">Capacity</th>
                            <th class="p-4 font-medium text-left">Price</th>
                            <th class="p-4 font-medium text-left">Deposit
                            </th>
                            <th class="p-4 font-medium">Remarks</th>
                            <th class="p-4 font-medium nowrap">Promo Price</th>
                            <th class="p-4 font-medium nowrap">Promo Start Date</th>
                            <th class="p-4 font-medium nowrap">Promo End Date</th>
                            <th class="p-4 font-medium">Status</th>
                            <th class="p-4 font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($halls as $hall)
                            <tr wire:key='{{ $hall->id }}' class="border-b even:bg-gray-50">

                                <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2 text-sm uppercase break-keep nowrap">
                                    {{ $hall->name }}
                                </td>
                                <td class="px-4 py-2">
                                    <span class="capitalize ">{{ $hall->description }}</span>
                                </td>

                                <td class="px-4 py-2 text-center">
                                    {{ $hall->capacity }}
                                </td>
                                {{-- <td class="px-4 py-2 capitalize"></td> --}}
                                <td class="px-4 py-2">
                                    <span>{{ formatAsNaira($hall->price) }}</span>
                                </td>
                                <td class="px-4 py-2">
                                    <span>{{ formatAsNaira($hall->deposit) }}</span>
                                </td>
                                </td>
                                <td class="px-4 py-2 ">
                                    {{ $hall->remarks }}
                                </td>

                                <td class="px-4 py-2 ">
                                    {{ formatAsNaira($hall->promo_price) }}
                                </td>
                                <td class="px-4 py-2 ">
                                    {{ $hall->promo_start_day }}
                                </td>
                                <td class="px-4 py-2 ">
                                    {{ $hall->promo_end_day }}
                                </td>
                                {{-- <td class="px-4 py-2 ">
                                    {{ $hall->reference }}
                                </td> --}}
                                <td class="px-4 py-2 text-xs font-semibold text-center uppercase">
                                    <span
                                        class="py-2 rounded-full px-4 border {{ $hall->status == 'available' ? 'text-black' : 'text-white' }}  {{ 'bg-' . $hall->status }}">{{ $hall->status }}</span>
                                </td>

                                <td class="px-4 py-2">
                                    <p class="flex items-center justify-center space-x-2 text-sm">
                                        <a href="{{ route('hall.histories', $hall->id) }}"
                                            data-tooltip-target="history" data-tooltip-placement="left"
                                            class="flex items-center justify-center px-2 py-1 space-x-2 text-teal-600 border border-teal-600 rounded-md">
                                            </i>Histories
                                        </a>
                                        <button wire:click="editHall({{ $hall->id }})" data-tooltip-target="edit"
                                            data-tooltip-placement="left"
                                            class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-600 border border-blue-600 rounded-md">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        <button wire:click="deleteHall({{ $hall->id }})"
                                            wire:confirm="Are you sure you want to perform this action?"
                                            data-tooltip-target="delete" data-tooltip-placement="left"
                                            class="flex items-center justify-center px-2 py-1 space-x-2 text-red-600 border border-red-600 rounded-md">
                                            Delete
                                        </button>
                                    </p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="8">No data...</td>
                            </tr>
                        @endforelse
                        {{-- edit tooltip --}}
                        <x-tooltip id="edit" content="Edit hall data" />

                        {{-- edit tooltip --}}
                        <x-tooltip id="delete" content="Delete hall" />
                    </tbody>
                </table>
                <div class="px-4">
                    <p class="my-2">
                        {{ $halls->links(data: ['scrollTo' => '#halls']) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    <p class="bg-available "></p>
    <p class="bg-pending"></p>
    <p class="bg-unpaid"></p>
    <p class="bg-paid"></p>
    <p class="bg-occupied"></p>
    <p class="bg-late"></p>
    <p class="bg-dirty"></p>
    <p class="bg-repair"></p>
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
