<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
    @vite(['resources/css/app.css', 'resources/scss/main.scss', 'resources/js/app.js', 'resources/js/activity.js'])

    <title>Roomstatus | Hotel system</title>
    {{-- @livewireStyles --}}
    <style>
        [x-cloak] {
            display: none;
        }

        a.hover,
        a.underline {
            color: black !important;
            text-decoration: underline !important;
        }
    </style>
    {{-- @livewireStyles --}}
    @stack('styles')
</head>

<body x-data="{ mobile: false }" class="relative min-h-screen overflow-y-auto bg-cover"
    style="background-image: url('/img/bg-one.jpg')">
    <nav class="fixed z-30 w-full bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start">
                    <button @click="mobile = !mobile" id="toggleSidebarMobile" aria-expanded="true"
                        aria-controls="sidebar"
                        class="p-2 text-gray-600 rounded cursor-pointer lg:hidden hover:text-gray-900 hover:bg-primary focus:bg-gray-100 dark:focus:bg-gray-700 focus:ring-2 focus:ring-gray-100 dark:focus:ring-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                        <svg id="toggleSidebarMobileHamburger" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <svg id="toggleSidebarMobileClose" class="hidden w-6 h-6" fill="currentColor"
                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="flex items-center ml-2 md:mr-24">
                        <img src="{{ myLogo() }}" class="object-cover h-12 mr-3" alt="{{ config('app.name') }}" />
                        <span class="hidden text-2xl font-bold md:inline-block">{{ currentUser()->hotel->name }}</span>
                    </a>
                    <x-main-narbar />

                </div>
                <div class="flex items-center">
                    <button id="toggleSidebarMobileSearch" type="button"
                        class="p-2 text-gray-500 rounded-lg lg:hidden hover:text-gray-900 hover:bg-primary dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                        <span class="sr-only">Search</span>

                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <button type="button" data-dropdown-toggle="notification-dropdown"
                        class="relative p-2 text-gray-500 rounded-lg hover:text-gray-900 hover:bg-primary dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700">
                        <span class="sr-only">View notifications</span>

                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z">
                            </path>
                        </svg>
                        {{-- <span
                            class="absolute right-0 px-1 text-xs text-white rounded-full bg-primary top-1">{{ currentUser()->unreadNotifications->count() }}</span> --}}
                    </button>

                    {{-- <div class="z-20 z-50 hidden max-w-sm my-4 overflow-hidden text-base list-none bg-white divide-y divide-gray-100 rounded shadow-lg dark:divide-gray-600 dark:bg-gray-700"
                        id="notification-dropdown">
                        <div
                            class="block px-4 py-2 text-base font-medium text-center text-gray-700 bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            Notifications
                        </div>
                        <div class="max-h-[400px] overflow-y-auto">
                            @forelse (currentUser()->unreadNotifications as $notification)
                                <a href="#"
                                    class="flex px-4 py-3 border-b hover:bg-primary hover:text-white dark:border-gray-600">
                                    <div class="flex-shrink-0">
                                        <i class="text-gray-400 fa-regular fa-2x fa-bell"></i>
                                    </div>
                                    <div class="w-full pl-3">
                                        <div
                                            class="text-gray-500 hover:text-white font-normal text-sm mb-1.5 dark:text-gray-400">
                                            {!! $notification->data[0] !!}
                                        </div>
                                        <div class="text-xs font-medium text-primary-700 dark:text-primary-400">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </a>

                            @empty
                                <p class="w-full p-2 text-sm text-center">No message</p>
                            @endforelse
                        </div>
                        <a href="{{ route('notifications') }}"
                            class="block py-2 text-base font-normal text-center text-gray-900 bg-gray-50 hover:bg-primary dark:bg-gray-700 dark:text-white dark:hover:underline">
                            <div class="inline-flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                    <path fill-rule="evenodd"
                                        d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                View all
                            </div>
                        </a>
                    </div> --}}

                    <button type="button" data-dropdown-toggle="apps-dropdown"
                        class="hidden p-2 text-gray-500 rounded-lg sm:flex hover:text-gray-900 hover:bg-primary dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700">
                        <span class="sr-only">View notifications</span>

                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                            </path>
                        </svg>
                    </button>
                    <button id="theme-toggle" data-tooltip-target="tooltip-toggle" type="button"
                        class="text-gray-500 dark:text-gray-400 hover:bg-primary dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div id="tooltip-toggle" role="tooltip"
                        class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip">
                        Toggle dark mode
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>

                    <div class="flex items-center ml-3">
                        <div>
                            <button type="button"
                                class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
                                id="user-menu-button-2" aria-expanded="false" data-dropdown-toggle="dropdown-2">
                                <span class="sr-only">Open user menu</span>
                                <img class="object-cover w-12 h-12 bg-white rounded-full aspect-square"
                                    src="{{ profilePicture(auth()->user()) }}" alt="{{ auth()->user()->username }}" />
                            </button>
                        </div>

                        <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded shadow dark:bg-gray-700 dark:divide-gray-600"
                            id="dropdown-2">
                            <div class="px-4 py-3" role="none">
                                <p class="text-sm text-gray-900 dark:text-white" role="none">
                                    {{ auth()->user()->username }}
                                </p>
                                <p class="text-sm font-medium text-gray-900 truncate dark:text-gray-300"
                                    role="none">
                                    {{ auth()->user()->email }}
                                </p>
                                <p class="text-sm font-medium text-gray-900 uppercase truncate dark:text-gray-300"
                                    role="none">
                                    {{ auth()->user()->current_role }}
                                </p>
                            </div>
                            <ul class="p-1" role="none">
                                <li>
                                    <a href="{{ route('dashboard') }}"
                                        class="block px-4 py-2 text-sm {{ request()->is('*/dashbaord') || request()->is('home') ? 'bg-primary text-white' : 'text-gray-700' }}  hover:text-white hover:bg-primary dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white"
                                        role="menuitem">Dashboard</a>
                                </li>
                                {{-- <li>
                                    <a href="{{ route('switch') }}"
                                        class="block px-4 py-2 text-sm {{ request()->is('switch-roles') ? 'bg-primary text-white' : 'text-gray-700' }}  hover:text-white hover:bg-primary dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white"
                                        role="menuitem">Change Role</a>
                                </li> --}}
                                <li>
                                    <a href="{{ route('invoice.index') }}"
                                        class="block px-4 py-2 text-sm {{ request()->is('invoices') ? 'bg-primary text-white' : 'text-gray-700' }}  hover:text-white hover:bg-primary dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white"
                                        role="menuitem">Invoice</a>
                                </li>

                                <li>
                                    <a href="#"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white"
                                        role="menuitem">Sign out</a>
                                </li>

                                <form class="hidden logout-form" id="logout-form" action="{{ route('logout') }}"
                                    method="POST">
                                    @csrf
                                </form>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div class="relative w-full pb-32 mx-auto bg-cover">
        <main class="bg-center bg-cover">
            <div class="h-36 md:h-44 lg:h-48 full bg-primary">

            </div>
            <div class="w-full px-4 md:px-6">
                <div class="-mt-16 rounded-lg">
                    @yield('content')
                    {{ $slot ?? '' }}
                </div>
            </div>
        </main>
    </div>

    {{-- footer --}}

    <div class="absolute bottom-0 left-0 right-0 w-full bg-opacity-90 bg-primary">
        {{-- whatsapp icons --}}
        <a href="https://wa.me/+2348092696218"
            class="fixed z-50 block w-16 h-16 p-2 text-green-500 bg-white rounded-full aspect-square left-2 bottom-4 lg:left-4 lg:bottom-8">
            <span class="w-full">
                <i class="w-full mx-auto fa-brands fa-3x lg:fa-4x fa-whatsapp"></i>
            </span>
        </a>
        {{-- <x-status-component :date="$date/> --}}
        <div class="py-3 text-sm text-center text-white bg-black">
            &copy; 2023-{{ date('Y') }}
            <a href="https://bowofade.com" class="font-semibold text-primary hover:underline"
                target="_blank">{{ env('APP_NAME') }}</a>.
            All rights
            reserved.
        </div>
    </div>

    {{-- mobile navigation --}}

    <div x-cloak x-show="mobile" class="absolute inset-0 z-50 w-screen h-screen p-2 pt-8 m-0 bg-black bg-opacity-90">
        <x-mobile-nav />
        <span @click="mobile = false" class="absolute top-0 right-0 m-4 text-white cursor-pointer">
            <i class="fa-solid fa-times fa-2x"></i>
        </span>
    </div>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <x-livewire-alert::scripts />



    <p class="bg-available "></p>
    <p class="bg-pending"></p>
    <p class="bg-unpaid"></p>
    <p class="bg-paid"></p>
    <p class="bg-occupied"></p>
    <p class="bg-late"></p>
    <p class="bg-dirty"></p>
    <p class="bg-repair"></p>
    <p class="bg-Available "></p>
    <p class="bg-Pending"></p>
    <p class="bg-Unpaid"></p>
    <p class="bg-Paid"></p>
    <p class="bg-Occupied"></p>
    <p class="bg-Late"></p>
    <p class="bg-Dirty"></p>
    <p class="bg-Repair"></p>
    <p class="bg-income"></p>
    <p class="bg-expenditure"></p>
    <p class="bg-booked"></p>

    {{-- paystack --}}

    {{-- @livewireScripts --}}
    {{-- <script src="https://js.paystack.co/v1/inline.js"></script> --}}

    @stack('scripts')
    <script>
        window.on('swal:success', function(e) {
            Swal.fire(e.detail);
        });

        // confirm single deleted
        window.addEventListener('swal:confirm', event => {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won\'t be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#f11',
                confirmButtonColor: ''
                confirmButtonText: 'Yes delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('deleteConfirm');
                    // Swal.fire(
                    //   'Deleted!'
                    //   , 'Your file has been deleted'
                    //   , 'success'
                    // )
                }
            });
        });

        // confirm multiple delete
        window.addEventListener('swal:multiple', event => {
            Swal.fire({
                title: 'Are you sure you?',
                text: "You are deleting buck records at once, you won\'t be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#f11',
                confirmButtonText: 'Go Ahead'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emit('deleteMutipleConfirm');
                    // Swal.fire(
                    //   'Deleted!'
                    //   , 'Your file has been deleted'
                    //   , 'success'
                    // )
                }
            });
        });
    </script>
    <script>
        // JavaScript function to go back to the previous page
        function goBack() {
            window.history.back();
        }
    </script>
    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
        var Tawk_API = Tawk_API || {},
            Tawk_LoadStart = new Date();
        (function() {
            var s1 = document.createElement("script"),
                s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/68346ce043a2a8190b59fb5b/1jn0bmh6d';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>
    <!--End of Tawk.to Script-->
</body>

</html>
