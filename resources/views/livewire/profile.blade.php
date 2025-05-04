<div class="py-8">
    <form wire:submit.prevent="submit">
        {{ $this->form }}
        <button type="submit" class="mt-4 px-4 py-2 bg-primary-600 text-white rounded">Update Profile</button>
    </form>
    @if (session()->has('success'))
        <div class="p-2 mt-4 text-green-700 bg-green-100 rounded">
            {{ session('success') }}
        </div>
    @endif

    <hr class="my-8">
    <form wire:submit.prevent="updatePassword" class="mt-8">
        <div class="mb-4">
            <label for="current_password" class="block mb-1 font-semibold">Current Password</label>
            <input type="password" id="current_password" wire:model.defer="current_password"
                class="w-full px-3 py-2 border rounded" required>
            @error('current_password')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-4">
            <label for="password" class="block mb-1 font-semibold">New Password</label>
            <input type="password" id="password" wire:model.defer="password" class="w-full px-3 py-2 border rounded"
                required>
            @error('password')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="block mb-1 font-semibold">Confirm New Password</label>
            <input type="password" id="password_confirmation" wire:model.defer="password_confirmation"
                class="w-full px-3 py-2 border rounded" required>
        </div>
        <button type="submit" class="mt-4 px-4 py-2 bg-primary-600 text-white rounded">Update Password</button>
    </form>
</div>
