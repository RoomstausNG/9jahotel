@props(['name', 'label'])
<div class="w-full space-y-1 text-sm">
    <label for="{{ $name }}">{{ $label }}</label>
    <select wire:model.live="{{ $name }}" name="{{ $name }}" id="{{ $name }}"
        {{ $attributes->merge(['class' => 'w-full rounded-md focus:ring-primary focus:border-primary']) }}>
        {{ $slot }}
    </select>
    @error($name)
        <span class="text-red-600">{{ $message }}</span>
    @enderror
</div>
