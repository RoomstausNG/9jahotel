<form wire:submit='search' class="lg:pr-3">
    <label for="query" class="sr-only">Search</label>
    <div class="relative mt-1 overflow-hidden rounded-lg lg:w-64 xl:w-96">
        <input wire:model="query" type="search" id="query"
            class="bg-white shadow-sm border-gray-300 focus:bg-white text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            placeholder="Search here...">
        <button class="absolute top-0 right-0 h-full px-3 text-gray-500 bg-gray-200 cursor-pointer hover:text-black"
            type="submit">
            <i class="text-xl fa-solid fa-magnifying-glass"></i>
        </button>
    </div>
</form>
