<div class="w-full p-4 space-y-4 overflow-y-scroll bg-white rounded-lg" x-data="{ active: 'hotel' }">
    <div class="flex space-y-4 md:justify-between">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center space-x-4 font-medium">
                <span class="text-2xl">Hotel Setup</span>
                @if ($hotel->background_image)
                    <img src="{{ asset('storage/' . $hotel->background_image) }}" class="h-12" alt="{{ $hotel->name }}">
                @endif
            </div>
            <a href="{{ route('configuration') }}"
                class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a>
        </div>
        <div>
            @session('success')
                <div class="p-2 space-x-3 text-green-500 bg-white rounded shadow mx-w-max">
                    <span><i class="fa-regular fa-circle-check"></i></span>
                    <span>{{ $value }}</span>
                </div>
            @endsession
        </div>
    </div>
    <div class="flex flex-col w-full border-b border-black divide-x divide-gray-500 shadow md:flex-row">
        <button :class="active == 'hotel' ? 'bg-gray-100' : 'bg-gray-400 text-gray-100'" @click="active = 'hotel'"
            class="px-6 py-2 text-sm">Hotel Information</button>
        <button :class="active == 'amenity' ? 'bg-gray-100' : 'bg-gray-400 text-gray-100'" @click="active = 'amenity'"
            class="px-6 py-2 text-sm">Amenities</button>
        <button :class="active == 'revenue' ? 'bg-gray-100' : 'bg-gray-400 text-gray-100'" @click="active = 'revenue'"
            class="px-6 py-2 text-sm">Revenue Source(s)</button>
        <button :class="active == 'department' ? 'bg-gray-100' : 'bg-gray-400 text-gray-100'"
            @click="active = 'department'" class="px-6 py-2 text-sm">Department</button>
        <button :class="active == 'expenditure' ? 'bg-gray-100' : 'bg-gray-400 text-gray-100'"
            @click="active = 'expenditure'" class="px-6 py-2 text-sm">Expenditure Head</button>
        <button :class="active == 'password' ? 'bg-gray-100' : 'bg-gray-400 text-gray-100'" @click="active = 'password'"
            class="px-6 py-2 text-sm">Update Password</button>
        <button :class="active == 'role' ? 'bg-gray-100' : 'bg-gray-400 text-gray-100'" @click="active = 'role'"
            class="px-6 py-2 text-sm">User Roles</button>
    </div>

    <div x-show="active == 'hotel'" class="w-full overflow-y-auto">
        <form wire:submit='updateHotel' class="grid w-full gap-6 pb-16">
            <div class="grid grid-cols-1 gap-4 overflow-y-auto text-sm md:gap-6 md:grid-cols-2 lg:grid-cols-3">
                <x-input.text label="Hotel Name" name="name" class="text-gray-500 bg-gray-100" readonly />
                <x-input.text label="Telephone" name="telephone" class="text-gray-500 bg-gray-100" readonly />
                <x-input.text label="Email Address" name="email" class="text-gray-500 bg-gray-100" readonly />

                <x-form.select name="country_id" label="Country">
                    <option selected>Select Country</option>
                    @foreach (\Nnjeim\World\Models\Country::whereIn('id', [160, 236])->select('id', 'name')->get() as $country)
                        <option value="{{ $country->id }}">
                            {{ $country->name }}
                        </option>
                    @endforeach
                </x-form.select>
                <x-form.select name="state_id" label="State">
                    <option selected>Select State</option>
                    @foreach (\Nnjeim\World\Models\State::select('id', 'name')->where('country_id', $country_id)->get() as $state)
                        <option value="{{ $state->id }}">
                            {{ $state->name }}
                        </option>
                    @endforeach
                </x-form.select>
                <x-form.select name="city_id" label="City/Town">
                    <option selected>Select State</option>
                    @if ($state_id)
                        @foreach (\Nnjeim\World\Models\City::select('id', 'name')->where('state_id', $state_id)->get() as $city)
                            <option value="{{ $city->id }}">
                                {{ $city->name }}
                            </option>
                        @endforeach
                    @else
                        @php
                            $city = \Nnjeim\World\Models\City::find($city_id);
                        @endphp
                        <option value="{{ $city->id }}">
                            {{ $city->name }}
                        </option>
                    @endif
                </x-form.select>
                <x-input.text label="Address" name="address" />
                <x-input.text label="Zip/Poster Code" name="zip_code" />
                <x-form.select name="primary_currency" label="Primary Currency">
                    @php
                        $cur = \Nnjeim\World\Models\Currency::find(160);
                    @endphp
                    <option selected>Select Currency</option>
                    <option selected>{{ $cur->symbol . ' (' . $cur->country->name . ')' }}</option>
                    @foreach (\Nnjeim\World\Models\Currency::all() as $currency)
                        <option value="{{ $currency->id }}">
                            {{ $currency->symbol . ' (' . $currency->country->name . ')' }}
                        </option>
                    @endforeach
                </x-form.select>
                <x-form.select name="secondary_currency" label="Secondary Currency">
                    @php
                        $sec_cur = \Nnjeim\World\Models\Currency::find(236);
                    @endphp
                    <option selected>Select Currency</option>
                    <option selected>{{ $sec_cur->symbol . ' (' . $sec_cur->country->name . ')' }}</option>
                    @foreach (\Nnjeim\World\Models\Currency::all() as $currency)
                        <option value="{{ $currency->id }}">
                            {{ $currency->symbol . ' (' . $currency->country->name . ')' }}
                        </option>
                    @endforeach
                </x-form.select>
                <div class="w-full space-y-1 text-sm">
                    <label for="" class="capitalize ">Checkout Time
                    </label>
                    <div class="relative w-full">
                        <input type="time" wire:model.live='checked_out'
                            class="w-full p-2 border rounded-md shadow-sm focus:ring-primary focus:border-primary">
                    </div>
                    @error('')
                        <span class="text-red-600">{{ $message }}</span>
                    @enderror
                </div>
                <x-input.text label="Service Charge(%)" name="service_charge" />
                {{-- <x-input.text label="VAT(%)" name="vat" /> --}}

                <x-input.text label="Refund Surcharge(%)" name="surcharge" />
                <div class="flex items-center space-x-2 cursor-pointer">
                    <label for="vat">Add VAT to Room Rate?</label>
                    <input id="vat" type="checkbox" value="{{ $vat }}" wire:model.live="vat"
                        {{ $vat ? 'checked' : '' }}
                        class="p-1 border rounded-sm shadow-sm focus:ring-primary text-primary">
                </div>

                <x-input.text label="Hotel Background Image" name="background_image" type="file" class="px-0" />
                {{-- <x-input.text label="Logo" name="logo" type="file" class="px-0" /> --}}
            </div>
            {{-- <div>
                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <span class="text-red-600">{{ $error }}</span>
            @endforeach
            @endif
    </div> --}}
            <button class="px-6 py-2 text-sm text-white rounded-md bg-primary max-w-max">Submit</button>
        </form>
    </div>
    <div x-show="active == 'amenity'" class="w-full pb-16 mt-4 overflow-y-auto">
        <div x-data="{ open: @entangle('form').live }" class="w-full space-y-3">
            <div class="flex p-2 space-x-4 bg-white rounded-sm shadow">
                <button @click="open = true"
                    class="px-2 py-1 space-x-2 text-sm text-white border rounded bg-primary hover:bg-primary-700 tt"><span>Add
                        Amenity</span></button>
                @if (count($checked))
                    <button wire:click='deleteMultipleRevenue'
                        wire:confirm='You are deleting mutiple records at ones, other data like finace will be also deleted, Are you sure?'
                        class="px-2 py-1 space-x-1 text-sm text-white bg-red-600 border rounded hover:bg-red-700 tt">
                        <i class="fa-regular fa-trash-can"></i><span> Delete</span></button>
                    <button
                        class="px-2 py-1 space-x-2 text-sm text-white bg-blue-500 border rounded hover:bg-blue-700 tt">
                        <i class="fa-solid fa-print"></i>
                        <span>Export</span>
                    </button>
                @endif
            </div>
            <form wire:submit=@if ($updateA) "updateAmenity" @else "saveAmenity" @endif
                x-show="open" class="flex items-center w-full max-w-sm space-x-4">
                <input type="text" wire:model.live='amen'
                    class="px-2 py-1 border rounded focus:ring-0 focus:border-primary">
                <button class="px-3 py-1 text-white bg-green-500 rounded">{{ $updateA ? 'Update' : 'Save' }}</button>
                <button @click="open = false" type="button"
                    class="px-2 py-1 text-white bg-gray-500 rounded">Close</button>
            </form>
        </div>
        <div class="w-full py-4 text-sm">
            <table class="w-full max-w-sm border border-collapse">
                <thead>
                    <tr class="p-2 bg-gray-200 border-b">
                        <th class="p-2">
                            <input type="checkbox" wire:model.live="checked" class="select-input">
                        </th>
                        <th class="w-3/4 p-2 text-left">Hotel Amenities</th>
                        <th class="p-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($amenities as $amenity)
                        <tr class="even:bg-primary-50" wire:key='{{ $amenity->id }}'>
                            <td class="p-2 text-center border">
                                <input type="checkbox" value="{{ $amenity->id }}" wire:model.live="checked"
                                    class="select-input">
                            </td>
                            <td class="p-2 capitalize border">{{ $amenity->name }}</td>
                            <td class="flex items-center p-2 space-x-2 border">
                                <button type="button" wire:click="editAmenity({{ $amenity->id }})"
                                    class="px-2 py-1 text-sm text-blue-600 border border-blue-600 rounded-md">
                                    <span> <i class="fa-solid fa-pencil"></i> </span>
                                </button>
                                <button
                                    wire:confirm='All associated data like finance will also be deleted alonside, Are you Sure?'
                                    type="button" wire:click="deleteAmenity({{ $amenity->id }})"
                                    class="px-2 py-1 text-sm text-red-600 border border-red-600 rounded-md">
                                    <span> <i class="fa-solid fa-trash"></i> </span>
                                    {{-- <span>
                                        Delete</span> --}}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div x-show="active == 'revenue'" class="w-full pb-16 mt-4 overflow-y-auto">
        <div x-data="{ open: @entangle('form').live }" class="w-full space-y-3">
            <div class="flex p-2 space-x-4 bg-white rounded-sm shadow">
                <button @click="open = true"
                    class="px-2 py-1 space-x-2 text-sm text-white border rounded bg-primary hover:bg-primary-700 tt"><span>Add
                        Revenue</span></button>
                @if (count($checked))
                    <button wire:click='deleteMultipleRevenue'
                        wire:confirm='You are deleting mutiple records at ones, other data like finace will be also deleted, Are you sure?'
                        class="px-2 py-1 space-x-1 text-sm text-white bg-red-600 border rounded hover:bg-red-700 tt">
                        <i class="fa-regular fa-trash-can"></i><span> Delete</span></button>
                    <button
                        class="px-2 py-1 space-x-2 text-sm text-white bg-blue-500 border rounded hover:bg-blue-700 tt">
                        <i class="fa-solid fa-print"></i>
                        <span>Export</span>
                    </button>
                @endif
            </div>
            <form wire:submit=@if ($updateR) "updateRevenue" @else "saveRevenue" @endif
                x-show="open" class="flex items-center w-full max-w-sm space-x-4">
                <input type="text" wire:model.live='source'
                    class="px-2 py-1 border rounded focus:ring-0 focus:border-primary">
                <button class="px-3 py-1 text-white bg-green-500 rounded">{{ $updateR ? 'Update' : 'Save' }}</button>
                <button @click="open = false" type="button"
                    class="px-2 py-1 text-white bg-gray-500 rounded">Close</button>
            </form>
        </div>
        <div class="w-full py-4 text-sm">
            <table class="w-full max-w-sm border border-collapse">
                <thead>
                    <tr class="p-2 bg-gray-200 border-b">
                        <th class="p-2">
                            <input type="checkbox" wire:model.live="checked" class="select-input">
                        </th>
                        <th class="w-3/4 p-2 text-left">Revenue Source</th>
                        <th class="p-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($revenues as $revenue)
                        <tr class="even:bg-primary-50" wire:key='{{ $revenue->id }}'>
                            <td class="p-2 text-center border">
                                <input type="checkbox" value="{{ $revenue->id }}" wire:model.live="checked"
                                    class="select-input">
                            </td>
                            <td class="p-2 capitalize border">{{ $revenue->source }}</td>
                            <td class="flex items-center p-2 space-x-2 border">
                                <button type="button" wire:click="editRevenue({{ $revenue->id }})"
                                    class="px-2 py-1 text-sm text-blue-600 border border-blue-600 rounded-md">
                                    <span> <i class="fa-solid fa-pencil"></i> </span>
                                </button>
                                <button
                                    wire:confirm='All associated data like finance will also be deleted alonside, Are you Sure?'
                                    type="button" wire:click="deleteRevenue({{ $revenue->id }})"
                                    class="px-2 py-1 text-sm text-red-600 border border-red-600 rounded-md">
                                    <span> <i class="fa-solid fa-trash"></i> </span>
                                    {{-- <span>
                                        Delete</span> --}}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="py-2">

            </div>
        </div>
    </div>
    <div x-show="active == 'department'" class="w-full pb-16 mt-4 overflow-y-auto">
        <div x-data="{ open: @entangle('form').live }" class="w-full space-y-3">
            <div class="flex p-2 space-x-4 bg-white rounded-sm shadow">
                <button @click="$wire.addDepartment"
                    class="px-2 py-1 space-x-2 text-sm text-white border rounded bg-primary hover:bg-primary-700 tt"><span>Add
                        Department</span></button>
                @if (count($checked))
                    <button wire:click='deleteMultipleDepartment'
                        wire:confirm='You are deleting mutiple records at ones, All the users associated will have no depart if you delete it, Are you sure?'
                        class="px-2 py-1 space-x-1 text-sm text-white bg-red-600 border rounded hover:bg-red-700 tt">
                        <i class="fa-regular fa-trash-can"></i><span> Delete</span></button>
                    <button
                        class="px-2 py-1 space-x-2 text-sm text-white bg-blue-500 border rounded hover:bg-blue-700 tt">
                        <i class="fa-solid fa-print"></i>
                        <span>Export</span>
                    </button>
                @endif
            </div>
            <form wire:submit=@if ($updateD) 'updateDepartment' @else 'saveDepartment' @endif
                x-show="open" class="flex items-center w-full max-w-sm space-x-4">
                <input type="text" wire:model.live='departmentName'
                    class="px-2 py-1 border rounded focus:ring-0 focus:border-primary">
                <button class="px-3 py-1 text-white bg-green-500 rounded">{{ $updateD ? 'Update' : 'Save' }}</button>
                <button @click="open = false" type="button"
                    class="px-2 py-1 text-white bg-gray-500 rounded">Close</button>
            </form>
        </div>
        <div class="w-full py-4 text-sm">
            <table class="w-full max-w-sm border border-collapse">
                <thead>
                    <tr class="p-2 bg-gray-200 border-b">
                        <th class="p-2">
                            <input type="checkbox" wire:model.live="checked" class="select-input">
                        </th>
                        <th class="w-3/4 p-2 text-left">Department Name</th>
                        <th class="p-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($departments as $department)
                        <tr class="even:bg-primary-50" wire:key='{{ $department->id }}'>
                            <td class="p-2 text-center border">
                                <input type="checkbox" value="{{ $department->id }}" wire:model.live="checked"
                                    class="select-input">
                            </td>
                            <td class="p-2 capitalize border">{{ $department->name }}</td>
                            <td class="flex items-center p-2 space-x-2 border">
                                <button type="button" wire:click="editDepartment({{ $department->id }})"
                                    class="px-2 py-1 text-sm text-blue-600 border border-blue-600 rounded-md">
                                    <span> <i class="fa-solid fa-pencil"></i> </span>
                                </button>
                                <button type="button"
                                    wire:confirm='All the users associated will have no department if you delete it, Are you sure?'
                                    wire:click="deleteDepartment({{ $department->id }})"
                                    class="px-2 py-1 text-sm text-red-600 border border-red-600 rounded-md">
                                    <span> <i class="fa-solid fa-trash"></i> </span>
                                    {{-- <span>
                                        Delete</span> --}}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="py-2">

            </div>
        </div>
    </div>
    <div x-show="active == 'expenditure'" class="w-full pb-16 mt-4 overflow-y-auto">
        <div x-data="{ open: @entangle('form').live }" class="w-full space-y-3">
            <div class="flex p-2 space-x-4 bg-white rounded-sm shadow">
                <button @click="open = true"
                    class="px-2 py-1 space-x-2 text-sm text-white capitalize border rounded bg-primary hover:bg-primary-700 tt"><span>Add
                        expenditure</span></button>
                @if (count($checked))
                    <button wire:click='deleteMultipleExpenditure'
                        wire:confirm='You are deleting mutiple records at ones, other data like finace will be also deleted, Are you sure?'
                        class="px-2 py-1 space-x-1 text-sm text-white bg-red-600 border rounded hover:bg-red-700 tt">
                        <i class="fa-regular fa-trash-can"></i><span> Delete</span></button>
                    <button
                        class="px-2 py-1 space-x-2 text-sm text-white bg-blue-500 border rounded hover:bg-blue-700 tt">
                        <i class="fa-solid fa-print"></i>
                        <span>Export</span>
                    </button>
                @endif
            </div>
            <form wire:submit=@if ($updateE) "updateExpenditure" @else "saveExpenditure" @endif
                x-show="open" class="flex items-center w-full max-w-sm space-x-4">
                <input type="text" wire:model.live='source'
                    class="px-2 py-1 border rounded focus:ring-0 focus:border-primary">
                <button class="px-3 py-1 text-white bg-green-500 rounded">{{ $updateE ? 'Update' : 'Save' }}</button>
                <button @click="open = false" type="button"
                    class="px-2 py-1 text-white bg-gray-500 rounded">Close</button>
            </form>
        </div>
        <div class="w-full py-4 text-sm">
            <table class="w-full max-w-sm border border-collapse">
                <thead>
                    <tr class="p-2 bg-gray-200 border-b">
                        <th class="p-2">
                            <input type="checkbox" wire:model.live="checked" class="select-input">
                        </th>
                        <th class="w-3/4 p-2 text-left">Description</th>
                        <th class="p-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expenditures as $expenditure)
                        <tr class="even:bg-primary-50" wire:key='{{ $expenditure->id }}'>
                            <td class="p-2 text-center border">
                                <input type="checkbox" value="{{ $expenditure->id }}" wire:model.live="checked"
                                    class="select-input">
                            </td>
                            <td class="p-2 capitalize border">{{ $expenditure->source }}</td>
                            <td class="flex items-center p-2 space-x-2 border">
                                <button type="button" wire:click="editExpenditure({{ $expenditure->id }})"
                                    class="px-2 py-1 text-sm text-blue-600 border border-blue-600 rounded-md">
                                    <span> <i class="fa-solid fa-pencil"></i> </span>
                                </button>
                                <button type="button"
                                    wire:confirm='All associated data like finance will also be deleted alonside, Are you Sure?'
                                    wire wire:click="deleteExpenditure({{ $expenditure->id }})"
                                    class="px-2 py-1 text-sm text-red-600 border border-red-600 rounded-md">
                                    <span> <i class="fa-solid fa-trash"></i> </span>
                                    {{-- <span>
                                        Delete</span> --}}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="py-2">

            </div>
        </div>
    </div>
    <div x-show="active == 'password'" class="w-full pb-16 mt-4 overflow-y-auto">
        <div class="w-full space-y-3">
            {{-- <div class="flex p-2 space-x-4 bg-white rounded-sm shadow">
                <button @click="$wire.addDepartment"
                    class="px-2 py-1 space-x-2 text-sm text-white border rounded bg-primary hover:bg-primary-700 tt">
                    <span>Update
                        Password</span>
                </button>

            </div> --}}
            <form wire:submit="updatePassword" x-show="open" class="flex flex-col w-full max-w-sm space-y-3">
                <div class="">
                    <label for="current_password" class="mb-2 text-sm">Current Password</label><br>
                    <input type="password" wire:model='current_password'
                        class="px-2 py-1 border rounded focus:ring-0 focus:border-primary"> <br>
                    @error('current_password')
                        <small class="text-red-600">{{ $message }}</small>
                    @enderror
                </div>
                <div class="">
                    <label for="password" class="mb-2 text-sm">New Password</label><br>
                    <input type="password" wire:model='password'
                        class="px-2 py-1 border rounded focus:ring-0 focus:border-primary"> <br>
                    @error('password')
                        <small class="text-red-600">{{ $message }}</small>
                    @enderror
                </div>
                <div class="">
                    <label for="current_password" class="mb-2 text-sm">Password Confirmation</label><br>
                    <input type="password" wire:model='password_confirmation'
                        class="px-2 py-1 border rounded focus:ring-0 focus:border-primary">
                </div>
                <button class="px-3 py-1 text-white bg-green-500 rounded max-w-max">Update</button>
            </form>
        </div>
    </div>
    <div x-show="active == 'role'" class="w-full pb-16 mt-4 overflow-y-auto">
        <div class="w-full">

        </div>
        <div class="w-full py-4 text-sm">
            <table class="w-full max-w-sm border border-collapse">
                <thead>
                    <tr class="p-2 bg-gray-200 border-b">
                        {{-- <th class="p-2">
                            <input type="checkbox" wire:model.live="checked" class="select-input">
                        </th> --}}
                        <th class="w-3/4 p-2 text-left">Role Name</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr class="even:bg-primary-50" wire:key='{{ $role->id }}'>
                            {{-- <td class="p-2 text-center border">
                                <input type="checkbox" value="{{ $role->id }}" wire:model.live="checked"
                    class="select-input">
                    </td> --}}
                            <td class="p-2 capitalize border">{{ str_replace('-', ' ', $role->name) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="py-2">

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
