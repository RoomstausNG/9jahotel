<div class="container mx-auto py-8">
    <h2 class="text-2xl font-bold mb-6">Customer Dashboard</h2>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <!-- Favorite Hotels -->
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-4xl font-bold text-pink-600 mb-2">{{ $favoriteHotels ?? 0 }}</div>
            <div class="text-gray-600">Favorite Hotels</div>
        </div>
        <!-- Total Bookings -->
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-4xl font-bold text-blue-600 mb-2">{{ $totalBookings ?? 0 }}</div>
            <div class="text-gray-600">Total Bookings</div>
        </div>
        <!-- Active/Current Bookings -->
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-4xl font-bold text-green-600 mb-2">{{ $activeBookings ?? 0 }}</div>
            <div class="text-gray-600">Active Bookings</div>
        </div>
        <!-- Canceled Bookings -->
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-4xl font-bold text-red-600 mb-2">{{ $canceledBookings ?? 0 }}</div>
            <div class="text-gray-600">Canceled Bookings</div>
        </div>
        <!-- Completed Bookings -->
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-4xl font-bold text-yellow-600 mb-2">{{ $completedBookings ?? 0 }}</div>
            <div class="text-gray-600">Completed Bookings</div>
        </div>
    </div>
</div>
