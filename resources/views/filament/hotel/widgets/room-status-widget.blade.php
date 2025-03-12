<x-filament-widgets::widget class="">
    <div class="w-full py-3 space-y-4">
        <x-filament::section>

            <div class="flex items-center justify-between rounded-lg">
                <div>
                    <h2 class="text-2xl font-semibold">Room Status</h2>
                    <span class="text-sm text-gray-300">{{ $this->description }}</span>
                </div>
                <div>
                    {{-- <input type="date" wire:model.live="{{ $this->data['date'] }}"
                        class="font-medium text-gray-300 border-gray-300 rounded-md cursor-pointer"> --}}
                    {{ $this->form }}
                </div>
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="flex flex-wrap w-full grid-cols-3 gap-8 md:grid-cols-6 bg-primary-200">
                @foreach (myHotel()->rooms as $room)
                    <div class="w-48 p-2 rounded-lg shadow"
                        style="background-color: {{ getColorCode($room->currentStatus($this->data['date'])) }}; color: {{ $room->currentStatus($this->data['date']) !== 'available' ? 'white' : 'black' }}">
                        <p class="px-4 py-2 font-mono text-xl font-bold aspect-video">
                            {{ $room->number }}
                        </p>
                    </div>
                @endforeach
            </div>

        </x-filament::section>
    </div>

    <script>
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('dateField').value = today;
    </script>
</x-filament-widgets::widget>
