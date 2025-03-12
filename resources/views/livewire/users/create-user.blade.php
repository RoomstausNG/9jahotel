<div class="w-full p-4 pb-20 space-y-6 overflow-y-scroll bg-white rounded-lg">
    <div class="flex items-center justify-between space-x-4 text-2xl ">
        <h1 class="font-bold">
            Add Staff
        </h1>
        <a href="{{ route('configuration.users') }}" class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back
            to users</a>
    </div>
    <div class="w-full pb-8 overflow-y-auto">
        <form wire:submit='addUser' class="grid w-full gap-6 pb-16">
            <div
                class="grid grid-cols-1 gap-4 p-4 overflow-y-auto text-sm border rounded-lg md:gap-6 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">

                <x-input.text label="First Name" name="firstname" />
                <x-input.text label="Last Name" name="lastname" />
                <x-input.text label="Email Address" name="email" />
                <x-input.text label="Telephone" name="phone" />
                <x-form.select name="country_id" label="Country">
                    <option selected>Select Country</option>
                    @foreach (\Nnjeim\World\Models\Country::where('id', 160)->get() as $country)
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
                    @foreach (\Nnjeim\World\Models\City::select('id', 'name')->where('state_id', $state_id)->get() as $city)
                        <option value="{{ $city->id }}">
                            {{ $city->name }}
                        </option>
                    @endforeach
                </x-form.select>
                <x-input.text label="Address" name="address" />
                <x-form.select name="current_role" label="Role" class="capitalize">
                    <option>Select Role</option>
                    @foreach (['admin', 'manager', 'frontdesk', 'frontdesk-manager', 'sales-staff', 'sales-manager'] as $role)
                        @if (auth()->user()->current_role != 'super-admin' && $role == 'admin')
                            @continue
                        @endif
                        <option value="{{ $role }}" class="capitalize">
                            {{ str_replace('-', ' ', $role) }}
                        </option>
                    @endforeach
                </x-form.select>
                <x-form.select name="department_id" label="Department">
                    @foreach (\App\Models\Department::select('id', 'name')->get() as $department)
                        <option value="{{ $department->id }}">
                            {{ $department->name }}
                        </option>
                    @endforeach
                </x-form.select>
                <x-form.select name="gender" label="Gender">
                    <option selected>Your Sex?</option>
                    @foreach (['Male', 'Female'] as $sex)
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
                <x-input.text label="special duty Allowance" name="special_duty_insurance" />
                <x-input.text label="Overtime Allowance" name="overtime_insurance" />
                <x-input.text label="other deductions" name="other_deductions" />
                <x-input.text label="surcharges" name="surcharges" />
                <div class="space-y-1">
                    <label for="avatar">Photograph</label>
                    <input type="file" wire:model='signature' class="w-full border rounded-md shadow-sm">
                    @error('signature')
                        <span class="text-red-600">Invalid file format</span>
                    @enderror
                </div>
                <div class="">
                    <x-input.password name="password" label="Password" />
                </div>
                <div class="">
                    <x-input.password name="password_confirmation" label="Repeat Password" />
                </div>
            </div>

            <button class="px-6 py-2 text-sm text-white rounded-md bg-primary max-w-max">Save</button>
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
