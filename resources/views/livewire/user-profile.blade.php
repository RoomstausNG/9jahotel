{{-- @filamentStyles --}}
<div class="container py-8">
    @if (session()->has('success'))
        <div class="p-2 mb-4 text-green-700 bg-green-100 rounded">
            {{ session('success') }}
        </div>
    @endif
    <div class="">
        {{ $firstname }} {{ $lastname }}
        <input type="text" wire:model="firstname" name="firstname" />
    </div>
    <form wire:submit.prevent="updateProfile" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input.text label="First Name" wire:model="firstname" name="firstname" />
            <x-input.text label="Last Name" wire:model="lastname" name="lastname" />
            <x-form.select label="Gender" wire:model="gender" name="gender">
                @foreach (['male', 'female'] as $gender)
                    <option value="{{ $gender }}">{{ ucfirst($gender) }}</option>
                @endforeach
            </x-form.select>
            <x-input.text label="Date of Birth" type="date" wire:model="dob" name="dob" />
            <x-input.text label="Address" wire:model="address" name="address" />
            <x-input.text label="Phone" wire:model="phone" name="phone" />
            <x-input.text label="ID Type" wire:model="id_type" name="id_type" />
            <x-input.text label="ID Number" wire:model="id_number" name="id_number" />
            <x-input.text label="Email" type="email" wire:model="email" name="email" />
            <x-input.text label="City ID" wire:model="city_id" name="city_id" />
        </div>
        <button type="submit" class="mt-4 px-4 py-2 bg-primary-600 text-white rounded">Update Profile</button>
    </form>

    <form wire:submit.prevent="updateAvatar" class="mt-8" enctype="multipart/form-data">
        <div class="flex flex-col items-center gap-4">
            <div class="relative w-32 h-32">
                @if ($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" alt="Avatar Preview"
                        class="w-32 h-32 object-cover rounded-full border-4 border-primary-600 shadow" />
                @elseif($user?->avatar)
                    <img src="{{ $user->avatar }}" alt="Current Avatar"
                        class="w-32 h-32 object-cover rounded-full border-4 border-primary-600 shadow" />
                @else
                    <div
                        class="w-32 h-32 flex items-center justify-center bg-gray-200 rounded-full border-4 border-primary-600 text-gray-400">
                        No Image</div>
                @endif
            </div>
            <input type="file" wire:model="avatar" name="avatar" accept="image/*" class="block mt-2" />
            @error('avatar')
                <span class="text-red-600 text-xs">{{ $message }}</span>
            @enderror
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded">Update Avatar</button>
        </div>
    </form>

    <hr class="my-8">

    <form wire:submit.prevent="updatePassword" class="max-w-md">
        <h3 class="font-bold mb-2">Change Password</h3>
        <div class="mb-2">
            <label class="block mb-1">Old Password</label>
            <input type="password" wire:model="old_password" class="w-full border rounded px-2 py-1">
            @error('old_password')
                <span class="text-red-600 text-xs">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-2">
            <label class="block mb-1">New Password</label>
            <input type="password" wire:model="password" class="w-full border rounded px-2 py-1">
            @error('password')
                <span class="text-red-600 text-xs">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-2">
            <label class="block mb-1">Confirm Password</label>
            <input type="password" wire:model="password_confirmation" class="w-full border rounded px-2 py-1">
        </div>
        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded">Update Password</button>
    </form>
</div>
{{-- @filamentScripts --}}
