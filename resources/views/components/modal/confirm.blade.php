@props(['text'])

<div x-cloak x-data="{ open: true }" x-init="setTimeout(() => { open = false }, 6000)" x-show="open"
    x-transition:enter="transition duration-500 transform ease-out"
    x-transition:leave="transition duration-500 transform ease-in"
    class="fixed left-0 right-0 z-50 items-center justify-center overflow-x-hidden overflow-y-auto top-4 md:inset-0 h-modal sm:h-full">
    <div class="relative w-full h-full max-w-md px-4 md:h-auto">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
            <!-- Modal header -->
            <div class="flex justify-end p-2">
                <button type="button" @click="open = false"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-700 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
            <!-- Modal body -->
            {{-- <x-modal.confirm /> --}}
            <div class="p-6 pt-0 space-y-4 text-center">
                {{-- placeholder for icons --}}
                {{ $slot }}

                <h4 class="text-xl font-medium text-gray-500">Welcome {{ $user->first_name }}</h4>

                <h3 class="mt-5 mb-6 text-lg text-gray-500 dark:text-gray-400">{{ $message }}</h3>
            </div>
        </div>
    </div>
    {{-- confirmation button --}}
    <x-confirm-delete
        text="Are you sure you want to delete this,
   this action is irreversible. All the associated data link agents, trips will also be deleted as well" />

</div>
