<div class="w-full p-4 space-y-6 overflow-y-scroll bg-white rounded-lg bb-20" x-data="{ active: 'hotel' }">
    <div class="flex items-center justify-between space-x-4 text-2xl ">
        <h1 class="font-medium">
            Customers
        </h1>
        <a href="{{ route('configuration') }}" class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a>
    </div>

    <div class="w-full space-y-4" x-data="{ customerForm: @entangle('customerForm').live }">
        <div class="flex items-center justify-between space-x-4 text-2xl font-medium">
            <button @click="customerForm = true"
                class="px-6 py-2 text-sm text-white rounded-md bg-primary">{{ $updateC ? 'Update Customer' : 'Add Customer' }}</button>

            <x-form.search />
        </div>
        <div class="" x-cloak x-show="customerForm">
            <form wire:submit=@if ($updateC) 'updateCustomer' @else 'AddCustomer' @endif
                class="w-full space-y-4">
                <div
                    class="grid grid-cols-1 gap-4 overflow-y-auto text-sm md:gap-6 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                    <x-input.text label="First Name" name="firstname" />
                    <x-input.text label="Last Name" name="lastname" />
                    <x-input.text label="Telephone" name="phone" />
                    <x-input.text label="Email Address" name="email" />
                    <x-form.select name="id_type" label="Customer's ID">
                        <option>Select ID Card Type</option>
                        @foreach (['International Passport', "Driver's License", 'NIN Card / Slip', 'Work ID Card', 'School ID Card', 'Bank Card', 'Birth Certificate', 'Other ID'] as $card)
                            <option value="{{ $card }}">
                                {{ $card }}
                            </option>
                        @endforeach
                    </x-form.select>
                    <x-input.text label="ID Card Number" name="id_number" />

                    <x-form.select name="gender" label="Customer's Gender" class="capitalize">
                        <option>Select Gender</option>
                        @foreach (['male', 'female'] as $sex)
                            <option value="{{ $sex }}" class="capitalize">
                                {{ $sex }}
                            </option>
                        @endforeach
                    </x-form.select>
                    <x-form.select name="state_id" label="State">
                        <option>Select State</option>
                        @foreach (\Nnjeim\World\Models\State::select('id', 'name')->where('country_id', 160)->get() as $state)
                            <option value="{{ $state->id }}">
                                {{ $state->name }}
                            </option>
                        @endforeach
                    </x-form.select>
                    <x-form.select name="city_id" label="City/Town">
                        <option>Select City</option>
                        @if ($state_id)
                            @foreach (\Nnjeim\World\Models\City::select('id', 'name')->where('state_id', $state_id)->get() as $city)
                                <option value="{{ $city->id }}" {{ $city_id == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        @else
                            @php
                                $city = \Nnjeim\World\Models\City::find($city_id);
                            @endphp
                            <option value="{{ $city_id }}">
                                {{ $city?->name }}
                            </option>
                        @endif
                    </x-form.select>

                    <x-input.text label="Address" name="address" />
                    <x-form.select name="business_id" label="If Credit Customer, select Company">
                        <option>Select Company</option>
                        @foreach (\App\Models\Business::select('id', 'name')->get() as $business)
                            <option value="{{ $business->id }}">
                                {{ $business->name }}
                            </option>
                        @endforeach
                    </x-form.select>
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
                        <th class="p-4 font-medium text-left">No</th>
                        <th class="p-4 font-medium text-left nowrap">Name</th>
                        <th class="p-4 font-medium text-left">Phone</th>
                        <th class="p-4 font-medium text-left">Email</th>
                        <th class="p-4 font-medium text-left">ID Card Type</th>
                        <th class="p-4 font-medium text-left">ID Number</th>
                        <th class="p-4 font-medium text-left">City</th>
                        <th class="p-4 font-medium text-left nowrap">Last Checked_In</th>
                        <th class="p-4 font-medium text-left nowrap">Credit Customer</th>
                        <th class="p-4 font-medium text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr wire:key='{{ $customer->id }}' class="border-b even:bg-gray-50">
                            {{-- <td class="px-4 py-2">

                            </td> --}}
                            <td class="px-4 py-2">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 text-sm capitalize break-keep nowrap">
                                {{ $customer->firstname . ' ' . $customer->lastname }}
                            </td>

                            <td class="px-4 py-2 ">
                                {{ $customer->phone }}
                            </td>
                            <td class="px-4 py-2 text-sm">
                                {{ $customer->email }}
                            </td>
                            <td class="px-4 py-2 uppercase">
                                {{ $customer->id_type }}
                            </td>
                            <td class="px-4 py-2 text-sm uppercase">
                                {{ $customer->id_number }}
                            </td>
                            <td class="px-4 py-2 text-sm">
                                {{ $customer->city?->name }}
                            </td>
                            <td class="px-4 py-2 text-sm">
                                {{ $customer->created_at?->format('d M, Y') }}</td>
                            <td class="px-4 py-2 text-sm">
                                {{ $customer->company?->name ?? 'No' }}
                            </td>
                            {{-- <td class="px-4 py-2 text-sm">
                                <a href="{{ route('customer.histories', $customer->id) }}"
                                    data-tooltip-target="history" data-tooltip-placement="left"
                                    class="flex items-center justify-center px-2 py-1 space-x-2 border rounded-md text-primary">
                                    histories
                                </a>
                            </td> --}}

                            <td class="px-4 py-2">
                                <p class="flex items-center justify-start space-x-2 text-sm">
                                    <button wire:click="editCustomer({{ $customer->id }})" data-tooltip-target="edit"
                                        data-tooltip-placement="left"
                                        class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-600 border border-blue-600 rounded-md">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <a href="{{ route('customer.histories', $customer->id) }}"
                                        data-tooltip-target="history" data-tooltip-placement="left"
                                        class="flex items-center justify-center px-2 py-1 space-x-2 border rounded-md text-primary">
                                        histories
                                    </a>
                                    {{-- <button wire:click="deleteCustomer({{ $customer->id }})"
                                        wire:confirm="Customer and all associated histories will be deleted as well. Are you sure you want to perform this action?"
                                        data-tooltip-target="delete" data-tooltip-placement="left"
                                        class="flex items-center justify-center px-2 py-1 space-x-2 text-red-600 border border-red-600 rounded-md">
                                        Delete
                                    </button> --}}
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
                    {{ $customers->links() }}
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
