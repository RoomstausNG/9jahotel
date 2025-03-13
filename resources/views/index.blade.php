<x-layouts.frontend>
    {{-- linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), --}}
    <div class="w-full flex items-center justify-center bg-cover bg-center px-4 py-4 min-h-[300px] max-h-[480px]"
        style="width: 100%; height: 100vh; background:  url('/img/background.jpeg'); background-size: cover; background-position: center; display: flex; justify-content: center; align-items: center;">
        <div x-data="{ tab: 'room' }" class="w-full max-w-screen-xl px-4 mx-auto">
            <div class="text-center py-16">
                <h1 class="text-2xl font-medium">
                    <span class="font-black italic text-2xl">Amazing</span>
                    <span>Hotels Deals just for You!</span>
                </h1>
            </div>
            <div class="flex max-w-max bg-white">
                <button :class="[tab == 'room' ? 'bg-primary-500 text-white font-medium' : 'bg-white ']"
                    @click="tab = 'room'" class="px-8 md:px-12 py-3 inline-block hover:bg-primary">Room</button>
                <button @click="tab = 'hall'" class="px-8 md:px-12 py-3 inline-block border-x hover:bg-primary"
                    :class="[tab == 'hall' ? 'bg-primary-500 text-white font-medium' : 'bg-white ']">Event
                    Halls</button>
                <a href="group-booking" class="px-8  md:px-12 py-3 inline-block hover:bg-primary">Group
                    Bookings</a>
            </div>
            <div class="w-full">
                @livewire('search-city')
            </div>
        </div>
    </div>
    <div class="w-full max-w-screen-xl p-4 mx-auto space-y-12 md:space-y-16">

        <section
            class="flex flex-col items-center w-full gap-8 p-4 text-white bg-primary-500 md:p-6 lg:p-8 rounded-2xl lg:justify-between md:flex-row">
            <div>
                <h1 class="text-xl font-medium md:text-2xl lg:text-3xl md:font-semibold max-w-[300px]">Reserve your
                    specific
                    hotelroom, Pay less
                </h1>
            </div>
            <div
                class="flex w-full md:w-1/2 lg:min-w-[70%] items-center justify-between space-x-4 overflow-x-auto scroll">
                <div
                    class="w-3/5 min-w-[250px] space-x-4 lg:max-w-[320px] h-[140px] bg-primary-800 rounded-xl px-4 items-center flex justify-between">
                    <span>
                        <svg class="h-12 text-white" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                            <path
                                d="M223.5 32C100 32 0 132.3 0 256S100 480 223.5 480c60.6 0 115.5-24.2 155.8-63.4c5-4.9 6.3-12.5 3.1-18.7s-10.1-9.7-17-8.5c-9.8 1.7-19.8 2.6-30.1 2.6c-96.9 0-175.5-78.8-175.5-176c0-65.8 36-123.1 89.3-153.3c6.1-3.5 9.2-10.5 7.7-17.3s-7.3-11.9-14.3-12.5c-6.3-.5-12.6-.8-19-.8z" />
                        </svg>
                    </span>
                    <p>Gain points for each night you stay.</p>
                </div>
                <div
                    class="w-3/5 min-w-[250px] space-x-4 lg:max-w-[320px] h-[140px] bg-primary-800 rounded-xl px-4 items-center flex justify-between">
                    <span class="">
                        <svg class="h-10" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 640 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                            <path
                                d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c10 0 18.8-4.9 24.2-12.5l-99.2-99.2c-14.9-14.9-23.3-35.1-23.3-56.1v-33c-15.9-4.7-32.8-7.2-50.3-7.2H178.3zM384 224c-17.7 0-32 14.3-32 32v82.7c0 17 6.7 33.3 18.7 45.3L478.1 491.3c18.7 18.7 49.1 18.7 67.9 0l73.4-73.4c18.7-18.7 18.7-49.1 0-67.9L512 242.7c-12-12-28.3-18.7-45.3-18.7H384zm24 80a24 24 0 1 1 48 0 24 24 0 1 1 -48 0z" />
                        </svg>
                    </span>
                    <p>Save upto
                        25% when you use <span class="font-bold text-red-600">Book-Me-Now</span> option</p>
                </div>
                <div
                    class="w-3/5 min-w-[250px] space-x-4 lg:max-w-[320px] h-[140px] bg-primary-800 rounded-xl px-4 items-center flex justify-between">
                    <span class="">
                        <svg class="h-10" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                            <path
                                d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zm64 80v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm128 0v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H208c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H336zM64 400v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H208zm112 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H336c-8.8 0-16 7.2-16 16z" />
                        </svg>
                    </span>
                    <p>Flexible cancellation options in case your plans alter..</p>
                </div>
        </section>

        <div class="">
            <h1 class="text-2xl font-medium text-primary-DARK">Top Rated Hotels</h1>
            <section
                class="flex w-full gap-8 py-4 space-x-2 overflow-x-auto text-white md:py-6 rounded-2xl lg:justify-between">
                @for ($i = 0; $i < 10; $i++)
                    <div
                        class="overflow-hidden relative h-[300px] shadow rounded-lg min-w-[60%] md:min-w-[30%] lg:min-w-[17%] md:max-w-[250px]">
                        <img src="{{ asset('img/bg-one.jpg') }}" alt="{{ env('APP_NAME') }}"
                            class="block object-cover w-auto h-full">
                        <div
                            class="absolute bottom-0 left-0 right-0 w-full px-4 pt-4 pb-2 text-white bg-opacity-50 bg-primary-DARK">
                            <p class="p-0 m-0 font-medium text-primary-50 " style="line-height: 4px">Alubarika hotel
                                4.0+
                            </p>
                            <small class="text-sm">Lagos</small>
                        </div>
                    </div>
                @endfor
            </section>
        </div>

        {{-- hotels --}}
        <section
            class="flex flex-col items-center w-full gap-8 p-4 md:p-6 lg:p-8 rounded-2xl lg:justify-between md:flex-row">
            @livewire('hotel-cities')
        </section>

        {{-- app decotations --}}

    </div>
    <div class="w-full pt-16 bg-primary-50">
        <section
            class="flex flex-col justify-center w-full max-w-screen-md gap-8 mx-auto lg:flex-row-reverse rounded-2xl lg:justify-between">

            <div class="flex flex-col items-start justify-center w-full py-4 space-y-3 lg:justify-start lg:pl-12">
                <h1 class="text-4xl font-bold md:text-5xl lg:text-7xl md:font-extrabold text-primary">Get the <br>
                    9jahotel app</h1>
                <p
                    class="text-lg font-semibold max-w-[300px] lg:max-w-[400px] text-gray-700 md:font-bold md:text-2xl lg:text-3xl">
                    Download the
                    9jahotel app and
                    book a hotel
                    instantly</p>
                <p class="text-lg">Book your hotel instantly with our Android App.</p>
                <div class="flex items-center gap-4">
                    <a href="" class="">
                        <img src="{{ asset('img/android.jpeg') }}" alt="9jahotel android app"
                            class="w-auto h-10 lg:h-14">
                    </a>
                    <a href="" class="">
                        <img src="{{ asset('img/apple.jpeg') }}" alt="9jahotel io app" class="w-auto h-10 lg:h-14">
                    </a>
                </div>
            </div>
            <img src="" alt="" class="w-full lg:w-1/2">
        </section>
    </div>
</x-layouts.frontend>
