<template>
  <div
    v-if="pending"
    class="flex items-center justify-center bg-gray-50"
    :class="{ 'h-screen': !user, 'h-full min-h-[calc(100vh-150px)]': user }"
  >
    <!-- Enhanced Loading State similar to search page -->
    <div class="text-center space-y-6 max-w-md mx-auto px-4">
      <!-- Animated hotel icon -->
      <div class="relative">
        <div
          class="w-20 h-20 mx-auto bg-primary-100 rounded-full flex items-center justify-center animate-pulse"
        >
          <Icon name="i-lucide-building-2" class="w-10 h-10 text-primary-600" />
        </div>
        <div
          class="absolute -top-2 -right-2 w-6 h-6 bg-primary-500 rounded-full animate-bounce"
        />
      </div>

      <!-- Loading text with animation -->
      <div class="space-y-2">
        <h2 class="text-xl font-semibold text-gray-800">
          Loading Hotel Details
        </h2>
        <p class="text-gray-600">
          Please wait while we fetch the information...
        </p>
      </div>

      <!-- Animated dots -->
      <div class="flex justify-center space-x-1">
        <div
          class="w-2 h-2 bg-primary-500 rounded-full animate-bounce"
          style="animation-delay: 0ms"
        />
        <div
          class="w-2 h-2 bg-primary-500 rounded-full animate-bounce"
          style="animation-delay: 150ms"
        />
        <div
          class="w-2 h-2 bg-primary-500 rounded-full animate-bounce"
          style="animation-delay: 300ms"
        />
      </div>

      <!-- Loading progress bar -->
      <div class="w-full bg-gray-200 rounded-full h-1">
        <div
          class="bg-primary-500 h-1 rounded-full animate-pulse"
          style="width: 70%"
        />
      </div>

      <!-- Skeleton cards -->
      <div class="space-y-3 pt-4">
        <div class="h-4 bg-gray-200 rounded animate-pulse" />
        <div class="h-4 bg-gray-200 rounded animate-pulse w-3/4" />
        <div class="h-4 bg-gray-200 rounded animate-pulse w-1/2" />
      </div>
    </div>
  </div>

  <div
    v-else-if="error || !hotel"
    class="flex items-center justify-center"
    :class="{ 'h-screen': !user, 'h-full min-h-[calc(100vh-150px)]': user }"
  >
    <div class="text-center">
      <Icon
        name="i-lucide-alert-triangle"
        class="w-12 h-12 mx-auto text-red-500"
      />
      <h2 class="mt-4 text-lg font-semibold">Could not load hotel details</h2>
      <p class="mt-2 text-sm text-gray-600">
        {{ error?.message || "The hotel data is unavailable." }}
      </p>
      <UButton class="mt-4" @click="$router.back()">Go Back</UButton>
    </div>
  </div>

  <div v-else class="h-full flex flex-col">
    <!-- Sticky Header -->
    <header
      class="sticky top-0 left-0 right-0 z-40 flex items-center justify-between p-4 bg-primary/50 backdrop-blur-sm text-white"
    >
      <UButton
        icon="i-lucide-arrow-left"
        variant="ghost"
        color="white"
        @click="$router.back()"
      />
      <div class="flex items-center space-x-3">
        <UButton
          icon="i-lucide-share-2"
          variant="ghost"
          class="-mt-1"
          color="white"
        />
        <FavoriteToggle
          v-if="hotel"
          :hotel-id="hotel.id"
          :initial-favorite="hotel.isFavourite"
          @update:is-favorite="isFavorite = $event"
        />
      </div>
    </header>

    <!-- Scrollable Main Content -->
    <main class="flex-1 overflow-y-auto">
      <!-- Image Gallery with Auto-change -->
      <div class="relative h-64">
        <!-- Discount Badge -->
        <div
          v-if="hotel.onlinePercentage && hotel.onlinePercentage > 0"
          class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded z-10"
        >
          {{ hotel.onlinePercentage }}% OFF
        </div>

        <NuxtImg
          :src="mainImage"
          :alt="hotel.name"
          class="object-cover w-full h-full transition-opacity duration-500"
        />
        <div
          class="absolute bottom-4 right-4 bg-black/50 text-white px-2 py-1 rounded-md text-xs"
        >
          {{ activeImageIndex + 1 }} / {{ hotel.images.length }}
        </div>
      </div>
      <div class="p-2 bg-white">
        <div class="flex space-x-2 overflow-x-auto">
          <NuxtImg
            v-for="(img, index) in hotel.images"
            :key="index"
            :src="img"
            alt="Thumbnail"
            class="w-16 h-12 object-cover rounded-md cursor-pointer transition-all duration-200"
            :class="{
              'border-2 border-primary-500': activeImageIndex === index,
            }"
            @click="setActiveImage(index)"
          />
        </div>
      </div>

      <!-- Main Content -->
      <div class="p-4 space-y-6 backdrop-blur-sm">
        <!-- Hotel Info -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
          <h1 class="text-lg font-bold leading-4">{{ hotel.name }}</h1>
          <div
            v-if="hotel.starRating"
            class="flex items-center gap-2 mt-2 text-sm text-gray-600"
          >
            <Icon name="i-lucide-star" class="text-yellow-500" />
            <span>{{ hotel.starRating }}</span>
            <span>({{ hotel.reviews.total }} reviews)</span>
          </div>
          <div class="flex gap-2 mt-1 text-sm text-gray-600">
            <Icon name="i-lucide-map-pin" />
            <span class="leading-4 text-xs">{{ hotel.address }}</span>
            <UButton
              v-if="hasValidCoordinates"
              variant="link"
              size="xs"
              @click="showMap = true"
            >
              View on Map
            </UButton>
          </div>
          <div class="mt-4 space-y-1">
            <div class="flex flex-col">
              <span
                v-if="hotel.onlinePercentage && hotel.onlinePercentage > 0"
                class="text-lg font-medium text-gray-500 line-through"
              >
                ₦{{ formatPrice(hotel.roomPrice) }}
              </span>
              <div class="flex items-baseline space-x-1">
                <span class="text-2xl font-bold text-primary-600">
                  ₦{{
                    formatPrice(
                      calculateDiscountedPrice(
                        hotel.roomPrice,
                        hotel.onlinePercentage
                      )
                    )
                  }}
                </span>
                <span class="text-sm font-normal text-gray-500">/night</span>
              </div>
            </div>
          </div>
          <UBadge color="success" variant="subtle" class="mt-2"
            >Available</UBadge
          >
        </div>

        <!-- Amenities with smaller icons -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
          <h2 class="text-lg font-semibold">Amenities</h2>
          <div class="grid grid-cols-4 gap-3 mt-4 text-center">
            <div
              v-for="amenity in visibleAmenities"
              :key="amenity.name"
              class="flex flex-col items-center"
            >
              <div
                class="flex items-center justify-center w-6 h-6 rounded-full bg-primary-100 text-primary-600"
                v-html="amenity.icon"
              />
              <span class="mt-1 text-xs text-gray-600 leading-tight">{{
                amenity.name
              }}</span>
            </div>
          </div>
          <UButton
            v-if="hotel.amenities.length > 4"
            variant="link"
            class="mt-4"
            @click="showAllAmenities = !showAllAmenities"
          >
            {{ showAllAmenities ? "Show less" : "View all amenities" }}
          </UButton>
        </div>

        <!-- About (hidden if description is empty) -->
        <div
          v-if="hotel.description && hotel.description.trim() !== ''"
          class="bg-white p-4 rounded-lg shadow-sm"
        >
          <h2 class="text-lg font-semibold">About {{ hotel.name }}</h2>
          <div
            class="prose max-w-none mt-2 text-gray-600 text-sm"
            v-html="hotel.description"
          />
        </div>

        <!-- Guest Reviews (hidden if reviews is empty string) -->
        <div
          v-if="
            hotel.reviews &&
            typeof hotel.reviews === 'object' &&
            hotel.reviews.list &&
            hotel.reviews.list.length > 0
          "
          class="bg-white p-4 rounded-lg shadow-sm"
        >
          <h2 class="text-lg font-semibold">Guest Reviews</h2>
          <!-- Review summary and list here -->
        </div>
      </div>
      <!-- Location -->
      <div v-if="hasValidCoordinates" class="bg-white p-4 rounded-lg shadow-sm">
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-lg font-semibold">Location</h2>
          <UButton
            variant="ghost"
            size="xs"
            icon="i-lucide-external-link"
            @click="showMap = true"
          >
            View Full Map
          </UButton>
        </div>
        <div
          class="mt-2 h-48 rounded-lg overflow-hidden cursor-pointer"
          @click="showMap = true"
        >
          <div
            :id="`map-preview-${hotel.id}`"
            class="w-full h-full bg-gray-200 flex items-center justify-center"
          >
            <div class="text-center">
              <Icon
                name="i-lucide-map-pin"
                class="w-8 h-8 mx-auto text-gray-400 mb-2"
              />
              <p class="text-gray-500">Click to view on map</p>
            </div>
          </div>
        </div>
        <div class="mt-2 text-sm text-gray-600">
          <Icon name="i-lucide-map-pin" class="inline w-4 h-4 mr-1" />
          {{ hotel.address }}, {{ hotel.city.name }}
        </div>
      </div>
    </main>

    <!-- Sticky Footer -->
    <footer
      class="sticky bottom-0 left-0 right-0 bg-white p-4 border-t shadow-lg flex items-center justify-between z-40"
    >
      <div>
        <div class="space-y-1">
          <span
            v-if="hotel.onlinePercentage && hotel.onlinePercentage > 0"
            class="text-sm font-medium text-gray-500 line-through block"
          >
            ₦{{ formatPrice(hotel.roomPrice) }}
          </span>
          <div class="flex items-baseline space-x-1">
            <span class="text-xs">from</span>
            <p class="font-bold text-lg">
              ₦{{
                formatPrice(
                  calculateDiscountedPrice(
                    hotel.roomPrice,
                    hotel.onlinePercentage
                  )
                )
              }}
            </p>
          </div>
        </div>
        <p class="text-xs text-gray-500">Including taxes and fees</p>
      </div>
      <UButton :to="`/hotels/${hotelCode}/categories`" variant="solid"
        >Reserve Now</UButton
      >
    </footer>

    <!-- Map Modal -->
    <UModal
      v-if="hasValidCoordinates"
      v-model:open="showMap"
      :title="`${hotel.name} Location`"
      :ui="{ content: 'w-full max-w-4xl' }"
    >
      <template #body>
        <div class="space-y-4">
          <div class="h-96 rounded-lg overflow-hidden">
            <div :id="`map-modal-${hotel.id}`" class="w-full h-full" />
          </div>
          <div class="text-sm text-gray-600">
            <Icon name="i-lucide-map-pin" class="inline w-4 h-4 mr-1" />
            {{ hotel.address }}, {{ hotel.city.name }}
          </div>
        </div>
      </template>
    </UModal>
  </div>
</template>

<script setup>
const userStore = useUserStore();
const user = computed(() => userStore.user);

// Conditionally set layout based on authentication status
definePageMeta({
  layout: false,
});

// Set layout dynamically based on user authentication
setPageLayout(computed(() => (user.value ? "auth" : "default")));

const route = useRoute();
const config = useRuntimeConfig();
const baseURL = config.public.apiBase || "/api";

const hotelCode = route.params.code;

const { data, pending, error } = await useFetch(
  `${baseURL}/hotels/${hotelCode}`
);

const hotel = computed(() => data.value?.data);

const isFavorite = ref(hotel.value?.isFavourite || false);
const activeImageIndex = ref(0);
const showAllAmenities = ref(false);
const showMap = ref(false);
let modalMap = null;

// Auto-change images every 5 seconds
let imageChangeInterval = null;

const startImageAutoChange = () => {
  if (hotel.value?.images && hotel.value.images.length > 1) {
    imageChangeInterval = setInterval(() => {
      activeImageIndex.value =
        (activeImageIndex.value + 1) % hotel.value.images.length;
    }, 5000);
  }
};

const stopImageAutoChange = () => {
  if (imageChangeInterval) {
    clearInterval(imageChangeInterval);
    imageChangeInterval = null;
  }
};

const setActiveImage = (index) => {
  stopImageAutoChange();
  activeImageIndex.value = index;
  // Restart auto-change after user interaction
  setTimeout(startImageAutoChange, 5000);
};

// Load Leaflet CSS and JS
const loadLeaflet = () => {
  return new Promise((resolve) => {
    if (import.meta.client && window.L) {
      resolve();
      return;
    }

    if (import.meta.client) {
      // Load CSS
      const cssLink = document.createElement("link");
      cssLink.rel = "stylesheet";
      cssLink.href = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css";
      cssLink.integrity = "sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=";
      cssLink.crossOrigin = "";
      document.head.appendChild(cssLink);

      // Load JS
      const script = document.createElement("script");
      script.src = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js";
      script.integrity = "sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=";
      script.crossOrigin = "";
      script.onload = resolve;
      document.head.appendChild(script);
    }
  });
};

// Initialize map
const initializeMap = async (containerId, zoom = 15) => {
  if (!hasValidCoordinates.value || !import.meta.client) return;

  await loadLeaflet();
  await nextTick();

  const container = document.getElementById(containerId);
  if (!container || !window.L) return;

  const lat = parseFloat(hotel.value.latitude);
  const lng = parseFloat(hotel.value.longitude);

  // Clear existing map if any
  if (container._leaflet_id) {
    container._leaflet_id = null;
    container.innerHTML = "";
  }

  const map = window.L.map(containerId).setView([lat, lng], zoom);

  window.L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
  }).addTo(map);

  const marker = window.L.marker([lat, lng]).addTo(map);

  const popupContent = `
    <div class="text-center">
      <h3 class="font-semibold">${hotel.value.name}</h3>
      <p class="text-sm text-gray-600">${hotel.value.address}</p>
      ${
        hotel.value.starRating
          ? `<div class="mt-2 text-xs text-gray-500">
        <span>${hotel.value.starRating}⭐</span>
        ${
          hotel.value.reviews?.total
            ? ` • ${hotel.value.reviews.total} reviews`
            : ""
        }
      </div>`
          : ""
      }
    </div>
  `;

  marker.bindPopup(popupContent).openPopup();

  return map;
};

onMounted(async () => {
  startImageAutoChange();
  if (hasValidCoordinates.value) {
    await loadLeaflet();
  }

  // Save hotel details to localStorage as soon as available
  if (hotel.value) {
    try {
      // Save the whole hotel object for later use
      localStorage.setItem("currentHotelDetails", JSON.stringify(hotel.value));
      // Save background image for booking summary page
      const bgImage =
        hotel.value.image ||
        (hotel.value.images && hotel.value.images[0]) ||
        "";
      localStorage.setItem("hotelImage", bgImage);
    } catch {
      // Ignore storage errors
    }
  }
});
// Watch for hotel data becoming available and save to localStorage immediately
watch(
  () => hotel.value,
  (newHotel) => {
    if (newHotel) {
      try {
        localStorage.setItem("currentHotelDetails", JSON.stringify(newHotel));
        const bgImage =
          newHotel.image || (newHotel.images && newHotel.images[0]) || "";
        localStorage.setItem("hotelImage", bgImage);
      } catch {}
    }
  },
  { immediate: true }
);

onUnmounted(() => {
  stopImageAutoChange();
  if (modalMap) {
    modalMap.remove();
    modalMap = null;
  }
});

// Watch for modal open to initialize map
watch(showMap, async (isOpen) => {
  if (isOpen && hasValidCoordinates.value) {
    await nextTick();
    modalMap = await initializeMap(`map-modal-${hotel.value.id}`, 16);
  } else if (modalMap) {
    modalMap.remove();
    modalMap = null;
  }
});

const mainImage = computed(
  () => hotel.value?.images[activeImageIndex.value] || hotel.value?.image
);

const visibleAmenities = computed(() => {
  if (!hotel.value) return [];
  return showAllAmenities.value
    ? hotel.value.amenities
    : hotel.value.amenities.slice(0, 4);
});

// Check if hotel has valid coordinates for map display
const hasValidCoordinates = computed(() => {
  return (
    hotel.value?.latitude &&
    hotel.value?.longitude &&
    hotel.value.latitude !== null &&
    hotel.value.longitude !== null &&
    !isNaN(parseFloat(hotel.value.latitude)) &&
    !isNaN(parseFloat(hotel.value.longitude))
  );
});

// Calculate discounted price
const calculateDiscountedPrice = (price, percentage) => {
  if (typeof percentage !== "number" || percentage <= 0) {
    return price;
  }
  const discount = (price * percentage) / 100;
  return price - discount;
};

// Format price with commas for display
const formatPrice = (price) => {
  return price.toLocaleString();
};

useHead({
  title: computed(() => hotel.value?.name || "Hotel Details"),
});
</script>

<style scoped>
/* Custom animations for loading state */
@keyframes bounce {
  0%,
  20%,
  53%,
  80%,
  100% {
    transform: translateY(0);
  }
  40%,
  43% {
    transform: translateY(-8px);
  }
  70% {
    transform: translateY(-4px);
  }
  90% {
    transform: translateY(-2px);
  }
}

.animate-bounce {
  animation: bounce 1.5s infinite;
}

/* Smooth transitions for images */
.transition-opacity {
  transition: opacity 0.5s ease-in-out;
}

/* Additional styles for smaller SVG icons */
.prose svg {
  display: inline-block;
  width: 1em;
  height: 1em;
  margin-right: 0.25em;
}

/* Amenity icons sizing */
[v-html] svg {
  width: 16px !important;
  height: 16px !important;
}
</style>
