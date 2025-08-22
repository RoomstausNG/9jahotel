<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto space-y-8">
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
          Hotel Components Demo
        </h1>
        <p class="text-gray-600">
          Showcasing HotelCard and HotelList components
        </p>
      </div>

      <!-- Single Hotel Card Demo -->
      <div class="space-y-4">
        <h2 class="text-2xl font-semibold text-gray-900">Single Hotel Card</h2>
        <div class="">
          <HotelCard
            :hotel="sampleHotel"
            @favorite-toggled="handleFavoriteToggle"
            @hotel-clicked="handleHotelClick"
          />
        </div>
      </div>

      <!-- Hotel List Demo - Grid View -->
      <div class="space-y-4">
        <h2 class="text-2xl font-semibold text-gray-900">
          Hotel List - Grid View
        </h2>
        <HotelList
          :hotels="sampleHotels"
          :loading="false"
          title="Sample Hotels"
          :show-header="true"
          :enable-sort="true"
          :enable-view-toggle="true"
          default-view-mode="grid"
          @favorite-toggle="handleFavoriteToggle"
          @hotel-click="handleHotelClick"
          @sort-change="handleSortChange"
        />
      </div>

      <!-- Hotel List Demo - Loading State -->
      <div class="space-y-4">
        <h2 class="text-2xl font-semibold text-gray-900">
          Hotel List - Loading State
        </h2>
        <HotelList
          :hotels="[]"
          :loading="true"
          title="Loading Hotels..."
          :show-header="true"
          :skeleton-count="6"
        />
      </div>

      <!-- Hotel List Demo - Empty State -->
      <div class="space-y-4">
        <h2 class="text-2xl font-semibold text-gray-900">
          Hotel List - Empty State
        </h2>
        <HotelList
          :hotels="[]"
          :loading="false"
          title="No Results"
          :show-header="true"
          empty-state-title="No hotels found in this area"
          empty-state-message="Try searching in a different location or adjusting your filters."
        />
      </div>

      <!-- Action Log -->
      <div class="space-y-4">
        <h2 class="text-2xl font-semibold text-gray-900">Action Log</h2>
        <div
          class="bg-white rounded-lg p-4 border border-gray-200 max-h-48 overflow-y-auto"
        >
          <div v-if="actionLog.length === 0" class="text-gray-500 text-sm">
            No actions yet. Click on hotels or favorite buttons to see
            interactions.
          </div>
          <div v-else class="space-y-2">
            <div
              v-for="(action, index) in actionLog.slice().reverse()"
              :key="index"
              class="text-sm p-2 bg-gray-50 rounded border-l-4 border-blue-500"
            >
              <div class="font-medium">{{ action.type }}</div>
              <div class="text-gray-600">{{ action.message }}</div>
              <div class="text-xs text-gray-400">{{ action.timestamp }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Sample hotel data
const sampleHotel = {
  id: 1,
  name: "Lagos Continental Hotel",
  address: "1234 Victoria Island, Lagos, Nigeria",
  city: { name: "Lagos" },
  roomPrice: 25000,
  originalPrice: 30000,
  image: "https://images.unsplash.com/photo-1566073771259-6a8506099945",
  starRating: 4,
  reviews: {
    total: 89,
    averageRating: 4.3,
  },
  amenities: ["wifi", "breakfast", "pool", "parking"],
  discount: 15,
};

const sampleHotels = [
  {
    id: 1,
    name: "Lagos Continental Hotel",
    address: "Victoria Island, Lagos",
    city: { name: "Lagos" },
    roomPrice: 25000,
    originalPrice: 30000,
    image: "https://images.unsplash.com/photo-1566073771259-6a8506099945",
    starRating: 4,
    reviews: {
      total: 89,
      averageRating: 4.3,
    },
    amenities: ["wifi", "breakfast", "pool", "parking"],
    discount: 15,
  },
  {
    id: 2,
    name: "Abuja Grand Resort",
    address: "Central Business District, Abuja",
    city: { name: "Abuja" },
    roomPrice: 18000,
    originalPrice: null,
    image: "https://images.unsplash.com/photo-1560347876-aeef00ee58a1",
    starRating: 5,
    reviews: {
      total: 156,
      averageRating: 4.7,
    },
    amenities: ["wifi", "breakfast", "pool"],
  },
  {
    id: 3,
    name: "Port Harcourt Business Hotel",
    address: "GRA Phase 2, Port Harcourt",
    city: { name: "Port Harcourt" },
    roomPrice: 22000,
    originalPrice: 25000,
    image: "https://images.unsplash.com/photo-1582719478250-c89cae4dc85b",
    starRating: 3,
    reviews: {
      total: 45,
      averageRating: 4.1,
    },
    amenities: ["wifi", "parking"],
    discount: 10,
  },
  {
    id: 4,
    name: "Kano Heritage Inn",
    address: "Sabon Gari, Kano",
    city: { name: "Kano" },
    roomPrice: 12000,
    originalPrice: null,
    image: "https://images.unsplash.com/photo-1631049307264-da0ec9d70304",
    starRating: 3,
    reviews: {
      total: 23,
      averageRating: 3.8,
    },
    amenities: ["wifi"],
  },
  {
    id: 5,
    name: "Ibadan City Lodge",
    address: "Ring Road, Ibadan",
    city: { name: "Ibadan" },
    roomPrice: 15000,
    originalPrice: 18000,
    image: "https://images.unsplash.com/photo-1542314831-068cd1dbfeeb",
    starRating: 2,
    reviews: {
      total: 67,
      averageRating: 3.9,
    },
    amenities: ["wifi", "breakfast"],
    discount: 20,
  },
  {
    id: 6,
    name: "Enugu Mountain View",
    address: "Independence Layout, Enugu",
    city: { name: "Enugu" },
    roomPrice: 20000,
    originalPrice: null,
    image: "https://images.unsplash.com/photo-1520250497591-112f2f40a3f4",
    starRating: 4,
    reviews: {
      total: 112,
      averageRating: 4.4,
    },
    amenities: ["wifi", "breakfast", "pool", "parking"],
  },
];

// Action log for demonstrating interactions
const actionLog = ref([]);

// Toast for notifications
const toast = useToast();

// Event handlers
const handleFavoriteToggle = (data) => {
  const action = {
    type: "Favorite Toggle",
    message: `${data.hotel.name} ${
      data.isFavorite ? "added to" : "removed from"
    } favorites`,
    timestamp: new Date().toLocaleTimeString(),
  };
  actionLog.value.push(action);

  toast.add({
    title: data.isFavorite ? "Added to favorites" : "Removed from favorites",
    description: action.message,
    color: data.isFavorite ? "green" : "gray",
  });
};

const handleHotelClick = (hotel) => {
  const action = {
    type: "Hotel Clicked",
    message: `Clicked on ${hotel.name} - would navigate to hotel details`,
    timestamp: new Date().toLocaleTimeString(),
  };
  actionLog.value.push(action);

  toast.add({
    title: "Hotel Selected",
    description: `Viewing details for ${hotel.name}`,
    color: "blue",
  });
};

const handleSortChange = (sortValue) => {
  const action = {
    type: "Sort Changed",
    message: `Changed sort to: ${sortValue}`,
    timestamp: new Date().toLocaleTimeString(),
  };
  actionLog.value.push(action);

  toast.add({
    title: "Sort Applied",
    description: `Hotels sorted by ${sortValue}`,
    color: "blue",
  });
};

// SEO
useHead({
  title: "Hotel Components Demo - 9jahotel",
  meta: [
    {
      name: "description",
      content: "Demo page showcasing HotelCard and HotelList components.",
    },
  ],
});
</script>
