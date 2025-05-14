<div class="w-full px-4 py-8 space-y-4 text-black bg-white rounded-lg bg-opacity-95 lg:py-12 lg:px-8 lg:space-y-6">
    @session('success')
        <div class="px-4 py-2 text-green-600 bg-red-100 rounded-md">
            {{ $value }}
        </div>
    @endsession
    @session('error')
        <div class="px-4 py-2 text-white bg-green-500 rounded-md">
            {{ $value }}
        </div>
    @endsession
    <div class="space-y-1">
        <span class="inline-block mx-auto text-yellow-500">
            <svg class="text-yellow-500" width="96" height="96" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                <path
                    d="M569.5 440C588 472 564.8 512 527.9 512H48.1c-36.9 0-60-40.1-41.6-72L246.4 24c18.5-32 64.7-32 83.2 0l239.9 416zM288 354c-25.4 0-46 20.6-46 46s20.6 46 46 46 46-20.6 46-46-20.6-46-46-46zm-43.7-165.3l7.4 136c.3 6.4 5.6 11.3 12 11.3h48.5c6.4 0 11.6-5 12-11.3l7.4-136c.4-6.9-5.1-12.7-12-12.7h-63.4c-6.9 0-12.4 5.8-12 12.7z" />
            </svg>
        </span>
        {{-- <img class="w-20 mx-auto shadow-lg" src="{{ asset('img/roomstatus.png') }}" alt="{{ env('APP_NAME') }}"> --}}
        <h1 class="text-2xl font-semibold"> Renew Your Subscription</h1>
        <p class="text-lg"> Welcome back! Renew your subscription to continue enjoying our services.</p>
    </div>
    <div class="">
        <h1 class="text-lg font-medium">Your Hotel Information</h1>
        <table class="w-full rounded shadow-sm">
            <tr class="w-full p-2">
                <td class="px-2 py-1 text-left">Hotel Name:</td>
                <td class="px-2 py-1 text-left">{{ $hotel->name }}</td>
            </tr>
            <tr class="w-full p-2">
                <td class="px-2 py-1 text-left">Hotel Address:</td>
                <td class="px-2 py-1 text-left first-letter:capitalize">{{ $hotel->address }}</td>
            </tr>
        </table>
    </div>
    <div class="">
        <h1 class="text-lg font-medium">Your Current Information</h1>
        <table class="w-full rounded shadow-sm">
            <tr class="w-full p-2">
                <td class="px-2 py-1 text-left">Plan Name:</td>
                <td class="px-2 py-1 text-left capitalize">{{ $invoice->subscription }}</td>
            </tr>
            <tr class="w-full p-2">
                <td class="px-2 py-1 text-left">Expiration date:</td>
                <td class="px-2 py-1 text-left first-letter:capitalize">{{ $hotel->renew?->format('d M, Y') }}</td>
            </tr>
        </table>
    </div>
    @if ($invoice)
        <form wire:submit='pay' class="space-y-1">
            <h1 class="text-lg font-medium">Select Renewal Plan</h1>
            @error('amount')
                <span class="text-center text-red-600">{{ $message }}</span> <br>
            @enderror
            <select name="amount" wire:model='amount'
                class="py-2 mb-2 @error('amount') 'border-red-600' @enderror text-sm font-medium rounded focus:border-primary focus:ring-0 border-primary"
                id="">
                <option selected value="{{ $invoice->amount }}">Default ({{ formatAsNaira($invoice->amount) }})
                </option>
                <option value="10000" disabled>Monthly {{ formatAsNaira(10000) }}</option>
                <option value="99995" disabled>Yearly {{ formatAsNaira(99995) }}</option>
            </select>
            <button class="block px-6 py-2 mx-auto text-white rounded-lg bg-primary">Pay Now</button>
            {{-- <p class="text-lg"> Welcome back! Renew your subscription to continue enjoying our services.</p> --}}
        </form>
    @else
        <div class="flex text-center">
            <h1 class="text-red-600 text-3xl font-bold">Account is temporary Blocked</h1>
        </div>
    @endif

    <div class="border-t border-black border-dashed">
        <span class="text-lg font-medium">{{ env('APP_NAME') }}</span>
        Call: <a href="tel:+2348134121123">08134121123</a> / <a href="tel:+234701196873">0701196873</a>
        <a href="mailto:sales@roomstatus.ng">, sales@roomstatus.ng</a>
        <p class="p-1 text-white bg-black">{{ env('APP_URL') }}</p>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('swal:success', (event) => {
                Swal.fire(event.details)
                setTimeout(() => {
                    location.reload();
                }, 5000);
                // console.log(event.details)
            });

        });
    </script>
@endpush
