<div class="hidden space-x-3 lg:flex">
    {{-- <a href="/home"
        class="flex items-center p-1 px-2 space-x-2 font-medium text-center {{ request()->is('home') ? 'bg-primary text-white' : 'bg-white' }} border border-gray-300 rounded-md shadow hover:bg-primary hover:text-white">
        <img src="{{ asset('img/history-removebg-preview.png') }}" alt="{{ env('APP_NAME') }}" class="w-8 2xl:w-10">
        <span>Dashboard</span> </a> --}}

    @can('read reservation')
        <a href="{{ route('reservations') }}"
            class="flex items-center p-1 px-2 space-x-2 font-medium {{ request()->is('bookings/reservations') ? 'bg-primary text-white' : 'bg-white' }} text-center border border-gray-300 rounded-md shadow hover:bg-primary hover:text-white">
            <img src="{{ asset('img/reservation-removebg-preview.png') }}" alt="{{ env('APP_NAME') }}" class="w-8 2xl:w-10">
            <span> Reservation</span>
        </a>
    @endcan

    @can('read report')
        <a href="{{ route('report') }}"
            class="flex items-center p-1 px-2 space-x-2 font-medium text-center {{ request()->is('reports') || request()->is('reports/*') ? 'bg-primary text-white' : 'bg-white' }} border border-gray-300 rounded-md shadow hover:bg-primary hover:text-white">
            <img src="{{ asset('img/report-removebg-preview.png') }}" alt="{{ env('APP_NAME') }}" class="w-8 2xl:w-10">

            <span> Report</span>
        </a>
    @endcan

    @can('read configuration')
        <a href="{{ route('configuration') }}"
            class="flex items-center p-1 px-2 {{ request()->is('configurations') || request()->is('configurations/*') ? 'bg-primary text-white' : 'bg-white' }} space-x-2 font-medium text-center border border-gray-300 rounded-md shadow hover:bg-primary hover:text-white">
            <img src="{{ asset('img/configuration-removebg-preview.png') }}" alt="{{ env('APP_NAME') }}"
                class="w-8 2xl:w-10">
            <span>
                Configuration</span>
        </a>
    @endcan
    @can('read sales')
        <a href="{{ route('sales') }}"
            class="flex items-center p-1 px-2 {{ request()->is('bookings/sales') ? 'bg-primary text-white' : 'bg-white' }} space-x-2 font-medium text-center border border-gray-300 rounded-md shadow hover:bg-primary hover:text-white">
            <img src="{{ asset('img/pos-removebg-preview.png') }}" alt="{{ env('APP_NAME') }}" class="w-8 2xl:w-10">
            <span>Point of
                Sales</span>
        </a>
    @endcan
    <a href="{{ route('supplies') }}"
        class="flex items-center p-1 px-2 {{ request()->is('supplies') ? 'bg-primary text-white' : 'bg-white' }} space-x-2 font-medium text-center border border-gray-300 rounded-md shadow hover:bg-primary hover:text-white">
        <img src="{{ asset('img/supplies.png') }}" alt="{{ env('APP_NAME') }}" class="w-8 2xl:w-10">
        <span>Hotel Supplies</span>
    </a>
</div>
