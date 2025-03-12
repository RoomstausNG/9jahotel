<section class="w-full px-4 py-4 mx-auto space-y-8 text-white bg-white rounded-lg shadow-lg">
    <form wire:submit='searchHotel'
        class="flex flex-col justify-between w-full max-w-screen-xl gap-8 mx-auto lg:items-end lg:flex-row">
        <div class="items-center flex-1">
            {{ $this->form }}
        </div>
        {{-- <button type="submit"
                class="px-6 py-2 mx-auto my-3 font-medium text-white rounded bg-primary hover:bg-secondary hover:text-black max-w-max">Submit</button>
         --}}
        <button type="submit"
            class="px-8 py-2 mx-auto font-medium text-white rounded-full bg-primary hover:bg-secondary hover:text-black max-w-max">Search
            Hotel</button>
    </form>
</section>
