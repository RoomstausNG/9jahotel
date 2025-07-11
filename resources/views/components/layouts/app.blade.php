<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />

    <meta name="application-name" content="{{ config('app.name') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ config('app.name') }}</title>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    {{-- @livewireStyles --}}
    @filamentStyles
    @vite('resources/css/app.css')
</head>

<body class="antialiased">
    <div x-data="{ open: true }" class="flex min-h-screen">
        <aside class="bg-white border-r shadow h-screen transition-all duration-300 flex flex-col"
            :class="open ? 'w-56' : 'w-16'">
            <div class="flex items-center justify-between p-4 border-b">
                <span class="font-bold text-lg" x-show="open">Menu</span>
                <button @click="open = !open" class="focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
            <nav class="flex-1 py-4 space-y-2">
                <a href="/home"
                    class="flex items-center px-4 py-3 hover:bg-gray-100 transition group {{ request()->is('home') ? 'bg-primary-500/10 font-bold text-primary-500' : '' }}">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0H7m6 0v6m0 0h6m-6 0H7" />
                    </svg>
                    <span class="ml-3 text-gray-700" x-show="open">Dashboard</span>
                </a>
                {{-- <a href="/profile"
                    class="flex items-center px-4 py-3 hover:bg-gray-100 transition group {{ request()->is('profile') ? 'bg-primary-500/10 font-bold text-primary-500' : '' }}">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M5.121 17.804A9 9 0 1112 21a9 9 0 01-6.879-3.196z" />
                    </svg>
                    <span class="ml-3 text-gray-700" x-show="open">Profile</span>
                </a> --}}
                <a href="{{ route('bookings') }}"
                    class="flex items-center px-4 py-3 hover:bg-gray-100 transition group {{ request()->is('bookings') ? 'bg-primary-500/10 font-bold text-primary-500' : '' }}">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="ml-3 text-gray-700" x-show="open">Bookings</span>
                </a>
                <a href="/favorites"
                    class="flex items-center px-4 py-3 hover:bg-gray-100 transition group {{ request()->is('favorites') ? 'bg-primary-500/10 font-bold text-primary-500' : '' }}">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M5 15l7-7 7 7" />
                    </svg>
                    <span class="ml-3 text-gray-700" x-show="open">Favorites</span>
                </a>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="flex items-center px-4 py-3 text-red-600 hover:bg-gray-100 transition group">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
                    </svg>
                    <span class="ml-3" x-show="open">Logout</span>
                </a>
            </nav>
        </aside>
        <div class="flex-1 flex flex-col min-h-screen">
            <!-- Sticky Header -->
            <header class="sticky top-0 z-30 bg-white border-b flex items-center justify-between px-4 h-16 shadow-sm">
                <div class="flex items-center space-x-4">
                    <span class="text-xl font-bold">{{ config('app.name') }}</span>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Notifications Icon -->
                    <button class="relative focus:outline-none">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-0 right-0 inline-block w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    <!-- Profile Dropdown -->
                    <div x-data="{ show: false }" class="relative">
                        <button @click="show = !show" class="focus:outline-none flex items-center">
                            <img src="{{ auth('customers')->user()?->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth('customers')->user()?->name) }}"
                                alt="Profile" class="w-9 h-9 rounded-full border-2 border-gray-200 object-cover" />
                        </button>
                        <div x-show="show" @click.away="show = false" x-cloak
                            class="absolute right-0 mt-2 w-56 bg-white border rounded-lg shadow-lg py-2 z-40">
                            <div class="px-4 py-2 border-b">
                                <div class="font-semibold text-gray-800">{{ auth('customers')->user()?->name }}</div>
                                <div class="text-xs text-gray-500">Referral: <span
                                        class="font-mono">{{ auth('customers')->user()?->referral_code ?? 'N/A' }}</span>
                                </div>
                                <div class="text-xs text-gray-500">{{ auth('customers')->user()?->email }}</div>
                            </div>
                            <!-- Hidden logout form -->
                            <form id="logout-form" method="POST" action="{{ route('customer.logout') }}"
                                class="hidden">
                                @csrf
                            </form>
                            <a href="#"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                class="w-full flex items-center px-4 py-3 text-red-600 hover:bg-gray-100 transition">
                                <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            <main class="flex-1 px-6">
                {{ $slot }}
            </main>
        </div>
    </div>
    {{-- <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script> --}}
    {{-- @livewireScripts --}}
    @filamentScripts
    @vite('resources/js/app.js')
</body>

</html>
