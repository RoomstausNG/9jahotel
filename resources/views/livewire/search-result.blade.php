<div class="w-full mx-auto">
    <div class="w-full max-w-screen-xl px-4 py-4 mx-auto space-y-8 rounded-lg">
        {{-- @livewire('search-city') --}}
        <div class="w-full">
            <h1 class="text-2xl font-semibold text-center">Search Hotel result</h1>
        </div>
        <div class="grid gap-8">
            {{-- results --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-4 w-grid">
                <div class="hidden w-full pr-4 space-y-8 lg:block">
                    {{-- <div class="w-full">
                        @if ($mapUrl)
                            <div class="rounded-lg shadow-lg overflow-hidden">
                                <img src="{{ $mapUrl }}" alt="Map of {{ $city->name }}"
                                    class="w-full h-64 object-cover" loading="lazy">
                            </div>
                        @else
                            <div class="bg-gray-100 p-4 rounded-lg text-center">
                                <p class="text-gray-500">Map unavailable at this time</p>
                            </div>
                        @endif
                    </div> --}}
                    <div class="space-y-2">
                        <p class="text-lg font-medium">Search hotel by name</p>
                        <input wire:model.live='name' type="text"
                            class="w-full border-gray-400 rounded-lg shadow focus:ring-0 focus:border-2 ring-0 focus:border-primary">
                    </div>
                    <p class="text-lg font-semibold text-gray-900">Filter By</p>
                    <div class="space-y-2">
                        <p class="font-medium">Price per night</p>
                        <div class="flex space-x-4">
                            <input wire:model.lazy='low' type="text" placeholder="min price"
                                class="w-full border-gray-400 rounded-lg shadow focus:ring-0 focus:border-2 ring-0 focus:border-primary">
                            <input wire:model.lazy='high' type="text" placeholder="max price"
                                class="w-full border-gray-400 rounded-lg shadow focus:ring-0 focus:border-2 ring-0 focus:border-primary">
                        </div>
                        <button type="submit"
                            class="w-full py-2 px-4 bg-primary hover:bg-primary-700 text-white font-medium rounded-lg transition duration-200 focus:outline-none">
                            Save
                        </button>
                    </div>

                    <div class="w-full space-y-3">
                        <h1 class="text-lg font-semibold text-gray-900">Power Availability</h1>
                        <div class="">
                            <select name="" id="" class="capitalize">
                                @foreach (\App\Models\Power::all() as $power)
                                    <option class="capitalize" value="{{ $power->id }}">{{ $power->name }}">
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="w-full space-y-3">
                        <h1 class="text-lg font-semibold text-gray-900">Amenities</h1>
                        <div
                            class="flex gap-4 flex-wrap items-start max-h-[500px] p-2 shadow-sm border rounded-md overflow-y-auto">
                            @foreach ($amenities as $amenity)
                                @if (!$amenity->icon)
                                    @continue
                                @endif
                                <label for="{{ $amenity->id }}"
                                    class="flex cursor-pointer border items-center gap-2 py-2 px-4 shadow-sm rounded-md transition-all duration-200
                                   {{ in_array($amenity->id, $filterAmenities) ? ' border-primary bg-primary-100' : ' border-gray-200 hover:border-gray-200' }}">
                                    <input wire:model.live='filterAmenities' class="hidden" type="checkbox"
                                        value="{{ $amenity->id }}" id="{{ $amenity->id }}">
                                    <span
                                        style="white-space:nowrap; display: inline-flex; align-items: center; height: 16px; margin-right: 4px;">
                                        {!! preg_replace('/<svg/', '<svg height="20" width="auto" style="height: 20px; width: auto;"', $amenity->icon) !!}
                                    </span>
                                    <span class="capitalize font-medium text-sm"> {{ $amenity->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-4 md:col-span-3 max-w-[600px] lg:max-w-[800px] xl:max-w-[900px]">
                    <div class="flex items-center justify-between bg-[#111936] text-white p-4 rounded-full w-full">
                        <div class="flex items-center gap-3">
                            <div class="text-purple-500 bg-purple-50 p-2 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                                </svg>
                            </div>
                            <span class="">Get a reward night when you stay for 10 nights</span>
                        </div>
                        <div class="flex gap-4 items-center">
                            <a href="/login" class="bg-blue-600 text-white px-6 py-1.5 rounded-full">Sign in</a>
                            <a href="/register" class="text-white underline">Learn more</a>
                        </div>
                    </div>
                    <div class="flex flex-col justify-between w-full md:flex-row">
                        <div class="flex items-center justify-between w-full space-y-2">
                            <label for="" class="text-lg font-semibold text-gray-900">Sort results by</label>
                            <select wire:model.live='sortBy'
                                class="w-full border-gray-400 max-w-[180px] text-sm rounded-lg shadow py-2 focus:ring-0 focus:border-2 ring-0 focus:border-primary">
                                <option value="">Recommended</option>
                                <option value="price_low">Price: Low to High</option>
                                <option value="price_high">Price: High to Low</option>
                                <option value="rating_high">Rating: High to Low</option>
                                {{-- <option value="rating_low">Rating: Low to High</option> --}}
                                <option value="class_high">Property Class (Star Rating)</option>
                                {{-- <option value="class_low">Property Class: Low to High</option> --}}
                            </select>
                        </div>
                    </div>
                    @forelse ($hotels as $hotel)
                        <div
                            class="flex duration-300 transition-all transform hover:shadow-lg flex-col md:flex-row overflow-hidden rounded-xl  shadow-sm min-h-[200px] border">
                            <div class="w-full h-full md:w-2/5">
                                <img src="{{ getCloudinaryUrl($hotel->background_image) }}"
                                    class="block object-cover object-center w-full min-h-[250px] md:max-h-[320px] lg:min-h-[280px] lg:max-h-[320px] h-full"
                                    alt="{{ $hotel->name }}">
                            </div>

                            <div class="flex flex-col justify-between flex-1 p-4 md:pb-6 border-l border-gray-100">
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-lg font-semibold text-gray-900 capitalize">{{ $hotel->name }}
                                        </p>
                                        <p class="text-sm capitalize" style="line-height: 16px">
                                            {{ $hotel->address . ', ' . $hotel->city->name }}</p>
                                    </div>
                                    <div class="flex flex-wrap w-full gap-2">
                                        @foreach ($hotel->amenities->take(5) as $amenity)
                                            <p
                                                class="py-1 flex text-gray-500 border rounded shadow-sm items-center text-xs px-2 font-medium capitalize">
                                                <span
                                                    style="white-space:nowrap; display: inline-flex; align-items: center; height: 16px; margin-right: 4px;">
                                                    {!! preg_replace('/<svg/', '<svg height="16" width="auto" style="height: 16px; width: 100%;"', $amenity->icon) !!}
                                                </span>
                                                <span>{{ $amenity->name }}</span>
                                            </p>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="p-0">
                                        <div class="flex items-center justify-end p-0 space-x-3">
                                            @php
                                                $price = $hotel->roomCategory()->orderBy('price1')->first()?->price1;
                                            @endphp
                                            <p class="text-sm font-medium">Walk-in Price</p>
                                            <p class="text-lg font-medium text-gray-500 line-through ">
                                                {{ formatAsNaira($price) }}
                                            </p>

                                        </div>
                                        <div class="flex items-center justify-between p-0">

                                            <p class="text-lg">Price from:</p>
                                            <p class="text-xl font-semibold">
                                                {{ formatAsNaira(calculateRemainingAmount($price, $hotel->online_price)) }}
                                            </p>

                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="">
                                            @php
                                                $rate = $hotel->star_rating;
                                                // $rate = round($hotel->ratings()->avg('number'), 1);
                                            @endphp

                                            @if ($hotel->ratings()->exists())
                                                <p
                                                    class="{{ 'bg-[' . getRatingColor($rate) . ']' }} px-2 py-1 inline-block shadow max-w-max font-medium text-sm rounded">
                                                    {{ $rate }}</p>
                                                <span
                                                    class="pl-2 font-medium">{{ getRatingDescription($rate) }}</span>
                                                <br>
                                            @else
                                                <p
                                                    class="{{ 'bg-gray-200' }} px-2 py-1 shadow max-w-max text-sm font-medium rounded">
                                                    No reviews
                                                    yet
                                                </p>
                                            @endif
                                        </div>
                                        <p>
                                            <a href="{{ route('hotels.show', $hotel->code) }}"
                                                class="px-4 py-2 font-medium text-white bg-green-600 rounded-md">Book
                                                Hotel</a>
                                        </p>

                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex overflow-hidden rounded-xl shadow-sm min-h-[200px] border">
                            <p class="py">No hotels for search inputs</p>
                        </div>
                    @endforelse
                    @if ($hotels->count() < $totalFilteredCount)
                        <button wire:click='increasePage'
                            class="block px-8 py-2 mx-auto font-medium text-white transition duration-300 rounded-lg hover:bg-primary-800 bg-primary">
                            Load More
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
