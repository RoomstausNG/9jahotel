<div class="relative w-full" x-data="{ pass: true }">
    <x-form.label :name="$name" :label="$label" />
    <div class="relative w-full overflow-hidden">
        <input wire:model="{{ $name }}"
            {{ $attributes->merge(['class' => 'shadow-sm bg-gray-50 overflow-hidden text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500', 'name' => $name, 'id' => $name]) }}
            :type="pass ? 'password' : 'text'">
        <span @click="pass= !pass" class="absolute cursor-pointer hover:text-red-600 top-2 right-2"><i class="fa"
                :class="pass ? 'fa-eye' : 'fa-eye-slash'"></i></span>
    </div>
    @error($name)
        <span class="mb-2 text-sm text-red-600">{{ $message }}</span>
    @enderror
</div>
