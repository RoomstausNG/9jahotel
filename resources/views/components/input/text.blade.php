@props(['label', 'name', 'type' => 'text'])

<div class="w-full space-y-1 text-sm">
    <label for="{{ $name }}" class="text-left capitalize">{{ $label }}</label>
    <div class="relative w-full">
        <input name="{{ $name }}" wire:model="{{ $name }}"
            {{ $attributes->merge(['class' => 'w-full p-2 border rounded-md shadow-sm focus:ring-primary focus:border-primary', 'type' => $type ?? 'text', 'name' => $name, 'id' => $name]) }}>
    </div>
    @error($name)
        <span class="text-red-600">{{ $message }}</span>
    @enderror
</div>
