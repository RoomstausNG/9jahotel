<div id="city" class="w-full mx-auto space-y-8 lg:p-0">
    <div class="px-4 text-center lg:px-0">
        <h1 class="text-2xl font-semibold md:text-3xl">
            Select a city to book a hotel.
        </h1>
        <p class="text-lg"> We offer {{ number_format(totalHotel(), 0, '.', ',') }}
            hotels
            across
            {{ number_format($cities->count(), 0, '.', ',') }}
            cities and towns in
            Nigeria.
        </p>
        <hr class="text-center w-[100px] bg-primary mx-auto mt-2 p-[1.5px] border-0">
    </div>
    <div class="grid grid-cols-1 gap-4 sm:col-span-2 md:grid-cols-3 lg:grid-cols-4">
        @forelse ($cities as $city)
            <a class="block p-3 rounded space-y-1 border transition duration-300 transform hover:shadow-2xl"
                href="">
                <h1 class="text-lg font-semibold capitalize text-primary">
                    {{ 'Hotels in ' . $city->name . ', ' . $city->state->name }}</h1>
                <p class="text">There are {{ $city->hotels_count }} hotels in {{ $city->name }}, featuring over
                    {{ $city->ratings_count }}
                    verified reviews and
                    {{ $city->bookings_count }} bookings.</p>
            </a>
        @empty
            <div>
                <p>No city with hotel yet</p>
            </div>
        @endforelse
    </div>
    <div class="max-w-4xl mx-auto">
        {{ $cities->links(data: ['scrollTo' => '#city']) }}
    </div>
</div>
