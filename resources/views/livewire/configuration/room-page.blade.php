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
                Room Type/Category
            </h1>
            <a href="{{ route('configuration') }}" class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a>
        </div>
        <div x-data="{ typeForm: @entangle('typeForm').live }" class="w-full space-y-2">
            <div class="flex items-center justify-between space-x-4 text-2xl font-medium">
                <button @click="typeForm = true"
                    class="px-6 py-2 text-sm text-white rounded-md bg-primary">{{ $updateType ? 'Update Room Type' : 'Add Room Type' }}</button>

                <x-form.search />
            </div>
            <div class="" x-cloak x-show="typeForm">
                <form wire:submit=@if ($updateType) 'updateRoomT' @else 'AddRoomType' @endif
                    class="w-full space-y-4">
                    <div
                        class="grid grid-cols-1 gap-4 overflow-y-auto text-sm md:gap-6 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                        <x-input.text label="Room Type Name" name="name" />
                        <x-input.text label="price1 (₦)" name="price1" />
                        <x-input.text label="price2 ($)" name="price2" />
                        <x-input.text label="Deposit1 (₦)" name="deposit1" />
                        <x-input.text label="Deposit2 ($)" name="deposit2" />
                        <x-input.text label="weekend price (₦)" name="weekend_price" />

                        <x-form.select name="weekend_start_day" label="Weekend Start on">
                            <option>Select Day</option>
                            @foreach (['friday', 'saturday'] as $department)
                                <option value="{{ $department }}" class="capitalize">
                                    {{ $department }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.select name="weekend_end_day" label="Weekend end on">
                            <option>Select Day</option>
                            @foreach (['sunday'] as $day)
                                <option value="{{ $day }}" class="capitalize">
                                    {{ $day }}
                                </option>
                            @endforeach
                        </x-form.select>
                        <x-input.text label="Promo price1 (₦)" name="promo_price" />
                        <x-input.text name="promo_start_day" type="date" label="Promo Start on" />

                        <x-input.text name="promo_end_day" type="date" label="Promo end on" />
                        <x-input.text label="Remarks" name="remarks" />
                        <div
                            class="p-2 space-y-2 border border-gray-300 border-dashed rounded-lg md:col-span-2 lg:col-span-3 xl:col-span-4 ">
                            <span class="text-sm">Pictures of the rooms(make sure that all the images are in the same
                                folder.)</span>
                            @error('images*')
                                <span>{{ $message }}</span>
                            @enderror
                            <div class="">
                                @if ($images)
                                    <div class="flex ">
                                        @foreach ($images as $pic)
                                            <img src="{{ $pic->temporaryUrl() }}"
                                                class="inline-block object-cover m-2 w-14 h-14">
                                        @endforeach
                                    </div>
                                @endif

                                <input type="file" id="images" multiple class="hidden"
                                    accept="image/png, image/jpg, image/gif, image/jpeg" wire:model.live="images">
                                <label for="images"
                                    class="inline-block px-6 py-4 text-4xl text-center border border-gray-300 border-dashed rounded-md cursor-pointer hover:text-primary">+</label>
                            </div>
                        </div>

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
                        <tr class="w-full text-sm font-medium bg-gray-100 rounded">
                            {{-- <th class="p-4 font-medium text-left">
                            <input type="checkbox" value="" wire:model="all" class="select-input">
                        </th> --}}
                            <th class="p-4 font-medium text-left no-wrap">No</th>
                            <th class="p-4 font-medium text-left no-wrap">Room Type Name</th>
                            <th class="p-4 font-medium text-left no-wrap">Price1 (NGN)</th>
                            <th class="p-4 font-medium text-left no-wrap">Price2 (USD)</th>
                            <th class="p-4 font-medium text-left no-wrap">Deposit1 (NGN)</th>
                            <th class="p-4 font-medium text-left no-wrap">Deposit2 (USD)</th>
                            <th class="p-4 font-medium text-left no-wrap">Weekend Price</th>
                            <th class="p-4 font-medium text-left no-wrap">Weekend Start Day</th>
                            <th class="p-4 font-medium text-left no-wrap">Weekend End Day</th>
                            <th class="p-4 font-medium text-left no-wrap">Remarks</th>
                            <th class="p-4 font-medium text-left no-wrap">Promo Price</th>
                            <th class="p-4 font-medium text-left no-wrap">Promo Start Date</th>
                            <th class="p-4 font-medium text-left no-wrap">Promo End Date</th>
                            <th class="p-4 font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roomType as $type)
                            <tr wire:key='{{ $type->id }}' class="text-sm border-b even:bg-gray-50">
                                {{-- <td class="px-4 py-2">

                            </td> --}}
                                <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2 text-sm uppercase break-keep nowrap">
                                    {{ $type->name }}
                                </td>
                                <td class="px-4 py-2">
                                    <span>{{ formatAsNaira($type->price1) }}</span>
                                </td>
                                <td class="px-4 py-2 ">
                                    {{ formatAsDollar($type->price2) }}
                                </td>
                                <td class="px-4 py-2">
                                    <span>{{ formatAsNaira($type->deposit1) }}</span>
                                </td>
                                <td class="px-4 py-2 ">
                                    {{ formatAsDollar($type->deposit2) }}
                                </td>
                                <td class="px-4 py-2 text-sm font-semibold uppercase">
                                    {{ formatAsNaira($type->weekend_price) }}
                                </td>
                                <td class="px-4 py-2 capitalize ">
                                    {{ $type->weekend_start_day }}
                                </td>
                                <td class="px-4 py-2 capitalize ">
                                    {{ $type->weekend_end_day }}
                                </td>
                                <td class="px-4 py-2 capitalize ">
                                    {{ $type->remarks }}
                                </td>
                                <td class="px-4 py-2 text-sm font-semibold uppercase">
                                    {{ formatAsNaira($type->promo_price) }}
                                </td>
                                <td class="px-4 py-2 capitalize ">
                                    {{ $type->promo_start_day }}
                                </td>
                                <td class="px-4 py-2 text-sm text-center uppercase">{{ $type->promo_end_day }}</td>
                                <td class="px-4 py-2">
                                    <p class="flex items-center justify-center space-x-2 text-sm">

                                        <button wire:click="editRoomType({{ $type->id }})"
                                            data-tooltip-target="edit" data-tooltip-placement="left"
                                            class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-600 border border-blue-600 rounded-md">
                                            <i class="mr-1 fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        <button wire:click="deleteType({{ $type->id }})"
                                            wire:confirm="All rooms and bookings associated will also be deleted. Are you sure you want to perform this action?"
                                            data-tooltip-target="delete" data-tooltip-placement="left"
                                            class="flex items-center justify-center px-2 py-1 space-x-2 text-red-600 border border-red-600 rounded-md">
                                            <i class="mr-1 fa-solid fa-times"></i> Delete
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
                        <x-tooltip id="edit" content="Edit room data" />

                        {{-- edit tooltip --}}
                        <x-tooltip id="delete" content="Change Status" />
                    </tbody>
                </table>
                <div class="px-4">
                    <p class="my-2">
                        {{ $rooms->links() }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="p-4 space-y-6 overflow-y-scroll border rounded-lg shadow-sm">
        <div class="flex items-center justify-between space-x-4 text-2xl ">
            <h1 class="font-medium">
                Rooms
            </h1>
            {{-- <a href="{{ route('configuration') }}"
                class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a> --}}
        </div>
        <div class="w-full space-y-4" x-data="{ roomForm: @entangle('roomForm').live }">
            <div class="flex items-center justify-between space-x-4 text-2xl font-medium">
                <button @click="roomForm = true" class="px-6 py-2 text-sm text-white rounded-md bg-primary">Add
                    Room</button>

                <x-form.search />
            </div>
            <div class="" x-cloak x-show="roomForm">
                <form wire:submit=@if ($updateR) 'updateRoom' @else 'AddRoom' @endif
                    class="w-full space-y-4">
                    <div
                        class="grid grid-cols-1 gap-4 overflow-y-auto text-sm md:gap-6 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                        <x-input.text label="Room Number" name="number" />
                        <x-input.text label="Capacity" name="capacity" />
                        <x-input.text label="description" name="description" />
                        <x-form.select name="bed_type" label="Bed" class="capitalize">
                            <option>Select Bed Type</option>
                            @foreach (['Twin Size', 'Double Size', 'Triple Size', 'Queen Size', 'King Size'] as $bed)
                                <option value="{{ $bed }}" class="capitalize">
                                    {{ $bed }}
                                </option>
                            @endforeach
                        </x-form.select>
                        <x-form.select name="type_id" label="Room Type/Category">
                            <option>Select Room Type</option>
                            @foreach (\App\Models\Type::select('id', 'name')->get() as $type)
                                <option value="{{ $type->id }}" class="capitalize">
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </x-form.select>
                        <x-form.select class="capitalize" name="floor_id" label="Which Floor">
                            <option>Select Floor</option>
                            @foreach (\App\Models\Floor::select('id', 'name')->get() as $floor)
                                <option value="{{ $floor->id }}" class="capitalize">
                                    {{ $floor->name }}
                                </option>
                            @endforeach
                        </x-form.select>
                        <x-form.select name="status" label="Room Status">
                            <option>Select Status</option>
                            @if ($updateR)
                                @foreach (['available', 'dirty', 'repair'] as $status)
                                    <option value="{{ $status }}" class="capitalize">
                                        {{ $status }}
                                    </option>
                                @endforeach
                            @else
                                @foreach (['available'] as $status)
                                    <option value="{{ $status }}" class="capitalize">
                                        {{ $status }}
                                    </option>
                                @endforeach
                            @endif
                        </x-form.select>

                    </div>
                    <button class="px-6 py-2 text-sm text-white rounded-md bg-primary max-w-max">Save</button>
                </form>
            </div>
        </div>

        <div id="rooms" class="w-full pb-8 overflow-y-auto">
            <div
                class="mb-4 overflow-x-auto overflow-y-scroll bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <table id="" class="w-full overflow-x-auto overflow-y-auto rounded bord">
                    <thead class="w-full overflow-x-auto rounded">
                        <tr class="w-full font-medium bg-gray-100 rounded">
                            {{-- <th class="p-4 font-medium text-left">
                            <input type="checkbox" value="" wire:model="all" class="select-input">
                        </th> --}}
                            <th class="p-4 font-medium text-left">S/N</th>
                            <th class="p-4 font-medium text-left nowrap">Room No</th>
                            <th class="p-4 font-medium text-left">Description</th>
                            <th class="p-4 font-medium text-left nowrap">Room Type</th>
                            <th class="p-4 font-medium text-left">Capacity</th>
                            <th class="p-4 font-medium text-left">Floor</th>
                            <th class="p-4 font-medium">Bed Type</th>
                            <th class="p-4 font-medium">Status</th>
                            <th class="p-4 font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rooms as $room)
                            <tr wire:key='{{ $room->id }}' class="border-b even:bg-gray-50">

                                <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2 text-sm uppercase break-keep nowrap">
                                    {{ $room->number }}
                                </td>
                                <td class="px-4 py-2">
                                    <span href="#">{{ $room->description }}</span>
                                </td>
                                <td class="px-4 py-2 ">
                                    {{ $room->type->name }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    {{ $room->capacity }}
                                </td>
                                <td class="px-4 py-2 capitalize">
                                    {{ $room->floor->name }}
                                </td>
                                <td class="px-4 py-2 ">
                                    {{ $room->bed_type }}
                                </td>
                                <td class="px-4 py-2 text-xs font-semibold text-center uppercase">
                                    <span
                                        class="py-2 rounded-full px-4 border {{ $room->status == 'available' ? 'text-black' : 'text-white' }}  {{ 'bg-' . $room->status }}">{{ $room->status }}</span>
                                </td>

                                <td class="px-4 py-2">
                                    <p class="flex items-center justify-center space-x-2 text-sm">

                                        <a href="{{ route('room.histories', $room->number) }}"
                                            wire:click="editRoom({{ $room->id }})" data-tooltip-target="history"
                                            data-tooltip-placement="left"
                                            class="flex items-center justify-center px-2 py-1 space-x-2 text-teal-600 border border-teal-600 rounded-md">
                                            </i>Histories
                                        </a>
                                        <button wire:click="editRoom({{ $room->id }})" data-tooltip-target="edit"
                                            data-tooltip-placement="left"
                                            class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-600 border border-blue-600 rounded-md">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        <button wire:click="deleteRoom({{ $room->id }})"
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
                        {{-- history --}}
                        <x-tooltip id="history" content="View Room booking Histories" />

                        {{-- edit tooltip --}}
                        <x-tooltip id="edit" content="Edit room data" />

                        {{-- edit tooltip --}}
                        <x-tooltip id="delete" content="Change Status" />
                    </tbody>
                </table>
                <div class="px-4">
                    <p class="my-2">
                        {{ $rooms->links(data: ['scrollTo' => '#rooms']) }}
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
