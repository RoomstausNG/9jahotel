<template>
  <div
    class="h-full overflow-y-auto px-4 pb-8 space-y-6 bg-gradient-to-b from-white to-cyan-50"
  >
    <div
      class="flex items-center justify-between mb-4 sticky top-0 z-10 bg-white/80 backdrop-blur border-b border-gray-200"
    >
      <h1 class="text-2xl font-semibold text-gray-900 py-2">My Favorites</h1>
      <div class="flex gap-2">
        <UButton
          icon="i-lucide-refresh-cw"
          variant="ghost"
          color="primary"
          @click="fetchHotels"
        />
        <UButton icon="i-lucide-sort-asc" variant="ghost" color="primary" />
      </div>
    </div>
    <div
      v-if="hotels.length === 0 && !loading"
      class="text-center py-12 text-gray-500"
    >
      No favorite hotels found.
    </div>
    <div class="grid grid-cols-1 gap-6">
      <HotelCard
        v-for="hotel in hotels"
        :key="hotel.id"
        :hotel="hotel"
        @favorite-toggle="handleFavoriteToggle"
        @hotel-click="handleHotelClick"
      />
    </div>
    <div v-if="loading" class="text-center py-6">
      <ULoading size="lg" />
    </div>
    <div v-if="!loading && hasMore" class="flex justify-center mt-6">
      <UButton
        label="Load More"
        color="primary"
        :loading="loadingMore"
        @click="loadMore"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: "auth",
});

const hotels = ref([]);
const page = ref(1);
const perPage = 12;
const loading = ref(false);
const loadingMore = ref(false);
const hasMore = ref(true);

const config = useRuntimeConfig();
const baseURL = config.public.apiBase || "/api";
const userStore = useUserStore();

const fetchHotels = async (isLoadMore = false) => {
  if (isLoadMore) {
    loadingMore.value = true;
  } else {
    loading.value = true;
    hotels.value = [];
    page.value = 1;
    hasMore.value = true;
  }
  try {
    const endpoint = `${baseURL}/hotels/favourites?page=${page.value}&perPage=${perPage}`;
    const response = await $fetch(endpoint, {
      headers: {
        Authorization: userStore.token ? `Bearer ${userStore.token}` : "",
      },
    });
    if (response?.success && Array.isArray(response.data?.hotels)) {
      if (isLoadMore) {
        hotels.value.push(...response.data.hotels);
      } else {
        hotels.value = response.data.hotels;
      }
      hasMore.value = response.data.hotels.length === perPage;
      page.value += 1;
    } else {
      hasMore.value = false;
    }
  } catch (error) {
    hasMore.value = false;
  } finally {
    loading.value = false;
    loadingMore.value = false;
  }
};

const loadMore = () => {
  if (hasMore.value && !loadingMore.value) {
    fetchHotels(true);
  }
};

const handleFavoriteToggle = () => {
  fetchHotels();
};

const handleHotelClick = (hotel: any) => {
  // Optionally navigate to hotel details
};

onMounted(() => {
  fetchHotels();
});
</script>

<style scoped>
.bg-gradient-to-b {
  background: linear-gradient(180deg, #ffffff 0%, #f0ffff 100%);
}
</style>
