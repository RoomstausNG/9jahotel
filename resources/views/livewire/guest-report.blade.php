{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ myHotel()->name }}</title>
    @vite(['resources/css/app.css', 'resources/scss/main.scss'])
</head>

<body class="flex flex-col justify-center pt-8 bg-gray-100 print:bg-none"> --}}
<div class="px-2 py-4 mx-auto space-y-3 bg-white rounded-sm border w-[900px] print:block">
    <h1 class="p-2 text-2xl font-bold text-center uppercase">{{ auth()->user()->hotel->name }}</h1>
    <div class="text-center ">
        {{-- <p>Tel: {{ auth()->user()->hotel->telephone }}</p>
        <p>Email: {{ auth()->user()->hotel->email }}</p>
        <p>Website: {{ config('app.url') }}</p> --}}
        <h2 class="text-2xl font-medium underline">Guest List as of {{ $date }}</h2>
    </div>
    <div
        class="mb-4 overflow-x-auto overflow-y-scroll bg-white border border-gray-200 rounded-sm shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <table id="" class="w-full overflow-x-auto overflow-y-auto bord">
            <thead class="w-full overflow-x-auto rounded">
                <tr class="w-full font-medium bg-gray-100 rounded">
                    {{-- <th class="p-4 font-medium text-left">
                            <input type="checkbox" value="" wire:model="all" class="select-input">
                        </th> --}}
                    {{-- <th class="p-4 font-medium text-left nowrap">Sales Code</th> --}}
                    <th class="p-4 font-medium text-left nowrap">No</th>
                    <th class="p-4 font-medium text-left nowrap">Guest Name</th>
                    <th class="p-4 font-medium text-left nowrap">Gender</th>
                    <th class="p-4 font-medium text-left nowrap">ID Card Type</th>
                    <th class="p-4 font-medium text-left nowrap">ID Number</th>
                    <th class="p-4 font-medium text-left nowrap">Room Number</th>
                    <th class="p-4 font-medium text-left nowrap">Checked in Date</th>
                    <th class="p-4 font-medium text-left nowrap">Checked Out Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    <tr class="border-b even:bg-gray-50">

                        <td class="px-4 py-2 text-sm font-bold uppercase break-keep nowrap">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-4 py-2 capitalize">
                            {{ $booking->customer->name }}
                        </td>
                        <td class="px-4 py-2 capitalize">
                            {{ $booking->customer->gender }}
                        </td>
                        <td class="px-4 py-2 capitalize">
                            {{ $booking->customer->id_type }}
                        </td>
                        <td class="px-4 py-2 capitalize">
                            {{ $booking->customer->id_number }}
                        </td>
                        <td class="px-4 py-2 uppercase">
                            {{ $booking->bookable->number }}
                        </td>
                        <td class="px-4 py-2">
                            {{ $booking->checked_in?->format('D d M, Y') }}
                        </td>
                        <td class="px-4 py-2">
                            {{ $booking->checked_out?->format('D d M, Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-4 text-center" colspan="8">No guest on this date</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <button onclick="window.print()"
        class="block px-2 py-1 mr-2 text-sm text-white bg-blue-600 rounded cursor-pointer max-w-max print:hidden">
        <i class="fa-solid fa-print"></i>
        print</button>
</div>

{{-- </body>

</html> --}}
