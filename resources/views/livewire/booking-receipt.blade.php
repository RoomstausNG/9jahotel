<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $booking->code }}</title>
    @vite(['resources/css/app.css', 'resources/scss/main.scss'])
</head>

<body class="flex flex-col justify-center pt-8 bg-gray-100 print:bg-none">
    <div class="px-2 py-4 mx-auto space-y-3 bg-white border w-[600px] print:block">
        @if ($booking->hotel->logo)
            <img src="{{ $booking->hotel->logo ? asset('storage/' . $booking->hotel->logo) : asset('img/logo.png') }}"
                class="object-cover w-16 h-16 mx-auto" alt="{{ $booking->hotel->name }}">
            <h1 class="p-2 text-2xl font-bold text-center uppercase">{{ $booking->hotel->name }}</h1>
        @else
            <h1 class="p-2 text-2xl font-bold text-center uppercase">{{ $booking->hotel->name }}</h1>
        @endif
        <div class="text-center ">
            <p>Tel: {{ auth()->user()->hotel->telephone }}</p>
            <p>Email: {{ auth()->user()->hotel->email }}</p>
            <p>Website: {{ config('app.url') }}</p>

            <p class="mx-auto mt-3 border-t-2 border-black border-dashed max-w-max">Serve by:
                {{ $booking->agent->firstname . ' ' . $booking->agent->lastname }}</p>
        </div>
        <div class="flex justify-center w-full px-6 mx-auto space-y-4">
            <table class="w-full pb-6 mx-auto">
                {{-- <thead>
                    <tr class="px-6">
                        <th class="p-3 text-left w-[35%]">Room</th>
                        <th class="p-3">price</th>
                        <th class="p-3">Days</th>
                        <th class="p-3">Days</th>
                        <th class="p-3 text-left">Total(#)</th>
                    </tr>
                </thead> --}}
                {{-- <tbody> --}}

                <tr>
                    <td class="px-2">Guest:</td>
                    <td class="px-2 capitalize">{{ $booking->customer->name }}</td>
                </tr>
                <tr>
                    <td class="px-2">Total Amount Received:</td>
                    <td class="px-2">{{ formatAsNaira($booking->amount_paid) }}</td>
                </tr>
                <tr>
                    <td class="px-2">Room Type:</td>
                    <td class="px-2 uppercase">{{ bookable->type?->name }}</td>
                </tr>
                <tr>
                    <td class="px-2">Room Number:</td>
                    <td class="px-2 uppercase">
                        @if (!empty($booking->bookable->name))
                            {{ $booking->bookable->name }}
                        @else
                            Room Number {{ $booking->bookable->number ?? '' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="px-2">Checked-In Date:</td>
                    <td class="px-2">{{ $booking->checked_in->format('D d M, Y') }}</td>
                </tr>
                <tr>
                    <td class="px-2">Checked-Out Date:</td>
                    <td class="px-2">{{ $booking->checked_out->format('D d M, Y') }}</td>
                </tr>
                @if ($booking->due > $booking->amount_paid)
                    <tr>
                        <td class="px-2">Unpaid Balance:</td>
                        <td class="px-2">{{ formatAsNaira($booking->due - $booking->amount_paid) }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="p-2"></td>
                    <td class="p-2"></td>
                </tr>
                {{-- </tbody> --}}
            </table>

        </div>
        <p class="py-3 text-lg text-center ">Thanks for your patronage</p>
        <div class="w-full text-center">
            <p class="px-6 py-1 mx-auto text-sm font-medium text-gray-600 bg-gray-100 rounded-sm max-w-max">BOOKING
                CODE: <span class="font-semibold text-black uppercase">{{ $booking->code }}</span></p>
            <p class="text-sm font-medium">Date: {{ $booking->created_at->format('d M, Y') }}</p>
        </div>
        <button onclick="window.print()"
            class="block px-2 py-1 mr-2 text-sm text-white bg-blue-600 rounded cursor-pointer max-w-max print:hidden">
            <i class="fa-solid fa-print"></i>
            print</button>
    </div>

</body>

</html>
