<template>
  <div class="h-full flex flex-col">
    <!-- Fixed Header - Always visible -->
    <div
      class="flex-shrink-0 gap-x-4 flex justify-between items-start p-4 bg-gray-900/95 backdrop-blur-sm border-b border-gray-700 z-[60] w-full"
    >
      <UButton
        icon="i-lucide-arrow-left"
        variant="ghost"
        color="neutral"
        @click="$router.back()"
      />
      <div class="flex flex-1 justify-center items-center gap-2">
        <div class="">
          <h1 class="font-semibold text-center text-white text-sm">
            {{ hotel?.name || "Hotel" }}
          </h1>
          <p class="text-xs text-gray-300">
            Select the room category you will like to book
          </p>
        </div>
      </div>
      <div class="w-10" />
      <!-- Spacer for centering -->
    </div>

    <!-- Content Area - Scrollable -->
    <div class="flex-1 overflow-hidden h-screen">
      <div
        v-if="pending"
        class="flex items-center justify-center h-full bg-white/20 backdrop-blur-sm"
      >
        <div class="text-center space-y-6 max-w-md mx-auto px-4">
          <div
            class="w-20 h-20 mx-auto bg-primary-100 rounded-full flex items-center justify-center animate-pulse"
          >
            <Icon name="i-lucide-bed" class="w-10 h-10 text-primary-600" />
          </div>
          <h2 class="font-semibold text-white">Loading Room Categories</h2>
          <div class="w-full bg-gray-700 rounded-full h-1">
            <div
              class="bg-primary-500 h-1 rounded-full animate-pulse"
              style="width: 70%"
            />
          </div>
        </div>
      </div>
      <div
        v-else-if="error"
        class="flex items-center justify-center h-full bg-white/20 backdrop-blur-sm"
      >
        <div class="text-center">
          <Icon
            name="i-lucide-alert-triangle"
            class="w-12 h-12 mx-auto text-red-500"
          />
          <h2 class="mt-4 text-lg font-semibold text-white">
            Could not load room categories
          </h2>
          <p class="mt-2 text-sm text-gray-400">{{ error }}</p>
          <UButton class="mt-4" @click="$router.back()">Go Back</UButton>
        </div>
      </div>
      <div v-else class="h-full bg-white/30 backdrop-blur-sm overflow-y-auto">
        <div class="px-4 py-4">
          <div class="grid grid-cols-2 gap-4">
            <div
              v-for="room in categories"
              :key="room.id"
              :class="[
                'bg-black/20 backdrop-blur-2xl border-2 border-primary rounded-lg shadow-sm px-4 pb-2 pt-8 relative transition-all',
                room.availableRoom > 0
                  ? 'cursor-pointer hover:border-primary-500 hover:shadow-lg'
                  : 'opacity-60 cursor-not-allowed',
              ]"
              @click="
                room.availableRoom > 0
                  ? selectRoom(room)
                  : showUnavailableToast()
              "
            >
              <!-- Available Room Badge -->
              <div
                v-if="typeof room.availableRoom === 'number'"
                class="absolute top-1 right-2 z-10"
              >
                <span
                  v-if="room.availableRoom > 0"
                  :class="'bg-green-500 text-white text-xs font-bold px-2 py-0.5 rounded shadow'"
                >
                  {{ room.availableRoom }}
                </span>
                <span
                  v-else
                  :class="'bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded shadow'"
                >
                  Not Available
                </span>
              </div>
              <!-- Room Icon removed -->

              <!-- Room Details -->
              <div class="text-center space-y-2">
                <h2 class="font-medium text-black text-sm leading-tight">
                  {{ room.name }}
                </h2>

                <!-- Price -->
                <div class="space-y-1">
                  <div class="flex my-4 flex-col justify-center gap-1">
                    <span
                      v-if="room.onlinePercentage > 0"
                      class="text-xs text-red-600 line-through block"
                    >
                      ₦{{ room.price.toLocaleString() }}
                    </span>
                    <span
                      class="text-primary-600 block leading-4 font-bold text-lg"
                    >
                      ₦{{
                        calculateDiscountedPrice(
                          room.price,
                          room.onlinePercentage
                        ).toLocaleString()
                      }}
                    </span>
                    <span class="text-xs text-neutral-100 block"
                      >per night</span
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const userStore = useUserStore();
const user = computed(() => userStore.user);

definePageMeta({ layout: false });
setPageLayout(computed(() => (user.value ? "auth" : "default")));

const route = useRoute();
const config = useRuntimeConfig();
const baseURL = config.public.apiBase || "/api";

const hotelCode = route.params.code;
const categories = ref([]);
const pending = ref(true);
const error = ref(null);

// Use hotel composable to fetch hotel data
const { hotel, loading: _hotelLoading, fetchHotel } = useHotel(hotelCode);

const checkIn = ref("");
const checkOut = ref("");

onMounted(() => {
  // Get checkIn/checkOut from localStorage
  if (typeof window !== "undefined") {
    try {
      const params = JSON.parse(localStorage.getItem("hotelSearchParams"));
      checkIn.value = params?.checkIn || "";
      checkOut.value = params?.checkOut || "";
    } catch {
      checkIn.value = "";
      checkOut.value = "";
    }
  }

  // Fetch both hotel data and categories
  fetchHotel();
  fetchCategories();
});

const fetchCategories = async () => {
  pending.value = true;
  error.value = null;
  try {
    const url = `${baseURL}/hotels/${hotelCode}/categories?checkIn=${checkIn.value}&checkOut=${checkOut.value}`;
    const res = await $fetch(url);
    if (res.success) {
      categories.value = res.data;
    } else {
      throw new Error(res.message || "Failed to fetch categories");
    }
  } catch (err) {
    error.value = err.message || "Failed to load categories.";
  } finally {
    pending.value = false;
  }
};

// Calculate discounted price
const calculateDiscountedPrice = (price, percentage) => {
  if (typeof percentage !== "number" || percentage <= 0) {
    return price;
  }
  const discount = (price * percentage) / 100;
  return price - discount;
};

// Select room function
const selectRoom = (room) => {
  // Save category info, hotel info, and hotel background image to localStorage
  if (typeof window !== "undefined") {
    const currentParams = JSON.parse(
      localStorage.getItem("hotelSearchParams") || "{}"
    );
    let hotelBgImage = "";
    if (hotel.value) {
      hotelBgImage =
        hotel.value.backgroundImage ||
        hotel.value.bgImage ||
        hotel.value.background ||
        hotel.value.image ||
        hotel.value.imageUrl ||
        hotel.value.coverImage ||
        (Array.isArray(hotel.value.images) ? hotel.value.images[0] : "");
    }
    const updatedParams = {
      ...currentParams,
      categoryName: room.name,
      hotelName: hotel.value?.name || "Hotel",
      hotelImage: hotelBgImage,
    };
    localStorage.setItem("hotelSearchParams", JSON.stringify(updatedParams));
  }

  // Navigate to room selection page
  navigateTo(`/hotels/${hotelCode}/categories/${room.id}/rooms`);
};
</script>

<style scoped>
img {
  background: #f3f4f6;
}
</style>
