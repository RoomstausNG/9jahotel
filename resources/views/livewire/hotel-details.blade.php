<div class="relative w-full p-0 mx-auto" x-data="{ review: false, modal: @entangle('modal').live }">
    @php
        $amenities = $hotel->amenities;
        $faqs = $hotel->faqs;
    @endphp
    <div class="grid w-full max-w-screen-lg px-4 py-4 mx-auto space-y-8 rounded-lg">
        {{-- back button --}}
        <div class="w-full">
            <a href="{{ route('hotels.result', $hotel->city->id) }}"
                class="px-6 py-2 text-white bg-gray-600 rounded">Back</a>
        </div>
        {{-- @livewire('search-city') --}}
        <div id="default-carousel" class="relative w-full" data-carousel="slide">
            <!-- Carousel wrapper -->
            <div class="relative h-56 overflow-hidden rounded-lg md:h-96 lg:h-[450px]">
                <!-- Item 1 -->
                {{-- @foreach ($hotel->images as $image)
                    <div class="duration-700 ease-in-out ">
                        <img src="{{ getCloudinaryImage($image->url) }}"
                            class="absolute block object-cover w-full h-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2"
                            alt="{{ $hotel->name . ' 9jahotel' }}">


                    </div>
                @endforeach --}}
                <img src="{{ getCloudinaryImage($hotel->background_image) }}"
                    class="absolute block object-cover w-full h-full -translate-x-1/2 -translate-y-1/2 border top-1/2 left-1/2"
                    alt="{{ $hotel->name . ' 9jahotel' }}">

            </div>
            <!-- Slider indicators -->
            <div class="absolute z-30 flex space-x-3 -translate-x-1/2 bottom-5 left-1/2 rtl:space-x-reverse">
                @for ($i = 0; $i < $hotel->images?->count(); $i++)
                    <button type="button" class="w-3 h-3 rounded-full" aria-current="true"
                        aria-label="Slide {{ $i + 1 }}" data-carousel-slide-to="{{ $i }}"></button>
                @endfor
            </div>
            <!-- Slider controls -->
            <button type="button"
                class="absolute top-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer start-0 group focus:outline-none"
                data-carousel-prev>
                <span
                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/80 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none">
                    <svg class="w-4 h-4 text-primary dark:text-gray-800 rtl:rotate-180" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 1 1 5l4 4" />
                    </svg>
                    <span class="sr-only">Previous</span>
                </span>
            </button>
            <button type="button"
                class="absolute top-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer end-0 group focus:outline-none"
                data-carousel-next>
                <span
                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/80 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none">
                    <svg class="w-4 h-4 text-primary dark:text-gray-800 rtl:rotate-180" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 9 4-4-4-4" />
                    </svg>
                    <span class="sr-only">Next</span>
                </span>
            </button>
        </div>
        <div id="overview" class="w-full gap-4">
            <div class="flex space-x-6 border-b" x-data="{ activeTab: 'overview' }">
                <a href="#overview"
                    :class="activeTab == 'overview' ? 'border-b-2 border-primary text-primary font-semibold' : 'font-medium'"
                    class="pb-2 " @click="activeTab = 'overview'">Overview</a>
                @if ($hotel->amenities->count() > 0)
                    <a href="#amenities"
                        :class="activeTab == 'amenities' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'font-medium'"
                        class="pb-2 " @click="activeTab = 'amenities'">Amenities</a>
                @endif

                <a href="#price"
                    :class="activeTab == 'price' ? 'border-b-2 border-primary text-primary font-semibold' : 'font-medium'"
                    class="pb-2 font-medium" @click="activeTab = 'price'">Price</a>
            </div>
        </div>
        <div class="space-y-3">
            <div>
                <h1 class="text-2xl font-semibold md:text-3xl">{{ $hotel->name }}</h1>
                <address>{{ $hotel->address . ', ' . ucfirst($hotel->city->name) }}</address>
            </div>

            @if ($hotel->ratings()->exists())
                @php
                    $rate = round($hotel->ratings()->avg('number'), 1);
                @endphp

                <p
                    class="{{ 'bg-[' . getRatingColor($rate) . ']' }} px-2 py-1 inline-block shadow max-w-max font-medium text-sm rounded">
                    {{ $rate }}</p> <span class="pl-2 font-medium">{{ getRatingDescription($rate) }}</span> <br>
                <button @click="review=true" class="space-x-2 font-medium underline">
                    <span class="text-blue-600">View reviews</span>
                    <span>
                        <i class="text-xs fa-solid fa-greater-than"></i></span>
                </button>
            @else
                <p class="{{ 'bg-gray-200' }} px-2 py-1 shadow max-w-max text-sm font-medium rounded">No reviews
                    yet
                </p>
            @endif

        </div>

        @if ($amenities?->count() > 0)
            <div class="gap-6 space-y-4" id="amenities">
                <h1 class="text-2xl font-semibold">
                    Amenities
                </h1>
                <div class="flex gap-6">
                    @foreach ($amenities as $amenity)
                        <div :key="{{ $amenity->id }}"
                            class="flex border items-center flex-col py-2 px-4 shadow-sm rounded-md transition-all duration-200">
                            <span class="text-primary">{!! $amenity->icon !!}</span>
                            <span class="capitalize font-medium"> {{ $amenity->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($faqs->count() > 0)
            <div class="gap-6 space-y-4" id="amenities">
                <h1 class="text-2xl font-semibold">
                    Amenities
                </h1>
                <div class="flex gap-6">
                    @foreach ($amenities as $amenity)
                        <div :key="{{ $amenity->id }}"
                            class="flex border items-center flex-col py-2 px-4 shadow-sm rounded-md transition-all duration-200">
                            <span class="text-primary">{!! $amenity->icon !!}</span>
                            <span class="capitalize font-medium"> {{ $amenity->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="w-full">
            <div class="flex items-center justify-between max-w-sm p-4 shadow rounded-2xl">
                <div class="flex flex-col justify-center text-center max-w-max">
                    <span><i class="fa-regular fa-clock fa-2x text-primary"></i></span>
                    <p class="text-lg font-medium">Checkin Time</p>
                    <p class="font-medium ">Anytime</p>
                </div>
                <div class="flex flex-col justify-center text-center max-w-max">
                    <span><i class="fa-solid fa-stopwatch-20 fa-2x text-primary"></i></i></span>
                    <p class="text-lg font-medium">Checkout Time</p>
                    <p class="font-medium ">{{ $hotel->checked_out?->format('H:m A') }}</p>
                </div>
            </div>
        </div>
        <div class="w-full gap-4 space-y-4" id="price">
            <h1 class="text-2xl font-semibold md:text-3xl">Choose your room category based on price</h1>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($hotel->roomCategory as $category)
                    @php
                        $total = $category->images?->count();
                        $firstImage = $category->rooms()->whereNotNull('image')->first()?->image;
                        $available = $category->rooms
                            ->filter(fn($room) => $room->isAvailable(Session::get('checkin'), Session::get('checkout')))
                            ->count();
                    @endphp
                    <div class="overflow-hidden rounded-lg shadow">
                        <div id="default-carousel" class="relative w-full" data-carousel="slide">
                            <span
                                class="absolute z-50 flex items-center justify-center w-8 h-8 text-center text-white bg-green-400 rounded-full top-3 right-2">{{ $available }}</span>
                            <!-- Carousel wrapper -->
                            <div class="relative h-48 overflow-hidden md:h-56">
                                {{-- <div class="duration-700 ease-in-out "> --}}
                                <img src="{{ getCloudinaryImage($firstImage) }}"
                                    class="absolute block object-cover w-full h-full"
                                    alt="{{ $hotel->name . ' 9jahotel' }}">
                                {{-- </div> --}}
                            </div>
                        </div>
                        <div href="" class="block p-4 space-y-3">
                            <h1 class="font-medium capitalize hover:text-primary">{{ $category->name }}</h1>
                            <div class="space-y-1">
                                <div class="flex items-center justify-start space-x-3">
                                    <p class="font-medium text-gray-500 line-through">
                                        {{ formatAsNaira($category->price1) }}</p>
                                    <p class="text-sm ">Walk-in Price</p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xl font-medium">
                                        {{ formatAsNaira(calculateRemainingAmount($category->price1, $category->hotel->online_price)) }}
                                    </p>
                                    @if ($available > 0)
                                        <a href="{{ route('bookroom', $category->id) }}"
                                            class="px-4 py-1 text-sm font-medium text-white rounded-full bg-primary hover:bg-primary-800">Select
                                            Room</a>
                                    @else
                                        <span
                                            class="px-4 py-1 text-sm font-medium text-gray-400 bg-gray-100 rounded-full cursor-not-allowed">No
                                            available room</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                @empty
                    <p class="text-xl">No room category yet</p>
                @endforelse
            </div>
        </div>
    </div>

    @if ($modal)
        <div x-cloak x-show="modal"
            class="fixed inset-0 z-50 flex items-center justify-center w-screen h-screen bg-black bg-opacity-80">
            <div class="w-4/5 p-8 mx-auto bg-white shadow-lg rounded-3xl max-w-[500px] relative">
                <button
                    class="absolute top-0 right-0 p-2 m-4 text-2xl font-bold duration-300 cursor-pointer hover:text-primary"
                    @click="modal = false">x</button>

                <div class="py-4 space-y-4">
                    <h1 class="text-xl font-medium text-center">{{ $category->name }} Rooms</h1>
                    <div class="flex flex-wrap justify-center gap-4">
                        @forelse ($this->category?->rooms as $room)
                            <a href="{{ route('bookroom', $room->id) }}"
                                class="block px-4 py-1 transition duration-300 shadow cursor-pointer rounded-3xl hover:bg-primary-50 hover:text-primary">
                                <span class="font-medium uppercase ">Room {{ $room->number }}</span>
                                <span></span>
                            </a>
                        @empty
                            <p class="px-4 text-lg">No rooms available as of this date, kindly select another date</p>
                        @endforelse
                    </div>
                    <a href="" class="block"></a>
                </div>
            </div>
        </div>

    @endif
</div>
