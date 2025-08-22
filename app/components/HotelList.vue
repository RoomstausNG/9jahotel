<template>
  <div class="space-y-4">
    <!-- List Header -->
    <div v-if="showHeader" class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-semibold text-gray-900">
          {{ title || "Hotels" }}
        </h2>
        <p
          v-if="hotels && hotels.length > 0"
          class="text-sm text-gray-600 mt-1"
        >
          {{ hotels.length }} hotel{{ hotels.length !== 1 ? "s" : "" }} found
        </p>
      </div>

      <!-- Sort and View Toggle -->
      <div class="flex items-center gap-3">
        <!-- Sort Dropdown -->
        <USelect
          v-if="enableSort"
          v-model="selectedSort"
          :options="sortOptions"
          placeholder="Sort by"
          size="sm"
          class="w-40"
        />

        <!-- View Toggle -->
        <div
          v-if="enableViewToggle"
          class="flex items-center bg-gray-100 p-1 rounded-lg"
        >
          <button
            class="p-2 rounded transition-colors"
            :class="
              viewMode === 'grid'
                ? 'bg-white shadow-sm text-gray-900'
                : 'text-gray-500 hover:text-gray-700'
            "
            @click="viewMode = 'grid'"
          >
            <Icon name="i-lucide-grid-3x3" class="w-4 h-4" />
          </button>
          <button
            class="p-2 rounded transition-colors"
            :class="
              viewMode === 'list'
                ? 'bg-white shadow-sm text-gray-900'
                : 'text-gray-500 hover:text-gray-700'
            "
            @click="viewMode = 'list'"
          >
            <Icon name="i-lucide-list" class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="space-y-4">
      <div
        v-for="i in skeletonCount"
        :key="i"
        class="animate-pulse bg-gray-200 rounded-xl h-64"
      />
    </div>

    <!-- Empty State -->
    <div v-else-if="!hotels || hotels.length === 0" class="text-center py-12">
      <Icon
        name="i-lucide-building"
        class="w-16 h-16 text-gray-300 mx-auto mb-4"
      />
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        {{ emptyStateTitle || "No hotels found" }}
      </h3>
      <p class="text-gray-600 max-w-md mx-auto">
        {{
          emptyStateMessage ||
          "Try adjusting your search criteria or explore different destinations."
        }}
      </p>
    </div>

    <!-- Hotels Grid/List -->
    <div
      v-else
      :class="[
        'transition-all duration-200',
        viewMode === 'grid'
          ? 'grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4'
          : 'space-y-4',
      ]"
      class="px-1"
    >
      <HotelCard
        v-for="hotel in sortedHotels"
        :key="hotel.id || hotel.name"
        :hotel="hotel"
        :class="viewMode === 'list' ? 'max-w-none' : ''"
        @favorite-toggled="handleFavoriteToggle"
        @hotel-clicked="handleHotelClick"
      />
    </div>

    <!-- Load More Button -->
    <div v-if="enableLoadMore && hasMore" class="text-center pt-6">
      <UButton
        :loading="loadingMore"
        variant="outline"
        size="lg"
        @click="loadMore"
      >
        Load More Hotels
      </UButton>
    </div>

    <!-- Pagination -->
    <div
      v-if="enablePagination && totalPages > 1"
      class="flex justify-center pt-6"
    >
      <UPagination
        v-model="currentPage"
        :page-count="pageSize"
        :total="totalHotels"
        :max="5"
        show-last
        show-first
      />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  hotels: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: "",
  },
  showHeader: {
    type: Boolean,
    default: true,
  },
  enableSort: {
    type: Boolean,
    default: true,
  },
  enableViewToggle: {
    type: Boolean,
    default: true,
  },
  enableLoadMore: {
    type: Boolean,
    default: false,
  },
  enablePagination: {
    type: Boolean,
    default: false,
  },
  hasMore: {
    type: Boolean,
    default: false,
  },
  loadingMore: {
    type: Boolean,
    default: false,
  },
  totalHotels: {
    type: Number,
    default: 0,
  },
  pageSize: {
    type: Number,
    default: 12,
  },
  skeletonCount: {
    type: Number,
    default: 8,
  },
  emptyStateTitle: {
    type: String,
    default: "",
  },
  emptyStateMessage: {
    type: String,
    default: "",
  },
  defaultViewMode: {
    type: String,
    default: "grid",
    validator: (value) => ["grid", "list"].includes(value),
  },
  defaultSort: {
    type: String,
    default: "featured",
  },
});

const emit = defineEmits([
  "favorite-toggle",
  "hotel-click",
  "load-more",
  "page-change",
  "sort-change",
]);

// View mode state
const viewMode = ref(props.defaultViewMode);

// Sort state
const selectedSort = ref(props.defaultSort);
const sortOptions = [
  { label: "Featured", value: "featured" },
  { label: "Price: Low to High", value: "price_asc" },
  { label: "Price: High to Low", value: "price_desc" },
  { label: "Rating: High to Low", value: "rating_desc" },
  { label: "Distance", value: "distance" },
  { label: "Name: A to Z", value: "name_asc" },
];

// Pagination state
const currentPage = ref(1);
const totalPages = computed(() =>
  Math.ceil(props.totalHotels / props.pageSize)
);

// Sorted hotels
const sortedHotels = computed(() => {
  if (!props.hotels || props.hotels.length === 0) return [];

  const hotels = [...props.hotels];

  switch (selectedSort.value) {
    case "price_asc":
      return hotels.sort(
        (a, b) => (a.roomPrice || a.price || 0) - (b.roomPrice || b.price || 0)
      );
    case "price_desc":
      return hotels.sort(
        (a, b) => (b.roomPrice || b.price || 0) - (a.roomPrice || a.price || 0)
      );
    case "rating_desc":
      return hotels.sort(
        (a, b) =>
          (b.reviews?.averageRating || 0) - (a.reviews?.averageRating || 0)
      );
    case "name_asc":
      return hotels.sort((a, b) => (a.name || "").localeCompare(b.name || ""));
    case "distance":
      return hotels.sort((a, b) => (a.distance || 0) - (b.distance || 0));
    case "featured":
    default:
      return hotels;
  }
});

// Event handlers
const handleFavoriteToggle = (data) => {
  emit("favorite-toggle", data);
};

const handleHotelClick = (hotel) => {
  emit("hotel-click", hotel);
};

const loadMore = () => {
  emit("load-more");
};

// Watch for sort changes
watch(selectedSort, (newSort) => {
  emit("sort-change", newSort);
});

// Watch for page changes
watch(currentPage, (newPage) => {
  emit("page-change", newPage);
});

// Save view mode preference
watch(viewMode, (newMode) => {
  if (import.meta.client) {
    localStorage.setItem("hotel-list-view-mode", newMode);
  }
});

// Restore view mode preference on mount
onMounted(() => {
  if (import.meta.client) {
    const savedViewMode = localStorage.getItem("hotel-list-view-mode");
    if (savedViewMode && ["grid", "list"].includes(savedViewMode)) {
      viewMode.value = savedViewMode;
    }
  }
});
</script>

<style scoped>
/* Custom animations for view mode transitions */
.grid > * {
  animation: fadeInUp 0.3s ease-out;
}

.space-y-4 > * {
  animation: fadeInLeft 0.3s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInLeft {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}
</style>
