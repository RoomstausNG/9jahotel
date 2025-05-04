<div class="py-8">
    <h1 class="text-2xl font-bold mb-4">Favorites</h1>
    <div class="mb-4">
        <label for="city" class="font-semibold">Filter by City:</label>
        <select wire:model="city" id="city" class="border rounded px-2 py-1">
            <option value="">All Cities</option>
            @foreach ($cities as $city)
                <option value="{{ $city->id }}">{{ $city->name }}</option>
            @endforeach
        </select>
    </div>
    <table class="min-w-full bg-white border border-gray-200 rounded shadow">
        <thead>
            <tr>
                <th class="px-4 py-2 border-b">Hotel</th>
                <th class="px-4 py-2 border-b">City</th>
                <th class="px-4 py-2 border-b">Address</th>
                <th class="px-4 py-2 border-b">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($favorites as $favorite)
                <tr>
                    <td class="px-4 py-2 border-b">{{ $favorite->hotel->name ?? '-' }}</td>
                    <td class="px-4 py-2 border-b">{{ $favorite->hotel->city->name ?? '-' }}</td>
                    <td class="px-4 py-2 border-b">{{ $favorite->hotel->address ?? '-' }}</td>
                    <td class="px-4 py-2 border-b">
                        <button wire:click="unfavorite({{ $favorite->id }})"
                            class="text-red-600 hover:underline">Unfavorite</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-4">No favorite hotels found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
