<form wire:submit.prevent="submit"> {{-- Updated for Livewire --}}
    @csrf {{-- CSRF token for Laravel forms (actually not mandatory for Livewire, but harmless) --}}
    <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input type="text" name="name" id="name" wire:model.defer="name" placeholder="Enter Your Name"
            class="w-full px-3 py-2 bg-primary-50 border border-primary-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500">
        @error('name')
            <span class="text-red-600 text-xs">{{ $message }}</span>
        @enderror
    </div>
    <div class="mb-4">
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
        <input type="tel" name="phone" id="phone" wire:model.defer="phone" placeholder="Enter Phone number"
            class="w-full px-3 py-2 bg-primary-50 border border-primary-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500">
        @error('phone')
            <span class="text-red-600 text-xs">{{ $message }}</span>
        @enderror
    </div>
    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" id="email" wire:model.defer="email" placeholder="Enter Email"
            class="w-full px-3 py-2 bg-primary-50 border border-primary-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500">
        @error('email')
            <span class="text-red-600 text-xs">{{ $message }}</span>
        @enderror
    </div>
    <div class="mb-6">
        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
        <textarea name="message" id="message" wire:model.defer="message" rows="4" placeholder="Your Message"
            class="w-full px-3 py-2 bg-primary-50 border border-primary-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"></textarea>
        @error('message')
            <span class="text-red-600 text-xs">{{ $message }}</span>
        @enderror
    </div>
    <div>
        <button type="submit"
            class="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-4 rounded-md transition duration-150">
            Submit
        </button>
    </div>
</form>
