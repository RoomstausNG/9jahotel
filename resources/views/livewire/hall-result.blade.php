<div class="w-full mx-auto">
    <div class="w-full max-w-screen-xl px-4 py-4 mx-auto space-y-8 rounded-lg">
        {{-- @livewire('search-city') --}}
        <div class="grid gap-8">
            {{-- results --}}
            <div id="results" class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-4 w-grid">
                <div class="hidden w-full pr-4 space-y-8 lg:block">
                    <div class="space-y-2">
                        <p class="text-lg font-medium">Search hotel by name</p>
                        <input wire:model.live='name' type="text"
                            class="w-full border-gray-400 rounded-lg shadow focus:ring-0 focus:border-2 ring-0 focus:border-primary">
                    </div>
                    <p class="text-xl font-medium flex justify-between">
                        <span>Filter By</span>

                        <button wire:click="resetFilters" class="text-sm hover:underline font-bold text-primary">Reset
                            Filter</button>
                    </p>
                    <div class="space-y-2">
                        <p class="font-medium">Price per night</p>
                        <div class="flex space-x-4">
                            <input wire:model.lazy='low' type="text" placeholder="Min price"
                                class="w-full border-gray-400 rounded-lg shadow focus:ring-0 focus:border-2 ring-0 focus:border-primary"
                                x-data x-on:reset-filters.window="$el.value = ''">
                            <input wire:model.lazy='high' type="text" placeholder="Max price"
                                class="w-full border-gray-400 rounded-lg shadow focus:ring-0 focus:border-2 ring-0 focus:border-primary"
                                x-data x-on:reset-filters.window="$el.value = ''">
                        </div>
                        <button type="submit"
                            class="w-full py-2 px-4 bg-primary hover:bg-primary-700 text-white font-medium rounded-lg transition duration-200 focus:outline-none">
                            Save
                        </button>
                    </div>
                    <div class="w-full space-y-3">
                        <h1 class="text-lg font-semibold text-gray-900">Amenities</h1>
                        <div
                            class="flex gap-y-2 gap-x-3 flex-wrap items-start max-h-[500px] p-2 shadow-sm border rounded-md overflow-y-auto">
                            @foreach ($amenities as $amenity)
                            @if (!$amenity->icon)
                            @continue
                            @endif
                            <label for="{{ $amenity->id }}"
                                class="flex cursor-pointer border items-center gap-2 py-1.5 px-3 shadow-sm rounded-md transition-all duration-200
                                   {{ in_array($amenity->id, $filterAmenities) ? ' border-primary bg-primary-100' : ' border-gray-200 hover:border-gray-200' }}">
                                <input wire:model.live='filterAmenities' class="hidden" type="checkbox"
                                    value="{{ $amenity->id }}" id="{{ $amenity->id }}">
                                <span
                                    style="white-space:nowrap; display: inline-flex; align-items: center; height: 16px; margin-right: 4px;">
                                    {!! preg_replace('/<svg
                                        /', '<svg height="20" width="auto" style="height: 20px; width: auto;"' ,
                                        $amenity->icon) !!}
                                </span>
                                <span class="capitalize font-medium text-sm"> {{ $amenity->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-4 md:col-span-3 max-w-[600px] lg:max-w-[768px] xl:max-w-[800px]">
                    <div class="flex flex-col justify-between w-full md:flex-row">
                        {{-- <div class="space-y-2">
                            <label for="">No to show</label> <br>
                            <select wire:model.live='perPage'
                                class="w-full border-gray-400 max-w-[150px] text-sm rounded-lg shadow py-2 focus:ring-0 focus:border-2 ring-0 focus:border-primary">
                                <option value=24>24</option>
                                <option value=5>5</option>
                                <option value=10>10</option>
                                <option value=20>20</option>
                                <option value=50>50</option>
                            </select>
                        </div> --}}
                        <div class="flex items-center justify-between w-full space-y-2">
                            <label for="sortBy" class="text-xl font-medium">Sort results by</label>
                            <select id="sortBy" wire:model.live='sortBy'
                                class="w-full border-gray-400 max-w-[180px] text-sm rounded-lg shadow py-2 focus:ring-0 focus:border-2 ring-0 focus:border-primary">
                                <option value="">Recommended</option>
                                <option value="price_low">Price: Low to High</option>
                                <option value="price_high">Price: High to Low</option>
                                <option value="rating_high">Rating: High to Low</option>
                                <option value="class_high">Property Class (Star Rating)</option>
                            </select>
                        </div>
                    </div>
                    @forelse ($hotels as $hotel)
                    <div class="flex flex-col md:flex-row overflow-hidden rounded-xl  shadow-sm min-h-[200px] border">
                        <div class="w-full block h-full md:w-2/5 relative">
                            <a href="{{ route('hotels.show', $hotel->code) }}" class="block">
                                <img src="{{ getCloudinaryUrl($hotel->background_image) }}"
                                    class="block object-cover object-center w-full min-h-[250px] md:max-h-[350px] lg:min-h-[280px] lg:max-h-[330px] h-full"
                                    alt="{{ $hotel->name }}">
                            </a>
                            <span wire:click="addToFavorite({{ $hotel->id }})"
                                class="bg-white p-1.5 rounded-full aspect-square shadow flex justify-center items-center absolute cursor-pointer text-red-600 top-0 right-0 m-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
                                    <path fill="currentColor"
                                        d="m12 21l-1.45-1.3q-2.525-2.275-4.175-3.925T3.75 12.812T2.388 10.4T2 8.15Q2 5.8 3.575 4.225T7.5 2.65q1.3 0 2.475.55T12 4.75q.85-1 2.025-1.55t2.475-.55q2.35 0 3.925 1.575T22 8.15q0 1.15-.387 2.25t-1.363 2.412t-2.625 2.963T13.45 19.7zm0-2.7q2.4-2.15 3.95-3.687t2.45-2.675t1.25-2.026T20 8.15q0-1.5-1-2.5t-2.5-1q-1.175 0-2.175.662T12.95 7h-1.9q-.375-1.025-1.375-1.687T7.5 4.65q-1.5 0-2.5 1t-1 2.5q0 .875.35 1.763t1.25 2.025t2.45 2.675T12 18.3m0-6.825" />
                                </svg>
                            </span>
                        </div>

                        <div class="flex flex-col justify-between flex-1 p-2 border-l md:pb-6">
                            <div class="space-y-4">
                                <div>
                                    <a href="{{ route('hotels.show', $hotel->code) }}"
                                        class="text-xl block font-medium capitalize">{{ $hotel->name }}</a>

                                    <p class="text-sm capitalize" style="line-height: 16px">
                                        {{ $hotel->address . ', ' . $hotel->city->name }}</p>
                                </div>
                                <p class="flex flex-wrap w-full gap-2">
                                    @php
                                    $totalAmenities = $hotel->amenities->count();
                                    $displayAmenities = $hotel->amenities->take(min(5, $totalAmenities));
                                    $remainingCount = $totalAmenities - 5;
                                    @endphp

                                    @foreach ($displayAmenities as $amenity)
                                    <span style="white-space:nowrap"
                                        class="px-2 py-1 text-xs font-medium text-gray-500 border rounded shadow-sm">{{
                                        $amenity->name }}</span>
                                    @endforeach

                                    @if ($remainingCount > 0)
                                    <span style="white-space:nowrap"
                                        class="px-2 py-1 text-xs font-medium text-primary border rounded shadow-sm">+{{
                                        $remainingCount }} more</span>
                                    @endif
                                </p>
                            </div>
                            <div class="space-y-3">
                                <div class="p-0">
                                    <div class="flex items-center justify-end p-0 space-x-3">
                                        @php
                                        $price = $hotel->hall_lowest_price;
                                        // $price = $hotel->roomCategory()->orderBy('price1')->first()?->price1;
                                        @endphp
                                        <p class="text-sm font-medium">Walk-in Price</p>
                                        <p class="text-lg font-medium text-gray-500 line-through ">
                                            {{ formatAsNaira($hotel->hall_lowest_price) }}
                                        </p>

                                    </div>
                                    <div class="flex items-center justify-between p-0">

                                        <p class="text-lg">Price from:</p>
                                        <p class="text-xl font-semibold">
                                            {{ formatAsNaira(calculateRemainingAmount($hotel->hall_lowest_price,
                                            $hotel->online_price)) }}
                                        </p>

                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="">
                                        @php
                                        $rate = $hotel->star_rating;
                                        // $rate = round($hotel->ratings()->avg('number'), 1);
                                        @endphp

                                        {{-- @if ($hotel->ratings()->exists())
                                        <p
                                            class="{{ 'bg-[' . getRatingColor($rate) . ']' }} px-2 py-1 inline-block shadow max-w-max font-medium text-sm rounded">
                                            {{ $rate }}</p>
                                        <span class="pl-2 font-medium">{{ getRatingDescription($rate) }}</span>
                                        <br>
                                        @else
                                        <p
                                            class="{{ 'bg-gray-200' }} px-2 py-1 shadow max-w-max text-sm font-medium rounded">
                                            No reviews
                                            yet
                                        </p>
                                        @endif --}}
                                        @if($rate)
                                        <p
                                            class="{{ 'bg-[' . getRatingColor($rate) . ']' }} px-2 py-1 inline-block shadow max-w-max font-medium text-sm rounded">
                                            {{ $rate }}</p>
                                        <span class="text-sm">Star</span>
                                        @else
                                        @endif

                                    </div>

                                    <p>
                                        <a href="{{ route('hotels.show', $hotel->code) }}"
                                            class="px-4 py-2 font-semibold text-white bg-green-600 rounded-md">Book
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
                    {{-- @if ($hotels->count() < $city->hotels->count())
                        <button wire:click='increasePage'
                            class="block px-8 py-2 mx-auto font-medium text-white transition duration-300 rounded-lg hover:bg-primary-800 bg-primary">Load
                            More</button>
                        @endif --}}
                        <div class="m-4 py-2">
                            <div class="">{{ $hotels->links(data: ['scrollTo' => '#results']) }}</div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
