@props(['label', 'name'])

<div class="w-full space-y-1 text-sm">
    <label for="{{ $name }}" class="capitalize ">{{ $label }}</label>
    <div class="relative w-full">
        <input type="text" name="{{ $name }}"
            class = 'w-full p-2 border rounded-md shadow-sm focus:ring-primary focus:border-primary' type = 'file'>
    </div>
    @error($name)
        <span class="text-red-600">{{ $message }}</span>
    @enderror
</div>
