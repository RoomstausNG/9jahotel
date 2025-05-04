<div class="relative w-full p-0 mx-auto" x-data="{ review: @entangle('review').live, modal: @entangle('modal').live }">
    @php
        // Pre-load relationships to avoid duplicate queries
        $amenities = $hotel->amenities;
        $faqs = $hotel->faqs;

        // Calculate rating once
        $hasRatings = $hotel->ratings()->exists();
        $rate = $hasRatings ? round($hotel->ratings()->avg('number'), 1) : 0;
    @endphp



    <div class="grid w-full max-w-screen-lg px-4 py-4 mx-auto space-y-8 rounded-lg">
        <div class="w-full py-6">
            {{-- search result --}}
            @livewire('search-city')
        </div>


        {{-- back button --}}
        <div class="w-full">
            <a href="{{ route('hotels.result', $hotel->city->id) }}"
                class="px-6 py-2 text-white bg-gray-600 rounded">Back</a>
        </div>

        {{-- Hotel Image Carousel --}}
        @livewire('image-carousel', ['hotelId' => $hotel->id])

        <div id="overview" class="w-full gap-4">
            <div class="flex space-x-6 border-b" x-data="{ activeTab: 'overview' }">
                <a href="#overview"
                    :class="activeTab == 'overview' ? 'border-b-2 border-primary text-primary font-semibold' : 'font-medium'"
                    class="pb-2 " @click="activeTab = 'overview'">Overview</a>
                @if ($amenities->count() > 0)
                    <a href="#amenities"
                        :class="activeTab == 'amenities' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'font-medium'"
                        class="pb-2 " @click="activeTab = 'amenities'">Amenities</a>
                @endif

                @if ($faqs->count() > 0)
                    <a href="#faqs"
                        :class="activeTab == 'faqs' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'font-medium'"
                        class="pb-2 " @click="activeTab = 'faqs'">Faq</a>
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

            @if ($hasRatings)
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

        {{-- Amenities Section --}}
        @if ($amenities->count() > 0)
            <div class="gap-6 space-y-4" id="amenities">
                <h1 class="text-2xl font-semibold">
                    Amenities
                </h1>
                <div class="flex flex-wrap gap-3">
                    @php
                        $displayCount = min(5, $amenities->count());
                        $remainingCount = $amenities->count() - $displayCount;
                    @endphp

                    @foreach ($amenities as $amenity)
                        <div
                            class="flex border items-center gap-2 py-1.5 px-2 shadow-sm rounded-md transition-all duration-200">
                            <span
                                style="white-space:nowrap; display: inline-flex; align-items: center; height: 16px; margin-right: 4px;"
                                class="text-primary">
                                {!! preg_replace('/<svg /', '<svg height="20" width="auto" style="height: 20px; width: auto;"', $amenity->icon) !!}
                            </span>
                            <span class="capitalize text-sm font-medium"> {{ $amenity->name }}</span>
                        </div>
                    @endforeach

                    {{-- @if ($remainingCount > 0)
                <div class="flex border items-center gap-2 p-2 shadow-sm rounded-md bg-gray-50">
                    <span class="font-medium text-gray-600">+{{ $remainingCount }} more</span>
                </div>
                @endif --}}
                </div>
            </div>
        @endif

        {{-- FAQs Section --}}
        @if ($faqs->count() > 0)
            <div class="gap-6 space-y-4" id="faqs">
                <h1 class="text-2xl font-semibold">
                    Frequently Asked Questions
                </h1>
                @foreach ($faqs as $faq)
                    <div x-data="{ open: false }"
                        class="border rounded-md shadow-sm transition-all duration-200 overflow-hidden">
                        <!-- Question header - clickable to toggle -->
                        <div @click="open = !open"
                            class="flex justify-between items-center p-4 cursor-pointer hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <span class="text-primary">{!! $faq->question->icon ?? '<i class="fas fa-question-circle"></i>' !!}</span>
                                <span class="font-medium">{{ $faq->question->text }}</span>
                            </div>
                            <!-- Toggle icon that rotates -->
                            <span class="text-gray-500 transform transition-transform duration-200"
                                :class="{ 'rotate-180': open }">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </div>

                        <!-- Answer section that slides down/up -->
                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform -translate-y-4"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-4"
                            class="px-4 pb-4 pt-0 text-gray-600 border-t">
                            {{ $faq->answer }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="w-full">
            <div class="flex items-center justify-between max-w-sm p-4 shadow rounded-2xl">
                <div class="flex flex-col justify-center text-center max-w-max">
                    <span><i class="fa-regular fa-clock fa-2x text-primary"></i></span>
                    <p class="text-lg font-medium">Checkin Time</p>
                    <p class="font-medium ">12 Noon</p>
                </div>
                <div class="flex flex-col justify-center text-center max-w-max">
                    <span><i class="fa-solid fa-stopwatch-20 fa-2x text-primary"></i></i></span>
                    <p class="text-lg font-medium">Checkout Time</p>
                    <p class="font-medium ">{{ $hotel->checked_out?->format('H:m A') }}</p>
                </div>
            </div>
        </div>

        <div class="p">
            <div class="">
                <div x-data="{ expanded: false }">
                    <div :class="expanded ? '' : 'line-clamp-3'"
                        class="overflow-hidden transition-all duration-300 text-gray-800">
                        {!! $hotel->description !!}
                    </div>
                    <button x-show="!expanded" @click="expanded = true"
                        class="mt-2 text-blue-600 underline text-sm font-medium focus:outline-none" type="button">
                        See more
                    </button>
                    <button x-show="expanded" @click="expanded = false"
                        class="mt-2 text-blue-600 underline text-sm font-medium focus:outline-none" type="button">
                        See less
                    </button>
                </div>
            </div>
        </div>

        {{-- Room Categories Section --}}
        <div class="w-full gap-4 space-y-4" id="price">
            <h1 class="text-2xl font-semibold md:text-3xl">Choose your room category based on price</h1>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($roomCategories as $category)
                    <div class="overflow-hidden rounded-lg shadow">
                        <div class="relative w-full">
                            <span
                                class="absolute z-50 flex items-center justify-center w-8 h-8 text-center text-white bg-green-400 rounded-full top-3 right-2">
                                {{ $category['available'] }}
                            </span>
                            <!-- Carousel wrapper -->
                            <div class="relative h-48 overflow-hidden md:h-56">
                                <img src="{{ getCloudinaryUrl($category['firstImage']) }}"
                                    class="absolute block object-cover w-full h-full"
                                    alt="{{ $hotel->name . ' 9jahotel' }}">
                            </div>
                        </div>
                        <div class="block p-4 space-y-3">
                            <h1 class="font-medium capitalize hover:text-primary">{{ $category['name'] }}</h1>
                            <div class="space-y-1">
                                <div class="flex items-center justify-start space-x-3">
                                    <p class="font-medium text-gray-500 line-through">
                                        {{ formatAsNaira($category['price1']) }}</p>
                                    <p class="text-sm">Walk-in Price</p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xl font-medium">
                                        {{ formatAsNaira($category['discountedPrice']) }}
                                    </p>
                                    @if ($category['available'] > 0)
                                        <a href="{{ route('bookroom', $category['id']) }}"
                                            class="px-4 py-1 text-sm font-medium text-white rounded-full bg-primary hover:bg-primary-800">
                                            Select Room
                                        </a>
                                    @else
                                        <span
                                            class="px-4 py-1 text-sm font-medium text-gray-400 bg-gray-100 rounded-full cursor-not-allowed">
                                            No available room
                                        </span>
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

    {{-- Room Selection Modal --}}
    @if ($modal)
        <div x-cloak x-show="modal"
            class="fixed inset-0 z-50 flex items-center justify-center w-screen h-screen bg-black bg-opacity-80">
            <div class="w-4/5 p-8 mx-auto bg-white shadow-lg rounded-3xl max-w-[500px] relative">
                <button
                    class="absolute top-0 right-0 p-2 m-4 text-2xl font-bold duration-300 cursor-pointer hover:text-primary"
                    @click="modal = false">x</button>

                <div class="py-4 space-y-4">
                    <h1 class="text-xl font-medium text-center">{{ $category->name ?? '' }} Rooms</h1>
                    <div class="flex flex-wrap justify-center gap-4">
                        @if ($category)
                            @forelse ($category->rooms as $room)
                                <a href="{{ route('bookroom', $room->id) }}"
                                    class="block px-4 py-1 transition duration-300 shadow cursor-pointer rounded-3xl hover:bg-primary-50 hover:text-primary">
                                    <span class="font-medium uppercase ">Room {{ $room->number }}</span>
                                    <span></span>
                                </a>
                            @empty
                                <p class="px-4 text-lg">No rooms available as of this date, kindly select another date
                                </p>
                            @endforelse
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Reviews Modal --}}
    @if ($review)
        <div x-cloak x-show="review"
            class="fixed inset-0 z-50 flex items-center justify-center w-screen h-screen bg-black bg-opacity-80">
            <div class="w-4/5 p-8 mx-auto bg-white shadow-lg rounded-3xl max-w-[500px] relative">
                <button
                    class="absolute top-0 right-0 p-2 m-4 text-2xl font-bold duration-300 cursor-pointer hover:text-primary"
                    @click="review = false">x</button>

                <div class="py-4 space-y-4">
                    <h1 class="text-xl font-medium text-center">{{ $hotel->name }} Reviews</h1>
                    <div class="space-y-4">
                        @forelse ($hotel->ratings as $rating)
                            <div class="p-4 border rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                                        <div>
                                            <p class="font-medium">{{ $rating->customer->name ?? 'Guest' }}</p>
                                            <p class="text-sm text-gray-500">
                                                {{ $rating->created_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                    <div
                                        class="px-2 py-1 text-sm font-medium rounded bg-{{ getRatingColor($rating->number) }}">
                                        {{ $rating->number }}
                                    </div>
                                </div>
                                <p class="mt-2 text-gray-700">{{ $rating->comment }}</p>
                            </div>
                        @empty
                            <p class="text-center">No reviews yet</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
