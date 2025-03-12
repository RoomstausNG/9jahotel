<div class="w-full p-4 pb-20 space-y-6 overflow-y-scroll bg-white rounded-lg">
    <div class="flex items-center justify-between space-x-4 text-2xl ">
        <h1 class="font-bold">
            <span class="text-primary"> {{ $user->firstname . ' ' . $user->lastname }}</span> Profile
        </h1>
        <a href="{{ route('configuration.users') }}" class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back
            to users</a>
    </div>
    {{-- <div class="w-full">
        <div class="flex items-center justify-between space-x-4 text-2xl font-medium">
            <button class="px-6 py-2 text-sm text-white rounded-md bg-primary">Add User</button>

            <x-form.search />
        </div>
    </div> --}}

    <div class="w-full pb-8 overflow-y-auto">
        <form wire:submit='updateProfile' class="grid w-full gap-6 pb-16">
            <div
                class="grid grid-cols-1 gap-4 overflow-y-auto text-sm md:gap-6 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">

                <x-input.text label="First Name" name="firstname" />
                <x-input.text label="Last Name" name="lastname" />
                <x-input.text label="Username" name="username" disabled
                    class="font-medium text-gray-500 bg-gray-100 border-gray-100 cursor-not-allowed" />
                <x-input.text label="Email Address" name="email" disabled
                    class="font-medium text-gray-500 bg-gray-100 border-gray-100 cursor-not-allowed" />
                <x-input.text label="Telephone" name="phone" />
                <x-form.select name="country_id" label="Country">
                    <option selected>Select Country</option>
                    @foreach (\Nnjeim\World\Models\Country::where('id', 160)->select('id', 'name')->get() as $country)
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
                    <option selected>Select City</option>
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
                        <option value="{{ $user->city_id }}">
                            {{ $user->city?->name }}
                        </option>
                    @endif
                </x-form.select>
                <x-input.text label="Address" name="address" />

                <x-form.select name="current_role" label="Role" class="capitalize">
                    <option>Select Role</option>
                    @foreach (['admin', 'manager', 'frontdesk', 'frontdesk-manager', 'sales-staff', 'sales-manager'] as $role)
                        <option value="{{ $role }}" class="capitalize">
                            {{ str_replace('-', ' ', $role) }}
                        </option>
                    @endforeach
                </x-form.select>
                <x-form.select name="department_id" label="Department">
                    <option>Select Department</option>
                    @foreach (\App\Models\Department::select('id', 'name')->get() as $department)
                        <option value="{{ $department->id }}">
                            {{ $department->name }}
                        </option>
                    @endforeach
                </x-form.select>
                <x-form.select name="gender" label="Gender" class="capitalize">
                    <option selected>Your Sex?</option>
                    @foreach (['male', 'female'] as $sex)
                        <option value="{{ $sex }}">
                            {{ $sex }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-input.text label="Designation" name="designation" />
                <div class="items-center p-2 space-x-2">
                    <label for="can_login">Account Active</label>
                    <input type="checkbox" wire:model.live='can_login' {{ $can_login ? 'checked' : '' }}
                        value="{{ $can_login }}" id="can_login" class="p-2 text-green-600 rounded">
                </div>
                <x-input.text label="Date of employment" type="date" name="date_of_employment" />
                <x-input.text label="Account Number" name="account_number" />
                <x-input.text label="Bank Name" name="bank_name" />
                <x-input.text label="Account Name" name="account_name" />
                <x-input.text label="Salary" name="gross_annual_salary" />
                <x-input.text label="PAYE" name="payee" />
                <x-input.text label="Special Bonus" name="special_bonus" />
                <x-input.text label="health Allowance" name="health_insurance" />
                <x-input.text label="housing Allowance" name="housing_insurance" />
                <x-input.text label="transport Allowance" name="transport_insurance" />
                <x-input.text label="overtime Allowance" name="overtime_insurance" />
                <x-input.text label="special duty Allowance" name="special_duty_insurance" />
                <x-input.text label="other deductions" name="other_deductions" />
                <x-input.text label="surcharges" name="surcharges" />
                <div class="space-y-1">
                    <label for="avatar">Photograph</label>
                    <input type="file" wire:model.live='signature' class="w-full border rounded-md shadow-sm">
                    @error('signature')
                        <span class="text-red-600">Invalid file format</span>
                    @enderror
                </div>
            </div>
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <span class="text-sm text-red-600">{{ $error }}</span>
                @endforeach
            @endif
            <button class="px-6 py-2 text-sm text-white rounded-md bg-primary max-w-max">Update Profile</button>
        </form>
    </div>

    <div
        class="p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm 2xl:col-span-2 dark:border-gray-700 sm:p-6 dark:bg-gray-900">
        <h3 class="mb-4 text-xl font-semibold dark:text-white">Password information</h3>
        <form wire:submit="updatePassword">
            <div class="grid grid-cols-6 gap-6">
                {{-- <div class="col-span-6 md:w-1/2">
                    <x-input.password name="current_password" label="Current Password" />
                </div> --}}
                <div class="col-span-6 md:w-1/2">
                    <x-input.password name="password" label="New Password" />
                </div>
                <div class="col-span-6 md:w-1/2">
                    <x-input.password name="password_confirmation" label="Repeat Password" />
                </div>
                <div class="col-span-6 sm:col-full">
                    <button
                        class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
                        type="submit">Update Password</button>
                </div>
            </div>
        </form>
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
