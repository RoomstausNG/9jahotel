<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />

    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <title>9jahotels | Roomstatus</title>

    @filamentStyles
    @vite(['resources/css/app.css', 'resources/sass/main.scss'])
</head>

<body class="antialiased">


    <nav class="bg-white border-b border-gray-200 ">
        <div class="flex flex-wrap items-center justify-between max-w-screen-xl px-4 py-2 mx-auto">
            <a href="/" class="flex items-center space-x-3 rtl:space-x-reverse">
                <img src="{{ asset('img/9jahotel_logo.png') }}" class="w-auto h-12 md:h-16"
                    alt="{{ env('APP_NAME') }}" />
            </a>
            <button data-collapse-toggle="navbar-dropdown" type="button"
                class="inline-flex items-center justify-center w-10 h-10 p-2 text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                aria-controls="navbar-dropdown" aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M1 1h15M1 7h15M1 13h15" />
                </svg>
            </button>
            <div class="hidden w-full md:block md:w-auto" id="navbar-dropdown">
                <ul
                    class="flex flex-col items-center px-4 py-2 mt-4 font-medium border border-gray-100 rounded-lg md:p-0 bg-gray-50 md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
                    {{-- <li>
                        <a href="/"
                            class="block px-3 py-2 rounded text-primary-DARK md:bg-transparent md:p-0 md:dark:text-blue-500 md:dark:bg-transparent"
                            aria-current="page">List your property</a>
                    </li> --}}
                    {{-- <li>
                        <button id="dropdownNavbarLink" data-dropdown-toggle="dropdownNavbar"
                            class="flex items-center justify-between w-full px-3 py-2 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:p-0 md:w-auto dark:00 md:dark:hover:bg-transparent">Dropdown
                            <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg></button>
                        <!-- Dropdown menu -->
                        <div id="dropdownNavbar"
                            class="z-10 hidden font-normal bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700 dark:divide-gray-600">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-400"
                                aria-labelledby="dropdownLargeButton">
                                <li>
                                    <a href="#"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Dashboard</a>
                                </li>
                                <li>
                                    <a href="#"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Settings</a>
                                </li>
                                <li>
                                    <a href="#"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Earnings</a>
                                </li>
                            </ul>
                            <div class="py-1">
                                <a href="#"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Sign
                                    out</a>
                            </div>
                        </div>
                    </li> --}}
                    {{-- <li>
                        <a href="#"
                            class="block px-3 py-2 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:p-0 dark:text-white">Support</a>
                    </li> --}}
                    <li>
                        <a href="#" target="_blank"
                            class="flex items-center gap-2 px-3 py-2 text-sm text-primary rounded-full border-primary hover:bg-primary-100 border">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m9 12.75 3 3m0 0 3-3m-3 3v-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>

                            </span>
                            <span> Download
                                App</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('support') }}"
                            class="block px-3 py-2 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:p-0 dark:text-white">Support</a>
                    </li>
                    <li>
                        @auth('customers')
                            <a href="/home"
                                class="block px-3 py-2 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:p-0 dark:text-white {{ request()->is('home') ? 'bg-gray-200 font-bold text-primary-700' : '' }}">
                                Dashboard
                            </a>
                        @else
                            <a href="/login"
                                class="block px-3 py-2 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:p-0 dark:text-white {{ request()->is('login') ? 'bg-gray-200 font-bold text-primary-700' : '' }}">
                                Sign in
                            </a>
                        @endauth
                    </li>
                    <li class="">
                        <a href="{{ route('hotel.create') }}"
                            class="block px-3 py-2 text-white rounded bg-primary hover:bg-primary-700">List your
                            property</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{ $slot }}

    @livewire('subscribe')
    <x-footer />

    @livewire('notifications')

    @filamentScripts
    @vite(['resources/js/app.js'])

    <p class="bg-[#00FF00]"></p>
    <p class="bg-[#ADFF2F]"></p>
    <p class="bg-[#FFFF00]"></p>
    <p class="bg-[#FFA500]"></p>
    <p class="bg-[#FF0000]"></p>
    <p class="bg-[#808080]"></p>
    <p class="bg-[#A5FF00]"></p>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <x-livewire-alert::scripts />
    @stack('scripts')

    {{-- <script>
        const words = [
            "Amazing"
            "Fantastic",
            "Great",
            "Secret",
            "Excellent",
            "Unbelievable",
            "Last Minute",
            "Discounted",
            "Special"
        ];

        let wordIndex = 0;
        let charIndex = 0;
        let isDeleting = false;
        let text = '';

        const typeSpeed = 100; // typing speed in milliseconds
        const eraseSpeed = 50; // erasing speed in milliseconds
        const pauseDelay = 1000; // delay after word is fully typed

        function typeWriter() {
            const currentWord = words[wordIndex];

            // Determine the current text based on whether we're typing or deleting
            if (isDeleting) {
                // Remove a character
                text = currentWord.substring(0, charIndex - 1);
                charIndex--;
            } else {
                // Add a character
                text = currentWord.substring(0, charIndex + 1);
                charIndex++;
            }

            // Update the HTML element
            document.getElementById("type").textContent = text;

            // Set the typing speed
            let typeDelay = isDeleting ? eraseSpeed : typeSpeed;

            // Check if word is complete
            if (!isDeleting && charIndex === currentWord.length) {
                // Pause at the end of typing a complete word
                typeDelay = pauseDelay;
                isDeleting = true;
            } else if (isDeleting && charIndex === 0) {
                // Move to next word after deletion is complete
                isDeleting = false;
                wordIndex = (wordIndex + 1) % words.length; // Loop back to first word after last word
            }

            // Call the function again after the delay
            setTimeout(typeWriter, typeDelay);
        }

        // Start the typing effect when the page loads
        window.onload = typeWriter;
    </script> --}}
</body>

</html>
