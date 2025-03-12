<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="{{ old('code') }}viewport" content="width=device-width, initial-scale=1">

    <title>{{ env('APP_NAME') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    @php
        $admin = \App\Models\User::first();
    @endphp
    <div class="flex flex-col items-center justify-center w-screen space-y-4 overflow-y-auto bg-center bg-cover md:pt-24"
        style="background-image: url('/img/bg-one.jpg')">
        <div class="w-[95%] max-w-lg pt-24 mx-auto text-center lg:pt-0 lg:max-w-3xl 2xl:max-w-5xl">
            <div class="w-full p-4 space-y-6 text-white bg-black rounded-lg bg-opacity-80 lg:p-12 lg:space-y-8">
                <div class="space-y-3">
                    <img src="{{ asset('img/logo.png') }}" alt="{{ env('APP_NAME') }}"
                        class="block object-cover w-24 h-24 p-2 mx-auto bg-white rounded-full shadow">
                    <h1 class="text-2xl font-medium">Create an Account with us</h1>
                </div>
                @session('success')
                    <div class="px-4 py-2 text-green-600 bg-red-100 rounded-md">
                        {{ $value }}
                    </div>
                @endsession
                {{-- livewire creation of account --}}
                {{ $slot ?? '' }}
                {{-- @livewire('create-hotel') --}}
            </div>
        </div>
        <p class="p-1 px-4 text-center text-white bg-black rounded bg-opacity-80 max-w-max">Already have an Account <a
                href="{{ route('login') }}" class="font-semibold text-primary"> Login here</a>
        </p>
    </div>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
    <script>
        // window.addEventListener('swal:success', function(e) {
        //     Swal.fire(e.detail);
        // });
    </script>
</body>

</html>
