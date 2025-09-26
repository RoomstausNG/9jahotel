<template>
  <div class="h-full flex flex-col">
    <!-- Fixed Header - Always visible -->
    <div
      class="flex-shrink-0 gap-x-4 flex justify-between gap-4 items-start p-4 bg-gray-900/95 backdrop-blur-sm border-b border-gray-700 z-[60] w-full"
    >
      <UButton
        icon="i-lucide-arrow-left"
        variant="ghost"
        color="white"
        @click="$router.back()"
      />
      <div class="flex flex-1 justify-center items-center gap-2">
        <div class="">
          <h1 class="font-semibold text-center text-white text-sm">
            {{ hotelName || "Hotel" }}
          </h1>
          <p class="text-xs text-gray-300">Select Your Room</p>
        </div>
      </div>
      <div class="w-10" />
      <!-- Spacer for centering -->
    </div>

    <!-- Category Badge -->
    <div class="flex-shrink-0 px-4 py-2 bg-white">
      <div
        class="bg-primary-500 text-white text-xs px-3 py-1 rounded-full w-fit"
      >
        {{ categoryName || "Standard Category" }}
      </div>
    </div>

    <!-- Room Grid Section -->
    <div class="flex-1 overflow-hidden bg-black/50">
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
          <h2 class="font-semibold text-white">Loading Rooms</h2>
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
            Could not load rooms
          </h2>
          <p class="mt-2 text-sm text-gray-400">{{ error }}</p>
          <UButton class="mt-4" @click="$router.back()">Go Back</UButton>
        </div>
      </div>
      <div v-else class="h-full overflow-y-auto">
        <div class="px-4 text-center py-6">
          <!-- Available Rooms Section -->
          <h3 class="text-white font-medium mb-4 leading-1 pb-0 m-0">
            Available Rooms
          </h3>
          <!-- Debug info -->
          <p class="text-white text-xs mb-2">You can select multiple room</p>
          <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 pt-4">
            <div
              v-for="room in rooms"
              :key="room.id"
              :class="[
                'relative aspect-square rounded-lg overflow-hidden cursor-pointer border-2 transition-all',
                isRoomSelected(room)
                  ? 'border-primary-500 ring-2 ring-primary-500/50'
                  : 'border-white hover:border-gray-500',
              ]"
              :style="{
                backgroundImage: `url(${
                  room.imageUrl ||
                  'https://res.cloudinary.com/dcm83lp3b/image/upload/v1752508514/slide_image_upd9ht.webp'
                })`,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
              }"
              @click="toggleRoomSelection(room)"
            >
              <!-- Room Number -->
              <div
                class="absolute top-2 left-2 bg-black/70 text-white text-xs px-2 py-1 rounded"
              >
                {{ room.number }}
              </div>

              <!-- Room Details -->
              <div
                class="absolute bottom-0 left-0 right-0 bg-black/70 text-white p-2"
              >
                <div class="text-xs font-medium text-center">Select</div>
              </div>

              <!-- Selected Indicator -->
              <div
                v-if="isRoomSelected(room)"
                class="absolute top-2 right-2 bg-primary-500 text-white rounded-full w-6 h-6 flex items-center justify-center"
              >
                <Icon name="i-lucide-check" class="w-3 h-3" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Selected Rooms Display -->
    <div
      v-if="selectedRooms.length > 0"
      class="flex-shrink-0 px-4 py-2 bg-gray-800/80 border-t border-gray-700"
    >
      <div class="text-center">
        <p class="text-sm text-gray-300 mb-2">
          Room
          <span class="font-semibold text-white">
            {{ selectedRooms.map((r) => r.number).join(", ") }}
          </span>
          selected
        </p>
      </div>
    </div>

    <!-- Fixed Continue Button -->
    <div class="flex-shrink-0 p-4">
      <UButton
        :disabled="selectedRooms.length === 0"
        :color="selectedRooms.length === 0 ? 'neutral' : 'primary'"
        variant="solid"
        size="lg"
        class="w-full block"
        @click="continueToBooking"
      >
        Continue
      </UButton>
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

const categoryId = route.params.id;
const rooms = ref([]);
const selectedRooms = ref([]);
const pending = ref(true);
const error = ref(null);

// Data from localStorage
const hotelName = ref("");
const categoryName = ref("");
const checkIn = ref("");
const checkOut = ref("");

onMounted(() => {
  // Get data from localStorage
  if (typeof window !== "undefined") {
    try {
      const params = JSON.parse(localStorage.getItem("hotelSearchParams"));
      checkIn.value = params?.checkIn || "";
      checkOut.value = params?.checkOut || "";
      hotelName.value = params?.hotelName || "";
      categoryName.value = params?.categoryName || "";
    } catch {
      checkIn.value = "";
      checkOut.value = "";
      hotelName.value = "";
      categoryName.value = "";
    }
  }

  fetchRooms();
});

const fetchRooms = async () => {
  pending.value = true;
  error.value = null;
  try {
    const url = `${baseURL}/hotels/categories/${categoryId}/rooms?checkIn=${checkIn.value}&checkOut=${checkOut.value}`;
    const res = await $fetch(url);
    if (res.success) {
      rooms.value = res.data;
      console.log("Rooms fetched:", res.data);
    } else {
      throw new Error(res.message || "Failed to fetch rooms");
    }
  } catch (err) {
    error.value = err.message || "Failed to load rooms.";
  } finally {
    pending.value = false;
  }
};

const toggleRoomSelection = (room) => {
  const index = selectedRooms.value.findIndex((r) => r.id === room.id);
  if (index > -1) {
    selectedRooms.value.splice(index, 1);
  } else {
    selectedRooms.value.push(room);
  }
};

const isRoomSelected = (room) => {
  return selectedRooms.value.some((r) => r.id === room.id);
};

const continueToBooking = () => {
  if (selectedRooms.value.length === 0) return;

  // Save selected rooms to localStorage
  if (typeof window !== "undefined") {
    localStorage.setItem("selectedRooms", JSON.stringify(selectedRooms.value));

    // Update search params with additional booking data
    const currentParams = JSON.parse(
      localStorage.getItem("hotelSearchParams") || "{}"
    );
    const updatedParams = {
      ...currentParams,
      selectedRoomCount: selectedRooms.value.length,
      totalRooms: selectedRooms.value.length,
    };
    localStorage.setItem("hotelSearchParams", JSON.stringify(updatedParams));
  }

  // Navigate to booking page
  navigateTo(`/hotels/${route.params.code}/booking`);
};
</script>
