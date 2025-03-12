@extends('layouts.dashboard')
@section('content')
    <div class="w-full pb-12 mx-auto space-y-4 bg-opacity-50 rounded-lg max-w-7xl">
        <h1 class="text-2xl font-medium text-white md:text-3xl">Configurations</h1>
        <div class="flex flex-wrap justify-center gap-8 p-8 mx-auto overflow-y-scroll mg:gap-12 xl:gap-16">

            @can('read hotel')
                <a href="{{ route('configuration.hotel') }}"
                    class="flex flex-col space-y-3 font-medium justify-center text-lg text-center bg-white hover:bg-gray-100 w-full sm:w-1/2 md:w-[200px] p-4 rounded-lg shadow md:p-6">
                    <img src="{{ asset('img/hotel.jpeg') }}" alt="hotel" class="w-1/2 h-auto mx-auto">
                    <p>Hotel Setup</p>
                </a>
            @endcan

            @can('read user')
                <a href="{{ route('configuration.users') }}"
                    class="flex flex-col space-y-3 font-medium justify-center text-lg text-center bg-white hover:bg-gray-100 w-full sm:w-1/2 md:w-[200px] p-4 rounded-lg shadow md:p-6">
                    <img src="{{ asset('img/users.jpeg') }}" alt="hotel" class="w-1/2 h-auto mx-auto">
                    <p>User Accounts</p>
                </a>
            @endcan
            @can('read item')
                <a href="{{ route('configuration.items') }}"
                    class="flex flex-col space-y-3 font-medium justify-center text-lg text-center bg-white hover:bg-gray-100 w-full sm:w-1/2 md:w-[200px] p-4 rounded-lg shadow md:p-6">
                    <img src="{{ asset('img/food.jpeg') }}" alt="hotel" class="w-1/2 h-auto mx-auto">
                    <p>Revenue Item</p>
                </a>
            @endcan

            @can('read room')
                <a href="{{ route('configuration.rooms') }}"
                    class="flex flex-col space-y-3 font-medium justify-center text-lg text-center bg-white hover:bg-gray-100 w-full sm:w-1/2 md:w-[200px] p-4 rounded-lg shadow md:p-6">
                    <img src="{{ asset('img/room.jpeg') }}" alt="hotel" class="w-1/2 h-auto mx-auto">
                    <p>Hotel Room Setup</p>
                </a>
            @endcan
            @can('read hall')
                <a href="{{ route('configuration.halls') }}"
                    class="flex flex-col space-y-3 font-medium justify-center text-lg text-center bg-white hover:bg-gray-100 w-full sm:w-1/2 md:w-[200px] p-4 rounded-lg shadow md:p-6">
                    <img src="{{ asset('img/hall.png') }}" alt="hotel" class="w-1/2 h-auto mx-auto">
                    <p>Hall Setup</p>
                </a>
            @endcan
            @can('read business')
                <a href="{{ route('configuration.business') }}"
                    class="flex flex-col space-y-3 font-medium justify-center text-lg text-center bg-white hover:bg-gray-100 w-full sm:w-1/2 md:w-[200px] p-4 rounded-lg shadow md:p-6">
                    <img src="{{ asset('img/credit.jpeg') }}" alt="hotel" class="w-1/2 h-auto mx-auto">
                    <p>Corporate Account setup</p>
                </a>
            @endcan

            @can('read customer')
                <a href="{{ route('configuration.customers') }}"
                    class="flex flex-col space-y-3 font-medium justify-center text-lg text-center bg-white hover:bg-gray-100 w-full sm:w-1/2 md:w-[200px] p-4 rounded-lg shadow md:p-6">
                    <img src="{{ asset('img/customer.jpeg') }}" alt="hotel" class="w-1/2 h-auto mx-auto">
                    <p>Customers setup</p>
                </a>
            @endcan
            @can('create finance')
                <a href="{{ route('configuration.expenditures') }}"
                    class="flex flex-col space-y-3 font-medium justify-center text-lg text-center bg-white hover:bg-gray-100 w-full sm:w-1/2 md:w-[200px] p-4 rounded-lg shadow md:p-6">
                    <img src="{{ asset('img/expenditure.png') }}" alt="hotel" class="w-1/2 h-auto mx-auto">
                    <p>Hotel Expenditure Setup</p>
                </a>
            @endcan
        </div>
    </div>
@endsection
