<div class="max-w-5xl w-full mx-auto py-10 sm:px-6 lg:px-8">
    <div class="bg-white pb-20  overflow-hidden sm:rounded-lg">
        <form wire:submit="submitForm" class="p-6 py-20 md:py-24 lg:py-28 bg-white space-y-4">
            <h1 class="text-2xl font-bold mb-6">Group Booking Request</h1>

            {{-- <div class="mb-8">
                <div class="flex items-center justify-between">
                    @foreach ($steps as $key => $step)
                        <div class="flex flex-col items-center">
                            <div
                                class="w-10 h-10 rounded-full flex items-center justify-center
                                {{ $step['completed'] ? 'bg-green-500' : ($currentStep == $key ? 'bg-blue-500' : 'bg-gray-300') }}
                                text-white font-bold">
                                {{ $loop->iteration }}
                            </div>
                            <div class="text-sm mt-2">{{ $step['title'] }}</div>
                            <div class="text-xs text-gray-500">{{ $step['description'] }}</div>
                        </div>

                        @if (!$loop->last)
                            <div class="flex-1 h-1 mx-2 bg-gray-300">
                                <div class="h-1 {{ $step['completed'] ? 'bg-green-500' : 'bg-gray-300' }}"
                                    style="width: {{ $step['completed'] ? '100%' : '0%' }}"></div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div> --}}

            {{ $this->form }}
        </form>
    </div>
</div>
</qodoArtifact>
