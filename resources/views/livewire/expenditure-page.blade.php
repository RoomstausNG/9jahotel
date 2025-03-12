@push('styles')
<style>
    .no-wrap {
        word-wrap: unset;
        word-break: keep-all;
        white-space: nowrap;
    }

</style>
@endpush

<div class="w-full p-4 pb-20 space-y-8 overflow-y-scroll bg-white rounded-lg">
    <div class="p-4 space-y-6 overflow-y-scroll border rounded-lg shadow-sm">
        <div class="flex justify-between space-x-4 text-2xl expenditures-center ">
            <h1 class="font-medium">
                Expenditures Records
            </h1>
            <a href="{{ route('configuration') }}" class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a>
        </div>
        <div class="w-full space-y-4" x-data="{ expenditureForm: @entangle('expenditureForm').live }">
            <div class="flex justify-between space-x-4 text-2xl font-medium expenditures-center">
                <button @click="expenditureForm = true" class="px-6 py-2 text-sm text-white rounded-md bg-primary">{{ $updateE ? 'Update Expenditure' : 'Add Expenditure' }}</button>

                <x-form.search />
            </div>
            <div class="" x-cloak x-show="expenditureForm">
                <form wire:submit=@if ($updateE) 'updateExpenditure' @else 'AddExpenditure' @endif class="w-full space-y-4">
                    <div class="grid grid-cols-1 gap-4 overflow-y-auto text-sm md:gap-6 md:grid-cols-2 2xl:grid-cols-4">
                        <x-form.select name="financeable_id" label="Expenses">
                            <option>Select Expenditure Type</option>
                            @foreach (\App\Models\Expenditure::select('id', 'source')->get() as $expenditure)
                            <option value="{{ $expenditure->id }}" class="capitalize">
                                {{ $expenditure->source }}
                            </option>
                            @endforeach
                        </x-form.select>
                        <x-input.text label="Amount" name="amount" />
                        <div class="w-full md:col-span-2">
                            <x-input.text label="Description" name="description" />
                        </div>
                    </div>
                    <button class="px-6 py-2 text-sm text-white rounded-md bg-primary max-w-max">Save</button>
                </form>
            </div>
        </div>

        <div id="expenditures" class="w-full pb-8 overflow-y-auto">
            <div class="mb-4 overflow-x-auto overflow-y-scroll bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <table id="" class="w-full overflow-x-auto overflow-y-auto rounded bord">
                    <thead class="w-full overflow-x-auto rounded">
                        <tr class="w-full font-medium bg-gray-100 rounded">
                            {{-- <th class="p-4 font-medium text-left">
                            <input type="checkbox" value="" wire:model="all" class="select-input">
                        </th> --}}
                            <th class="p-4 font-medium text-left">S/N</th>
                            <th class="p-4 font-medium text-left nowrap">Reference</th>
                            <th class="p-4 font-medium text-left nowrap">Source</th>
                            <th class="p-4 font-medium text-left">Type</th>
                            <th class="p-4 font-medium text-left nowrap">Amount</th>
                            <th class="p-4 font-medium text-left">Date
                            </th>
                            <th class="p-4 font-medium text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenditures as $expenditure)
                        <tr wire:key='{{ $expenditure->id }}' class="border-b even:bg-gray-50">

                            <td class="px-4 py-2">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 text-sm capitalize break-keep nowrap">
                                {{ $expenditure->code }}
                            </td>
                            <td class="px-4 py-2">
                                <span>{{ $expenditure->financeable->source }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded-lg text-white capitalize bg-{{ $expenditure->type }}">
                                    {{ $expenditure->type }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <span>{{ formatAsNaira($expenditure->amount) }}</span>
                            </td>
                            </td>
                            <td class="px-4 py-2">
                                {{ $expenditure->created_at->format('d M, Y') }}
                            </td>

                            <td class="px-4 py-2">
                                <p class="flex items-center justify-start space-x-2 text-sm">
                                    <button wire:click="editExpenditure({{ $expenditure->id }})" data-tooltip-target="edit" data-tooltip-placement="left" class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-600 border border-blue-600 rounded-md">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <button wire:click="deleteExpenditure({{ $expenditure->id }})" wire:confirm="Orders and associated Revenues will be deleted as well. Are you sure you want to perform this action?" data-tooltip-target="delete" data-tooltip-placement="left" class="flex justify-center px-2 py-1 space-x-2 text-red-600 border border-red-600 rounded-md expenditures-center">
                                        Delete
                                    </button>
                                </p>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td class="p-8 text-center" colspan="8">No data...</td>
                        </tr>
                        @endforelse
                        {{-- edit tooltip --}}
                        <x-tooltip id="edit" content="Edit expenditure data" />

                        {{-- edit tooltip --}}
                        <x-tooltip id="delete" content="Delete expenditure" />
                    </tbody>
                </table>
                <div class="px-4">
                    <p class="my-2">
                        {{ $expenditures->links(data: ['scrollTo' => '#expenditures']) }}
                    </p>
                </div>
            </div>
        </div>
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
        });
    });

</script>
@endpush
