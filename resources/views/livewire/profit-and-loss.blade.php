<div class="relative grid gap-4 pb-40 overflow-y-auto grid-col-1">
    <div class="flex items-center justify-between w-full space-y-2">
        <h1 class="text-2xl font-semibold text-white">Profit and Loss</h1>
        <a href="{{ route('report') }}" class="px-6 py-2 text-gray-700 rounded bg-gray-50">Back</a>
    </div>
    <form wire:submit='checkHistories' class="flex flex-col flex-wrap w-full gap-4 p-4 bg-white rounded-lg shadow-sm">
        <div class="w-full max-w-[300px]">
            <x-input.text type="date" label="Transaction start Date" name="start" />
        </div>
        <div class="w-full max-w-[300px]">
            <x-input.text type="date" label="Transaction end Date" name="end" />
        </div>
        <button class="px-6 py-2 text-white rounded-lg shadow-sm bg-primary-700 nowrap max-w-max">View Report</button>
    </form>
    @if ($showTable)
        <div class="w-full p-4 overflow-auto bg-white rounded-lg shadow">

            <div class="w-full p-2 space-y-4">
                <h1 class="text-lg font-semibold text-center uppercase">Profit and Loss Report from
                    <span class="text-blue-600">{{ $start ?? now()?->format('d M, Y') }}</span> to
                    <span class="text-blue-600"> {{ $end ?? now()?->format('d M, Y') }}</span> generated on
                    {{ date('d M, Y') }}
                </h1>
                <div class="flex w-full border divide-x">
                    <div class="w-full p-2 space-y-2 md:w-1/2">
                        <h1 class="text-xl font-bold text-center">Revenue</h1>
                        <div class="w-full">
                            <table class="w-full border">
                                <thead class="w-full">
                                    <tr class="w-full">
                                        <th class="w-1/2 px-4 py-2 text-left bg-gray-100">Source</th>
                                        <th class="w-1/2 px-4 py-2 text-left bg-gray-100">Amount(#)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="px-4 py-2">Items Sales</td>
                                        <td class="px-4 py-2 font-medium">{{ formatAsNaira($orders) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-2">Room Reservation</td>
                                        <td class="px-4 py-2 font-medium">{{ formatAsNaira($room) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-2">Hall Reservation</td>
                                        <td class="px-4 py-2 font-medium">{{ formatAsNaira($hall) }}</td>
                                    </tr>
                                    @foreach ($incomes ?? [] as $income)
                                        <tr>
                                            <td class="px-4 py-2">{{ $income->source }}</td>
                                            <td class="px-4 py-2 font-medium">{{ formatAsNaira($income->total) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="p-1 border">
                                        <td class="px-4 py-2 text-xl font-bold">Total Revenue</td>
                                        <td class="px-4 py-2 text-xl font-bold">
                                            @if ($incomes?->count() > 0)
                                                <span>
                                                    {{ formatAsNaira($orders + $room + $hall + $incomes->sum('total')) }}
                                                </span>
                                            @else
                                                <span
                                                    class="text-green-500">{{ formatAsNaira($orders + $room + $hall) }}</span>
                                            @endif
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="w-full p-2 space-y-2 md:w-1/2">
                        <h1 class="text-xl font-bold text-center">Expenditure</h1>
                        <div class="w-full">
                            <table class="w-full border">
                                <thead class="w-full">
                                    <tr class="w-full">
                                        <th class="w-1/2 px-4 py-2 text-left bg-gray-100">Source</th>
                                        <th class="w-1/2 px-4 py-2 text-left bg-gray-100">Amount(#)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($expenditures ?? [] as $expense)
                                        <tr>
                                            <td class="px-4 py-2 capitalize">{{ $expense->source }}</td>
                                            <td class="px-4 py-2 font-medium">{{ formatAsNaira($expense->total) }}</td>
                                        </tr>
                                    @endforeach
                                    @if ($expenditures?->count() > 0)
                                        <tr class="p-1 border">
                                            <td class="px-4 py-2 text-xl font-bold">Total Expenditure</td>
                                            <td class="px-4 py-2 text-xl font-bold text-red-600">
                                                {{ formatAsNaira($expenditures->sum('total')) }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @php
                    $revenueSum = $orders + $room + $hall;
                @endphp
                <div class="w-full p-2">
                    <p class="text-xl">Profit as at date: <span
                            class="font-bold text-green-500">{{ formatAsNaira($revenueSum - $expenditures->sum('total')) }}</span>
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
