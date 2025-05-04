{{-- <form wire:submit='signUp' class="w-full mx-auto space-y-6 text-white">
    <div class="flex flex-col flex-wrap w-full gap-4 p-4 border-gray-500 divide-white rounded">
        <div>{{ $this->form }}</div>
        <div class="w-full pt-2">
            <button
                class="block px-6 py-2 mx-auto text-white rounded-full min-w-max hover:bg-primary-700 bg-primary">Create
                an
                account</button>
        </div>
    </div>
</form> --}}

<div class="py-8 md:py-12 px-6 max-w-3xl mx-auto space-y-8">
    <x-filament-panels::form wire:submit="signUp">
        {{ $this->form }}
        <div class="w-full pt-2">
            <button class="block px-6 py-2 mx-auto text-white rounded-full min-w-max hover:bg-primary-700 bg-primary"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove>Create an account</span>
                <span wire:loading>Creating...</span>
            </button>
        </div>
    </x-filament-panels::form>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('swal:success', (event) => {
                Swal.fire(event.details)
                setTimeout(() => {
                    location.href = "{{ route('filament.hotel.auth.login') }}";
                }, 10000);
            });
        });
    </script>
@endpush
