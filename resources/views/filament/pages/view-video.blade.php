<x-filament::page>
    <video controls>
        <source src="{{ asset($this->record->url) }}" type="{{ $this->record->type }}">
        Your browser does not support the video tag.
    </video>
</x-filament::page>
