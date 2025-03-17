<div class="w-full mx-auto">
    <div class="w-full max-w-screen-xl px-4 py-4 mx-auto space-y-8 rounded-lg">
        {{-- @livewire('search-city') --}}
        <div class="w-full">
            <h1 class="text-2xl font-semibold text-center">Search Hotel result</h1>
        </div>
        <div class="grid gap-8">
            {{-- results --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-4 w-grid">
                <div class="hidden w-full pr-4 space-y-8 lg:block">
                    <div class="space-y-2">
                        <p class="text-lg font-medium">Search hotel by name</p>
                        <input wire:model.live='name' type="text"
                            class="w-full border-gray-400 rounded-lg shadow focus:ring-0 focus:border-2 ring-0 focus:border-primary">
                    </div>
                    <p class="text-xl font-medium">Filter By</p>
                    <div class="space-y-2">
                        <p class="font-medium">Price per night</p>
                        <div class="flex space-x-4">
                            <input wire:model.live='low' type="text" placeholder="min price"
                                class="w-full border-gray-400 rounded-lg shadow focus:ring-0 focus:border-2 ring-0 focus:border-primary">
                            <input wire:model.live='high' type="text" placeholder="max price"
                                class="w-full border-gray-400 rounded-lg shadow focus:ring-0 focus:border-2 ring-0 focus:border-primary">
                        </div>
                        <input type="range" class="w-full focus:text-primary focus:bg-primary">
                    </div>
                    <div class="">
                        <h1>Amenities</h1>
                        <div class="flex flex-col gap-2">
                            @foreach ($amenities as $amenity)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model.live='filterAmenities[]'
                                        value="{{ $amenity->id }}">
                                    <span>{{ $amenity->name }}</span>
                                </label>
                            @endforeach
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
                                <label for="" class="text-xl font-medium">Sort results by</label>
                                <select wire:model.live='sortBy'
                                    class="w-full border-gray-400 max-w-[180px] text-sm rounded-lg shadow py-2 focus:ring-0 focus:border-2 ring-0 focus:border-primary">
                                    <option>Recomended</option>
                                    <option value="low">Price: Low to High</option>
                                    <option value="high">Price: High to Low</option>
                                </select>
                            </div>
                        </div>
                        @forelse ($hotels as $hotel)
                            <div
                                class="flex flex-col md:flex-row overflow-hidden rounded-xl  shadow-sm min-h-[200px] border gap-x-2">
                                <div class="w-full h-full md:w-1/3">
                                    <img src="{{ getCloudinaryImage($hotel->background_image) }}"
                                        class="block object-cover object-center w-full min-h-[250px] md:max-h-[280px] md:min-h-[200px] h-full"
                                        alt="{{ $hotel->name }}">
                                </div>

                                <div class="flex flex-col justify-between flex-1 p-4 md:pb-6">
                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-xl font-medium capitalize">{{ $hotel->name }}</p>
                                            <p class="text-sm capitalize" style="line-height: 16px">
                                                {{ $hotel->address . ', ' . $hotel->city->name }}</p>
                                        </div>
                                        <p class="flex flex-wrap w-full gap-2">
                                            @foreach ($hotel->amenities as $amenity)
                                                <span style="white-space:nowrap"
                                                    class="px-2 py-1 text-xs font-medium text-gray-500 border rounded shadow-sm">{{ $amenity->name }}</span>
                                            @endforeach
                                        </p>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="p-0">
                                            <div class="flex items-center justify-end p-0 space-x-3">
                                                @php
                                                    $price = $hotel->roomCategory()->orderBy('price1')->first()
                                                        ?->price1;
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
                                                @if ($hotel->ratings()->exists())
                                                    @php
                                                        $rate = round($hotel->ratings()->avg('number'), 1);
                                                    @endphp

                                                    <p
                                                        class="{{ 'bg-[' . getRatingColor($rate) . ']' }} px-2 py-1 inline-block shadow max-w-max font-medium text-sm rounded">
                                                        {{ $rate }}</p> <span
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
                        @if ($hotels->count() < $city->hotels->count())
                            <button wire:click='increasePage'
                                class="block px-8 py-2 mx-auto font-medium text-white transition duration-300 rounded-lg hover:bg-primary-800 bg-primary">Load
                                More</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
