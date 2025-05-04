<div class="container mx-auto py-8">
    <h2 class="text-2xl font-bold mb-6">Customer Dashboard</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-5 gap-6">
        <!-- Favorite Hotels -->
        <div class="rounded-xl p-6 h-40 flex flex-col justify-between relative overflow-hidden"
            style="background: linear-gradient(135deg, #ffb6b9 0%, #fae3d9 100%);">
            <div class="flex justify-between items-start">
                <div class="text-white text-lg font-medium">Favorite Hotels</div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white opacity-80" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 21.364l-7.682-7.682a4.5 4.5 0 010-6.364z" />
                </svg>
            </div>
            <div class="text-white text-3xl font-bold">{{ $favorites ?? 0 }}</div>
            <div class="text-white text-sm opacity-80">Increased by 20%</div>
            <div class="absolute right-0 bottom-0 w-32 h-32 bg-white opacity-10 rounded-full -mb-12 -mr-12"></div>
        </div>
        <!-- Total Bookings -->
        <div class="rounded-xl p-6 h-40 flex flex-col justify-between relative overflow-hidden"
            style="background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);">
            <div class="flex justify-between items-start">
                <div class="text-white text-lg font-medium">Total Bookings</div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white opacity-80" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="text-white text-3xl font-bold">{{ $totalBookings ?? 0 }}</div>
            <div class="text-white text-sm opacity-80">Increased by 10%</div>
            <div class="absolute right-0 bottom-0 w-32 h-32 bg-white opacity-10 rounded-full -mb-12 -mr-12"></div>
        </div>
        <!-- Active Bookings -->
        <div class="rounded-xl p-6 h-40 flex flex-col justify-between relative overflow-hidden"
            style="background: linear-gradient(135deg, #b2f7c1 0%, #f0fff3 100%);">
            <div class="flex justify-between items-start">
                <div class="text-gray-700 text-lg font-medium">Active Bookings</div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700 opacity-80" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2l4-4m6 2a9 9 0 11-18 0a9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="text-gray-700 text-3xl font-bold">{{ $activeBookings ?? 0 }}</div>
            <div class="text-gray-700 text-sm opacity-80">Increased by 5%</div>
            <div class="absolute right-0 bottom-0 w-32 h-32 bg-white opacity-10 rounded-full -mb-12 -mr-12"></div>
        </div>
        <!-- Canceled Bookings -->
        <div class="rounded-xl p-6 h-40 flex flex-col justify-between relative overflow-hidden"
            style="background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);">
            <div class="flex justify-between items-start">
                <div class="text-white text-lg font-medium">Canceled Bookings</div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white opacity-80" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <div class="text-white text-3xl font-bold">{{ $canceledBookings ?? 0 }}</div>
            <div class="text-white text-sm opacity-80">Decreased by 8%</div>
            <div class="absolute right-0 bottom-0 w-32 h-32 bg-white opacity-10 rounded-full -mb-12 -mr-12"></div>
        </div>
        <!-- Completed Bookings -->
        <div class="rounded-xl p-6 h-40 flex flex-col justify-between relative overflow-hidden"
            style="background: linear-gradient(135deg, #fceabb 0%, #f8b500 100%);">
            <div class="flex justify-between items-start">
                <div class="text-white text-lg font-medium">Completed Bookings</div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white opacity-80" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="text-white text-3xl font-bold">{{ $completedBookings ?? 0 }}</div>
            <div class="text-white text-sm opacity-80">Increased by 15%</div>
            <div class="absolute right-0 bottom-0 w-32 h-32 bg-white opacity-10 rounded-full -mb-12 -mr-12"></div>
        </div>
    </div>
</div>
