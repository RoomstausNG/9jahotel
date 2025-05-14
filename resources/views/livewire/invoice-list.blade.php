<div class="w-full p-4 space-y-6 overflow-y-scroll bg-white rounded-lg" x-data="{ active: 'hotel' }">
    <div class="flex items-center justify-between space-x-4 text-2xl ">
        <h1 class="font-medium">
            Invoice
        </h1>
        <a href="{{ route('configuration') }}" class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a>
    </div>

    <div class="w-full space-y-4" x-data="{ orderForm: @entangle('orderForm').live }">
        <div class="flex items-center justify-between space-x-4 text-2xl font-medium">
            {{-- <button @click="orderForm = true"
                class="px-6 py-2 text-sm text-white rounded-md bg-primary">{{ $updateC ? 'Update Sale' : 'Add Sale' }}</button> --}}


            {{-- <script src="https://js.paystack.co/v1/inline.js"></script> --}}
            <x-form.search />
        </div>
    </div>

    <div class="w-full pb-8 overflow-y-auto">
        <div
            class="mb-4 overflow-x-auto overflow-y-scroll bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <table id="" class="w-full overflow-x-auto overflow-y-auto rounded bord">
                <thead class="w-full overflow-x-auto rounded">
                    <tr class="w-full font-medium bg-gray-100 rounded">
                        {{-- <th class="p-4 font-medium text-left">
                            <input type="checkbox" value="" wire:model="all" class="select-input">
                        </th> --}}
                        <th class="p-4 font-medium text-left nowrap">Reference</th>
                        <th class="p-4 font-medium text-left nowrap">Amount</th>
                        <th class="p-4 font-medium text-left nowrap">Issued on</th>
                        <th class="p-4 font-medium text-left nowrap">From</th>
                        <th class="p-4 font-medium text-left nowrap">To</th>
                        {{-- <th class="p-4 font-medium text-left nowrap">Paid on</th> --}}
                        <th class="p-4 font-medium nowrap">Status</th>
                        <th class="p-4 font-medium text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr wire:key='{{ $invoice->id }}' class="border-b even:bg-gray-50">

                            <td class="px-4 py-2 text-sm font-bold uppercase break-keep nowrap">
                                {{ $invoice->reference }}
                            </td>
                            <td class="px-4 py-2 capitalize">
                                {{ formatAsNaira($invoice->amount) }}
                            </td>
                            <td class="px-4 py-2 capitalize nowrap">{{ $invoice->created_at?->format('d M, Y') }}</td>
                            <td class="px-4 py-2 capitalize nowrap">{{ $invoice->from?->format('d M, Y') }}</td>
                            <td class="px-4 py-2">
                                {{ $invoice->to?->format('d M, Y') }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                <span
                                    class="px-2 py-1 text-sm border {{ $invoice->status == 'unpaid' ? 'border-red-600 text-red-600' : 'bg-green-600 text-white' }} rounded">{{ $invoice->status }}</span>
                            </td>
                            {{-- <td class="px-4 py-2 text-sm">
                                {{ formatAsNaira($invoice->item->price) }}
                            </td> --}}

                            <td class="px-4 py-2">
                                <p class="flex items-center justify-start space-x-2 text-sm">

                                    @if ($invoice->status == 'unpaid')
                                        {{-- <form id="paymentForm" class="inline">
                                            <input type="hidden" value="{{ myHotel()->email }}" id="email-address"
                                                required />
                                            <input type="hidden" value="{{ $invoice->amount }}" id="amount"
                                                required />
                                            <input type="hidden" value="{{ auth()->user()->firstname }}"
                                                id="first-name" />
                                            <input type="hidden" value="{{ $invoice->reference }}" id="ref" />
                                            <input type="hidden" value="{{ auth()->user()->lastname }}"
                                                id="last-name" />
                                             <button
                                                class="px-2 py-1 space-x-2 text-green-600 border border-green-600 rounded-md "
                                                type="submit" onclick="payWithPaystack()"> Pay </button>
                                        </form> --}}
                                        <button type="button" wire:click="payInvoice({{ $invoice->id }})"
                                            data-tooltip-target="pay" data-tooltip-placement="left"
                                            class="flex items-center justify-center px-2 py-1 space-x-2 text-green-600 border border-green-600 rounded-md">
                                            {{-- <i class="fa-solid fa-pen-to-square"></i> --}}
                                            <span>
                                                Pay
                                            </span>
                                        </button>
                                    @else
                                        {{-- <a href="{{ route('invoices.receipt', $invoice->reference) }}" target="_blank"
                                            data-tooltip-target="print" data-tooltip-placement="left"
                                            class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-500 border border-blue-500 rounded-md max-w-max">
                                            <i class="fa-solid fa-print"></i> <span>View</span>
                                        </a> --}}
                                    @endif

                                </p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="p-4 text-center" colspan="8">No data...</td>
                        </tr>
                    @endforelse
                    {{-- edit tooltip --}}
                    <x-tooltip id="pay" content="Pay for subscription" />

                    {{-- print tooltip --}}
                    <x-tooltip id="print" content="Print receipt" />

                    {{-- edit tooltip --}}
                    <x-tooltip id="view" content="View" />
                </tbody>
            </table>
            <div class="px-4">
                <p class="my-2">
                    {{ $invoices->links() }}
                </p>
            </div>
        </div>
    </div>


</div>

@script
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        $wire.on('pk', (event) => {
            alert('hello, I am clicked');
            // console.log(event.invoice);

            // let handler = PaystackPop.setup({
            //     key: 'pk_test_99a8cd7670b63ea9d458957e4195fb9ceed0bbb3', // bonifade pk_live_66c58d224c0726fb52a91b92fa7b2b65dd9032ab
            //     email: {{ myHotel()->email }},
            //     amount: e.invoice.amount,
            //     ref: e.invoice.reference + Math.floor((Math.random() * 100) + 1),
            //     onClose: function() {
            //         Swal.fire({
            //             'icon': 'error',
            //             'text': 'The payment could not be completed',
            //             'title': 'Payment fail',
            //             'showConfirmButton': true,
            //             'timer': 3000,
            //         })
            //     },
            //     callback: function(response) {
            //         // let message = 'Payment complete! Reference: ' + response.reference;
            //         // alert(message);
            //         $wire.dispatch('savePayment', {
            //             invoice: e.invoice
            //         });
            //     }
            // });

            // handler.openIframe();
        });
        document.addEventListener('livewire:init', () => {
            Livewire.on('payDo', (event) => {
                alert('hello, I am clicked');
                console.log(event.invoice);

            });
        }, true);
    </script>
@endscript

@push('scripts')
    {{-- <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        const paymentForm = document.getElementById('paymentForm');
        paymentForm.addEventListener("submit", payWithPaystack, false);

        function payWithPaystack(e) {
            e.preventDefault();

            let handler = PaystackPop.setup({
                key: 'pk_test_49e0366c1fcd2d36a47bc777919c7e2297cd776a', // Replace with your public key
                email: document.getElementById("email-address").value,
                amount: document.getElementById("amount").value * 100,
                ref: document.getElementById("ref").value + Math.floor((Math.random() * 100) +
                    1
                ), // generates a pseudo-unique reference. Please replace with a reference you generated. Or remove the line entirely so our API will generate one for you
                // label: "Optional string that replaces customer email"
                onClose: function() {
                    Swal.fire({
                        title: 'Payment Cancelled',
                        text: "You you can still repaid again",
                        icon: 'error',
                        timer: 5000,
                        showConfirmButton: true,
                    })
                },
                callback: function(response) {
                    $wire.dispatch('savePayment', response);
                }
            });

            handler.openIframe();
        }
    </script> --}}

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
