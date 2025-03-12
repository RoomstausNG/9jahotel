<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <title>9jahotels | Roomstatus</title>
    @filamentStyles
    @vite(['resources/css/app.css', 'resources/sass/main.scss'])
</head>

<body class="antialiased">
    <div class="fixed inset-0 flex flex-col items-center justify-center w-screen h-screen space-y-4 overflow-y-auto bg-center bg-cover backG"
        style="background-image: url('img/bg-one.jpg')">
        <div class="w-4/5 max-w-md mx-auto overflow-y-auto text-center lg:max-w-lg">
            <div class="w-full p-8 space-y-6 text-white bg-black bg-opacity-50 rounded-lg lg:p-12 lg:space-y-8">
                <div class="space-y-3">
                    <img src="{{ asset('img/logo.png') }}" alt="{{ env('APP_NAME') }}"
                        class="block object-cover w-24 h-24 p-2 mx-auto bg-white rounded-full shadow">
                    <h1 class="text-2xl font-medium">Customers Authentication</h1>
                </div>
                {{ $slot }}
            </div>
        </div>
        <p class="p-1 px-4 text-center text-white bg-black rounded bg-opacity-70 max-w-max lg:max-w-lg"> New to
            9jaHotel? <a href="/register" class="font-semibold text-primary hover:text-green-500">
                Create an
                account</a>
        </p>
    </div>



    @livewire('subscribe')


    @filamentScripts
    @vite(['resources/js/app.js'])

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <x-livewire-alert::scripts />
    @stack('scripts')
</body>

</html>
