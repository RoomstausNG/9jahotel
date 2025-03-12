<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="{{ old('code') }}viewport" content="width=device-width, initial-scale=1">

    <title>{{ env('APP_NAME') }}</title>
    @vite('resources/css/app.css')
    @filamentStyles

    @stack('styles')
</head>

<body>
    <div class="flex flex-col items-center justify-center w-screen h-screen space-y-4 overflow-y-auto bg-center bg-cover md:pt-24"
        style="background-image: url('/img/bg-one.jpg')">
        <div class="w-full max-w-3xl p-4 mx-auto text-white bg-black bg-opacity-50 rounded-lg lg:p-8 lg:space-y-4">
            <h1 class="text-3xl font-medium text-center">Register your Hotel</h1>
            {{ $slot }}
        </div>
    </div>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @livewire('notifications')

    @filamentScripts
    @vite('resources/js/app.js')

    @yield('scripts')
    @stack('scripts')
</body>

</html>
