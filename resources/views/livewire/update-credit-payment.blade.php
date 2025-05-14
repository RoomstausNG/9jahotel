<div class="pb-32 space-y-6">
    <div class="p-4 space-y-4 bg-white rounded-lg w-ful" x-data="{ step: 1 }">
        <div class="flex items-center justify-between space-x-4 text-2xl ">
            <h1 class="font-medium">
                Credit payment Histories <span
                    class="px-6 py-2 ml-4 font-medium capitalize border border-gray-300 rounded-md shadow-sm text-primary">{{ $business->name }}</span>

            </h1>
            <a href="javascript:history.back()" class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a>
        </div>
    </div>
    <div class="flex flex-col items-center pr-4 bg-white rounded-lg shadow justfy-between lg:flex-row">
        <form wire:submit="pay"
            class="flex flex-col flex-wrap items-end w-full gap-4 p-4 rounded-lg shadow-sm lg:flex-row">
            <div class="px-6 py-2 bg-white shadow text-red-600 max-w-[200px] rounded-md">
                <p py-2 px-4>{{ formatAsNaira($business->balance) }}</p>
            </div>
            <div class="flex items-end space-x-4">
                <x-input.text name="amount_paid" label="Amount" />
                <button class="px-6 py-2 text-white bg-green-500 rounded-md">Pay</button>
            </div>
        </form>
        <button wire:click="notifyBusiness({{ $business->id }})" data-tooltip-target="send email"
            data-tooltip-placement="left"
            class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-500 border border-blue-500 rounded-md">
            Notify
        </button>
    </div>

    @if ($payments?->count())
        <div class="w-full p-4 space-y-6 overflow-y-scroll bg-white rounded-lg bb-20" x-data="{ active: 'hotel' }">
            <div class="w-full pb-8 overflow-y-auto">
                <div
                    class="mb-4 overflow-x-auto overflow-y-scroll bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <table id="" class="w-full overflow-x-auto overflow-y-auto rounded bord">
                        <thead class="w-full overflow-x-auto rounded">
                            <tr class="w-full font-medium bg-gray-100 rounded">
                                {{-- <th class="p-4 font-medium text-left">
                                <input type="checkbox" value="" wire:model="all" class="select-input">
                            </th> --}}
                                <th class="p-4 font-medium text-left">No</th>
                                <th class="p-4 font-medium text-left nowrap">Transaction Date</th>
                                <th class="p-4 font-medium text-left nowrap">Amount owe</th>
                                <th class="p-4 font-medium text-left nowrap">Amount Paid</th>
                                <th class="p-4 font-medium text-left">Balance</th>
                                {{-- <th class="p-4 font-medium text-left">Action</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr wire:key='{{ $payment->id }}' class="border-b even:bg-gray-50">
                                    {{-- <td class="px-4 py-2">

                                </td> --}}
                                    <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-2 text-sm capitalize break-keep nowrap">
                                        {{ $payment->created_at?->format('h:iA, l d M, Y') }}
                                    </td>

                                    <td class="px-4 py-2 ">
                                        {{ formatAsNaira($payment->amount_owed) }}
                                    </td>

                                    <td class="px-4 py-2 ">
                                        {{ formatAsNaira($payment->amount_paid) }}
                                    </td>

                                    <td class="px-4 py-2 text-sm">
                                        {{ formatAsNaira($payment->amount_owed + $payment->amount_paid) }}
                                    </td>

                                    {{-- <td class="px-4 py-2">
                                        <p class="flex items-center justify-start space-x-2 text-sm">
                                            <button wire:click="deletepayment({{ $payment->id }})"
                                                wire:confirm="payment will be deleted including finance records. Are you sure you want to perform this action?"
                                                data-tooltip-target="delete" data-tooltip-placement="left"
                                                class="flex items-center justify-center px-2 py-1 space-x-2 text-red-600 border border-red-600 rounded-md">
                                                Delete
                                            </button>
                                        </p>
                                    </td> --}}
                                </tr>
                            @empty
                                <tr>
                                    <td class="p-4 text-center" colspan="8">No data...</td>
                                </tr>
                            @endforelse
                            {{-- edit tooltip --}}
                            <x-tooltip id="edit" content="Edit business data" />

                            {{-- edit tooltip --}}
                            <x-tooltip id="delete" content="Change Status" />
                        </tbody>
                    </table>
                    <div class="px-4">
                        <p class="my-2">
                            {{ $payments?->links() }}
                        </p>
                    </div>
                </div>
            </div>


        </div>
    @else
        <div class="p-4 my-4 bg-white rounded-lg shadow">
            <p class="p-2 text-xl text-yellow-400">Business has no payment histories yet!</p>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('swal:success', (event) => {
                Swal.fire(event.details)
                setTimeout(() => {
                    location.reload();
                }, 5000);
            });
        });
    </script>
@endpush
