@extends('layouts.dashboard')
@section('content')
    <div class="w-full pb-12 space-y-4 bg-opacity-50 rounded-lg">
        <h1 class="text-2xl font-medium text-white md:text-3xl">Reports</h1>
        <div class="flex flex-wrap justify-center gap-8 p-8 mx-auto overflow-y-scroll mg:gap-12 xl:gap-16">
            <a href="{{ route('report.revenue') }}"
                class="flex flex-col space-y-3 font-medium justify-center text-lg text-center bg-white hover:bg-gray-100 w-full sm:w-1/2 md:w-[200px] p-4 rounded-lg shadow md:p-6">
                <img src="{{ asset('img/revenue_report.png') }}" alt="hotel" class="w-1/2 h-auto mx-auto">
                <p>Revenue</p>
            </a>
            {{-- <a href="{{ route('configuration.rooms') }}"
                class="flex flex-col space-y-3 font-medium justify-center text-lg text-center bg-white hover:bg-gray-100 w-full sm:w-1/2 md:w-[200px] p-4 rounded-lg shadow md:p-6">
                <img src="{{ asset('img/customer_report.png') }}" alt="hotel" class="w-1/2 h-auto mx-auto">
                <p>Customer</p>
            </a> --}}
            <a href="{{ route('report.finance') }}"
                class="flex flex-col space-y-3 font-medium justify-center text-lg text-center bg-white hover:bg-gray-100 w-full sm:w-1/2 md:w-[200px] p-4 rounded-lg shadow md:py-6">
                <img src="{{ asset('img/profitandloss_report.png') }}" alt="hotel" class="w-1/2 h-auto mx-auto">
                <p>Finance(Profit & Loss)</p>
            </a>
            <a href="{{ route('guest-list') }}"
                class="flex flex-col space-y-3 font-medium justify-center text-lg text-center bg-white hover:bg-gray-100 w-full sm:w-1/2 md:w-[200px] p-4 rounded-lg shadow md:py-6">
                <img src="{{ asset('img/guestlist.png') }}" alt="hotel" class="w-1/2 h-auto mx-auto">
                <p>Generate Guest List</p>
            </a>
            <a href="{{ route('payroll-report') }}"
                class="flex flex-col space-y-3 font-medium justify-center text-lg text-center bg-white hover:bg-gray-100 w-full sm:w-1/2 md:w-[200px] p-4 rounded-lg shadow md:py-6">
                <img src="{{ asset('img/payroll.png') }}" alt="hotel" class="w-1/2 h-auto mx-auto">
                <p>Generate Payroll Report</p>
            </a>
        </div>
    </div>
@endsection
