<div class="max-w-5xl w-full mx-auto py-10 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
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
        </div>
    </div>
</div>
</qodoArtifact>
