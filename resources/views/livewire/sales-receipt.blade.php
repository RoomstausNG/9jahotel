<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $sale->code }}</title>
    @vite(['resources/css/app.css', 'resources/scss/main.scss'])
</head>

<body class="flex flex-col justify-center pt-8 bg-gray-100 print:bg-none">
    <div class="px-2 py-4 mx-auto space-y-3 bg-white border w-[600px] print:block">
        @if ($sale->hotel->logo)
            <img src="{{ $sale->hotel->logo ? asset('storage/' . $sale->hotel->logo) : asset('img/logo.png') }}"
                class="object-cover w-16 h-16 mx-auto" alt="{{ $sale->hotel->name }}">
            <h1 class="p-2 text-2xl font-bold text-center uppercase">{{ $sale->hotel->name }}</h1>
        @else
            <h1 class="p-2 text-2xl font-bold text-center uppercase">{{ $sale->hotel->name }}</h1>
        @endif
        <div class="text-center ">
            <p>Tel: {{ auth()->user()->hotel->telephone }}</p>
            <p>Email: {{ auth()->user()->hotel->email }}</p>
            <p>Website: {{ config('app.url') }}</p>

            <p class="mx-auto mt-3 border-t-2 border-black border-dashed max-w-max">Serve by:
                {{ $sale->agent->firstname . ' ' . $sale->agent->lastname }}</p>
        </div>
        <div class="w-full px-6 space-y-4">
            <table class="w-full pb-6">
                <thead>
                    <tr class="px-6">
                        <th class="p-3 text-left w-[35%]">Items</th>
                        <th class="p-3">Unit price</th>
                        <th class="p-3">Qty</th>
                        <th class="p-3 text-left">Total(#)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-y">
                        <td class="p-2">
                            <p class="flex items-center space-x-3">

                                <span>{{ $sale->item->name }}</span>
                                @if ($sale->item->image)
                                    <img src="{{ asset('storage/' . $sale->item->image) }}"
                                        alt="{{ $sale->item->name }}" class="object-cover w-16 aspect-video">
                                @endif
                            </p>
                        </td>
                        <td class="p-2 text-center">{{ formatAsNaira($sale->item->price) }}</td>
                        <td class="p-2 text-center">{{ $sale->quantity }}</td>
                        <td class="p-2">{{ formatAsNaira($sale->item->price * $sale->quantity) }}</td>
                    </tr>

                    <tr class="pt-8">
                        <td class="p-2"></td>
                        <td class="p-2"></td>
                        <td class="p-2 font-medium border-b border-black">Sub-Total</td>
                        <td class="p-2 font-medium border-b border-black">
                            {{ formatAsNaira($sale->item->price * $sale->quantity) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="p-2"></td>
                        <td class="p-2"></td>
                        <td class="p-2 font-medium border-t border-black">Discount
                            ({{ $sale->discount ? $sale->discount . '%' : '0%' }})
                        </td>
                        <td class="p-2 font-medium border-black border-y">
                            {{ formatAsNaira($sale->item->price * $sale->quantity * ($sale->discount / 100)) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="p-2"></td>
                        <td class="p-2"></td>
                        <td class="p-2 font-semibold border-black border-y">Total</td>
                        <td class="p-2 font-medium border-black border-y">{{ formatAsNaira($sale->total_price) }}</td>
                    </tr>
                </tbody>
            </table>

        </div>
        <p class="py-3 text-lg text-center ">Thanks for shopping with us</p>
        <div class="w-full text-center">
            <p class="px-6 py-1 mx-auto font-medium bg-gray-100 rounded-sm max-w-max">Order <span
                    class="uppercase ">{{ $sale->code }}</span></p>
            <p class="text-lg font-medium">Date: {{ $sale->created_at?->format('d-m-Y') }}</p>
        </div>
        <button onclick="window.print()"
            class="block px-2 py-1 mr-2 text-sm text-white bg-blue-600 rounded cursor-pointer max-w-max print:hidden">
            <i class="fa-solid fa-print"></i>
            print</button>
    </div>

</body>

</html>
