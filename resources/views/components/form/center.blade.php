@props(['update', 'title', 'header' => 'Add'])
<div x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center h-screen overflow-x-hidden bg-black bg-opacity-80"
    x-data="{ form: @entangle('form').live }" x-show="form">
    <div @click.away="form = false" class="relative w-full h-full max-w-lg px-4 overflow-y-auto md:h-auto">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow overflow-y-auto  dark:bg-gray-800 max-h-[600px]">
            <!-- Modal header -->
            <div class="flex items-start justify-between p-5 border-b rounded-t dark:border-gray-700">
                <h3 class="text-xl font-semibold capitalize dark:text-white">
                    @if ($update)
                        Edit {{ $title }} details
                    @else
                        {{ $header ?? 'Add' }} {{ $title }}
                    @endif
                </h3>
                <button type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-700 dark:hover:text-white"
                    @click="form = false">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>

            <form class="p-6 space-y-6" wire:submit=@if ($update) 'update' @else 'save' @endif>
                {{ $slot }}
                <!-- Modal footer -->
                <div class="items-center border-gray-200 rounded-b dark:border-gray-700">
                    <button
                        class="text-white disabled:bg-gray-700 capitalize disabled:text-gray-500 bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
                        type="submit">{{ $update ? 'Update' : 'Add' }} {{ $title }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
