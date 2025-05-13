<x-layouts.frontend>
    <div class=" min-h-screen pb-16">
        <div class="w-full bg-primary-50  py-16 pb-24 md:pb-28">
            {{-- Top Section --}}
            <div class="flex max-w-5xl mx-auto px-4 flex-col md:flex-row items-center mb-12 md:mb-16">
                <div class="md:flex-1 mb-8 md:mb-0 md:pr-12">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">We're here to help 24/7</h1>
                    <p class="text-gray-600 text-lg">
                        Every guide is trained and excited to work with you, whether you need help with a password reset
                        or
                        you're having issue booking a room or hall.
                    </p>
                </div>
                <div class="max-w-[300px] md:w-[200px] lg:w-[250px]">
                    <img src="{{ asset('img/support.png') }}" alt="Customer Support"
                        class="rounded-lg w-full max-w-md mx-auto md:mx-0">
                </div>
            </div>
        </div>
        {{-- Bottom Section - Contact Info and Form --}}
        <div class="bg-white p-6 md:p-10 rounded-lg shadow-xl max-w-5xl mx-auto px-4 -mt-32">
            <div class="flex flex-col md:flex-row gap-8 md:gap-12 px-6 lg:px-0">

                {{-- Left Column: Get in Touch --}}
                <div class="md:w-1/2">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Get In Touch With Us Now!</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-center">
                        {{-- Phone Number --}}
                        <div
                            class="aspect-square border border-primary-600 p-4 flex flex-col gap-3 justify-center items-center">
                            <div class="flex items-center justify-center h-10 w-10 border rounded-full border-primary">
                                {{-- Placeholder for Phone Icon --}}
                                <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.308 1.154a11.034 11.034 0 005.37 5.37l1.154-2.308a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                    </path>
                                </svg>

                            </div>
                            <h3 class="text-lg font-medium text-primary-700">Phone Number</h3>
                            <p class="text-gray-600 text-sm">+234 811 111 0790</p>
                            <p class="text-gray-600 text-sm">+234 814 395 390</p>
                        </div>

                        {{-- Email --}}
                        <div
                            class="aspect-square border border-primary-600 p-4 flex flex-col gap-3 justify-center items-center">
                            <div class="flex items-center justify-center h-10 w-10 border rounded-full border-primary">
                                {{-- Placeholder for Email Icon --}}
                                <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>

                            </div>
                            <h3 class="text-lg font-medium text-primary-700">Email</h3>
                            <p class="text-gray-600 text-sm">ask@9jahotel.com</p>
                            <p class="text-gray-600 text-sm">corporate@9jahotel.com</p>
                        </div>

                        {{-- Location --}}
                        <div
                            class="aspect-square border border-primary-600 p-4 flex flex-col gap-3 justify-center items-center">
                            <div class="flex items-center justify-center h-10 w-10 border rounded-full border-primary">
                                {{-- Placeholder for Location Icon --}}
                                <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-primary-700">Location</h3>

                            <p class="text-gray-600 text-sm">24 1st Avenue Fort Royal<br>Estate Airport
                                Road,<br>Gosa,
                                Abuja.</p>
                        </div>

                        {{-- Opening Hours --}}
                        <div
                            class="aspect-square border border-primary-600 p-4 flex flex-col gap-3 justify-center items-center">
                            <div class="flex items-center justify-center h-10 w-10 border rounded-full border-primary">
                                {{-- Placeholder for Clock Icon --}}
                                <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>

                            </div>
                            <h3 class="text-lg font-medium text-primary-700">Opening Hours</h3>
                            <p class="text-gray-600 text-sm">Monday - Saturday</p>
                            <p class="text-gray-600 text-sm">9:00AM - 5:00PM</p>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Contact Form --}}
                <div class="md:w-1/2">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Contact Us</h2>
                    <form method="POST"> {{-- Update action and add CSRF token for Laravel --}}
                        @csrf {{-- Add CSRF token for Laravel forms --}}
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" id="name" placeholder="Enter Your Name"
                                class="w-full px-3 py-2 bg-primary-50 border border-primary-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone
                                Number</label>
                            <input type="tel" name="phone" id="phone" placeholder="Enter Phone number"
                                class="w-full px-3 py-2 bg-primary-50 border border-primary-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="email" placeholder="Enter Email"
                                class="w-full px-3 py-2 bg-primary-50 border border-primary-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div class="mb-6">
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                            <textarea name="message" id="message" rows="4" placeholder="Your Message"
                                class="w-full px-3 py-2 bg-primary-50 border border-primary-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"></textarea>
                        </div>
                        <div>
                            <button type="button"
                                class="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-4 rounded-md transition duration-150">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.frontend>
