<section class="flex flex-col items-center justify-center w-full py-16 space-y-8 bg-slate-100">
    <div class="text-center">
        <h1 class="text-2xl font-semibold">Exclusive Hotel Deals and Offers</h1>
        <p>Sign up with your email address to receive exclusive hotel deals and special offers.</p>
    </div>
    <div class="w-full px-8 mx-auto max-w-[600px]">
        <form wire:submit='subscribe' class="flex items-end gap-3">
            <div class="flex-1">
                {{ $this->form }}
            </div>

            {{-- <button type="submit"
                class="px-6 py-2 mx-auto my-3 font-medium text-white rounded bg-primary hover:bg-secondary hover:text-black max-w-max">Submit</button>
         --}}
            <button type="submit"
                class="px-6 py-[6px] mx-auto font-medium text-white rounded bg-primary hover:bg-secondary hover:text-black max-w-max">Submit</button>
        </form>
    </div>

</section>
