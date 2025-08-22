<template>
  <div class="h-screen flex flex-col bg-white/30 backdrop-blur-sm">
    <!-- Fixed Header with Search -->
    <div class="bg-white shadow-sm border-b flex-shrink-0 sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-4 space-y-4">
          <!-- Back Button and Title -->
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <UButton
                icon="i-lucide-arrow-left"
                variant="ghost"
                color="neutral"
                @click="$router.go(-1)"
              />
              <h1 class="font-semibold leading-4 text-neutral-900">
                Hotels in {{ searchParams?.cityName || "Selected City" }}
              </h1>
            </div>
          </div>

          <!-- Unified Search Bar -->
          <div class="relative">
            <UInput
              v-model="hotelNameSearch"
              placeholder="Search hotels by name..."
              size="lg"
              class="w-full"
              :loading="hotelsLoading"
              @keyup.enter="handleSearch"
            >
              <template #trailing>
                <div class="flex items-center space-x-1">
                  <UButton
                    v-if="hotelNameSearch"
                    icon="i-lucide-x"
                    variant="ghost"
                    color="neutral"
                    size="xs"
                    @click="clearHotelSearch"
                  />
                  <UButton
                    label="Submit"
                    color="primary"
                    size="xs"
                    :loading="hotelsLoading"
                    @click="handleSearch"
                  />
                </div>
              </template>
            </UInput>
          </div>

          <!-- Pagination Controls with Filter and Sort -->
          <div
            v-if="pagination && pagination.total_pages > 0"
            class="flex justify-between items-center pt-2"
          >
            <div class="flex items-center gap-2">
              <UButton
                icon="i-lucide-chevron-left"
                variant="outline"
                color="neutral"
                :disabled="!pagination?.prev_page || hotelsLoading"
                @click="goToPage((pagination?.current_page || 1) - 1)"
              />
              <span class="text-sm text-neutral-600">
                <template v-if="hotelsLoading">
                  Loading page {{ targetPage || "unknown" }}...
                </template>
                <template v-else>
                  Page {{ pagination?.current_page || 1 }} of
                  {{ pagination?.total_pages || 1 }}
                </template>
              </span>
              <UButton
                icon="i-lucide-chevron-right"
                variant="outline"
                color="neutral"
                :disabled="!pagination?.next_page || hotelsLoading"
                @click="goToPage((pagination?.current_page || 1) + 1)"
              />
            </div>

            <!-- Filter and Sort Controls -->
            <div class="flex items-center gap-2">
              <UButton
                icon="i-lucide-filter"
                variant="outline"
                color="neutral"
                size="sm"
                @click="showFilterModal = true"
              />
              <UButton
                icon="i-lucide-arrow-up-down"
                variant="outline"
                color="neutral"
                size="sm"
                @click="showSortModal = true"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto pb-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Loading State -->
        <div v-if="hotelsLoading" class="space-y-4">
          <div v-for="i in 6" :key="i" class="animate-pulse">
            <div class="bg-white rounded-lg p-4 space-y-3 shadow-sm">
              <div class="flex space-x-4">
                <div
                  class="w-24 h-24 bg-neutral-300 rounded-lg flex-shrink-0"
                />
                <div class="flex-1 space-y-2">
                  <div class="h-4 bg-neutral-300 rounded w-3/4" />
                  <div class="h-3 bg-neutral-300 rounded w-1/2" />
                  <div class="h-3 bg-neutral-300 rounded w-1/4" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-12">
          <Icon
            name="i-lucide-alert-triangle"
            class="w-12 h-12 text-red-500 mx-auto mb-4"
          />
          <h3 class="text-lg font-medium text-neutral-900 mb-2">
            Failed to load hotels
          </h3>
          <p class="text-neutral-600 mb-4">{{ error }}</p>
          <UButton label="Try Again" color="primary" @click="searchHotels()" />
        </div>

        <!-- No Results -->
        <div
          v-else-if="!hotelsLoading && hotels.length === 0"
          class="text-center py-12"
        >
          <Icon
            name="i-lucide-search-x"
            class="w-12 h-12 text-neutral-400 mx-auto mb-4"
          />
          <h3 class="text-lg font-medium text-neutral-900 mb-2">
            No hotels found
          </h3>
          <p class="text-neutral-600 mb-4">
            {{
              hotelNameSearch
                ? `No hotels match "${hotelNameSearch}" in ${
                    searchParams?.cityName || "this city"
                  }`
                : `No hotels available in ${
                    searchParams?.cityName || "this city"
                  } for the selected dates`
            }}
          </p>
          <UButton
            v-if="hotelNameSearch"
            label="Clear Search"
            variant="outline"
            color="neutral"
            @click="clearHotelSearch"
          />
        </div>

        <!-- Hotels List -->
        <div v-else class="space-y-6">
          <!-- Hotels Grid -->
          <div class="grid grid-cols-1 gap-4">
            <HotelCard
              v-for="hotel in hotels"
              :key="hotel?.id || Math.random()"
              :hotel="hotel"
              @favorite-toggled="handleFavoriteToggle"
              @hotel-clicked="handleHotelClick"
            />
          </div>

          <!-- Load More Button -->
          <div
            v-if="pagination && pagination.has_more_pages && !hotelsLoading"
            class="flex justify-center pt-2"
          >
            <UButton
              label="Load More"
              variant="solid"
              color="primary"
              :loading="loadingMore"
              @click="loadMore"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Modal -->
    <UModal v-model:open="showFilterModal" title="Filter Hotels">
      <template #body>
        <div class="space-y-4">
          <div class="flex gap-4">
            <div class="flex-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Min Price (₦)
              </label>
              <UInput
                v-model="tempFilters.minPrice"
                type="number"
                placeholder="0"
                min="0"
              />
            </div>
            <div class="flex-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Max Price (₦)
              </label>
              <UInput
                v-model="tempFilters.maxPrice"
                type="number"
                placeholder="1000000"
                min="0"
              />
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <div class="flex justify-between w-full">
          <UButton
            label="Reset Filters"
            variant="outline"
            color="neutral"
            @click="resetFilters"
          />
          <div class="flex gap-2">
            <UButton
              label="Cancel"
              variant="outline"
              color="neutral"
              @click="showFilterModal = false"
            />
            <UButton
              label="Apply Filters"
              color="primary"
              @click="applyFilters"
            />
          </div>
        </div>
      </template>
    </UModal>

    <!-- Sort Modal -->
    <UModal v-model:open="showSortModal" title="Sort Hotels">
      <template #body>
        <div class="space-y-3">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
              Sort By
            </label>

            <!-- Try USelect first -->
            <USelect
              v-if="false"
              v-model="tempSort.selectedOption"
              :items="availableSortOptions"
              placeholder="Select sort option"
              value-key="value"
              class="w-full"
            />

            <!-- Fallback custom select -->
            <div v-else class="relative">
              <select
                v-model="tempSort.selectedOption"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-sm appearance-none cursor-pointer"
              >
                <option value="" disabled selected>Select sort option</option>
                <option
                  v-for="option in availableSortOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
              <!-- Custom dropdown arrow -->
              <div
                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
              >
                <Icon
                  name="i-lucide-chevron-down"
                  class="w-4 h-4 text-gray-400"
                />
              </div>
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <div class="flex justify-between w-full">
          <UButton
            label="Reset Sort"
            variant="outline"
            color="neutral"
            @click="resetSort"
          />
          <div class="flex gap-2">
            <UButton
              label="Cancel"
              variant="outline"
              color="neutral"
              @click="showSortModal = false"
            />
            <UButton label="Apply Sort" color="primary" @click="applySort" />
          </div>
        </div>
      </template>
    </UModal>
  </div>
</template>

<script setup lang="ts">
import type { Hotel } from "~/types/hotel";

// Get user data from your auth store/composable
const userStore = useUserStore();
const user = computed(() => userStore.user);

// Conditionally set layout based on authentication status
definePageMeta({
  layout: false,
});

setPageLayout(computed(() => (user.value ? "auth" : "default")));

// Define types for API response
interface HotelApiResponse {
  success: boolean;
  message: string;
  data: {
    hotels: Hotel[];
    meta: {
      pagination: {
        total: number;
        count: number;
        per_page: number;
        current_page: number;
        total_pages: number;
        has_more_pages: boolean;
        first_page: boolean;
        last_page: boolean;
        next_page: number | null;
        prev_page: number | null;
      };
    };
  };
}

// Toast for notifications
const toast = useToast();

// Router
const router = useRouter();

// API Base URL
const config = useRuntimeConfig();
const baseURL = config.public.apiBase || "/api";

// Search parameters interface
interface SearchParams {
  checkIn: string;
  checkOut: string;
  cityId: number;
  cityName?: string;
  adults?: number;
  rooms?: number;
  search?: string;
  sortBy?: string;
  sortOrder?: string;
  minPrice?: number;
  maxPrice?: number;
}

// Reactive state
const searchParams = ref<SearchParams>({
  checkIn: "",
  checkOut: "",
  cityId: 0,
  cityName: "",
  adults: 1,
  rooms: 1,
  search: "",
  sortBy: "",
  sortOrder: "",
  minPrice: undefined,
  maxPrice: undefined,
});

const hotels = ref<Hotel[]>([]);
const hotelsLoading = ref(false);
const error = ref<string | null>(null);
const hotelNameSearch = ref("");
const currentPage = ref(1);
const targetPage = ref(1); // Track which page we're trying to load
const perPage = ref(24);
const _perPageOptions = [10, 20, 50, 100];
const pagination = ref<HotelApiResponse["data"]["meta"]["pagination"] | null>(
  null
);

// Filter and Sort Modal States
const showFilterModal = ref(false);
const showSortModal = ref(false);
const loadingMore = ref(false);

// Temporary filter and sort states
const tempFilters = ref({
  minPrice: "",
  maxPrice: "",
});

const tempSort = ref({
  selectedOption: "",
});

// Sort options - user-friendly labels with API keys
const sortOptions = [
  { label: "Price (Low to High)", value: "room_lowest_price", order: "asc" },
  { label: "Price (High to Low)", value: "room_lowest_price", order: "desc" },
  { label: "Star Rating", value: "star_rating", order: "desc" },
  { label: "Hotel Name", value: "name", order: "asc" },
  { label: "Distance", value: "distance", order: "asc" }, // Only show when position is enabled
];

// Geolocation availability check
const isGeolocationAvailable = ref(false);
const _checkGeolocation = () => {
  if (import.meta.client && "geolocation" in navigator) {
    // Check if user has granted location permission
    navigator.permissions
      ?.query({ name: "geolocation" })
      .then((result) => {
        isGeolocationAvailable.value = result.state === "granted";
      })
      .catch(() => {
        // If permissions API is not available, assume geolocation is available
        isGeolocationAvailable.value = true;
      });
  }
};

// Computed available sort options (can be filtered based on conditions)
const availableSortOptions = computed(() => {
  const options = sortOptions.filter((option) => {
    // Filter out distance option if position is not available
    if (option.value === "distance") {
      // Only show distance option if geolocation is available
      return isGeolocationAvailable.value;
    }
    return true;
  });

  const mappedOptions = options.map((option) => ({
    label: option.label,
    value: `${option.value}|${option.order}`, // Combine value and order for easy handling
  }));

  console.log("Available sort options:", mappedOptions);
  return mappedOptions;
});

// Active request tracking to prevent multiple simultaneous requests
const activeRequest = ref<AbortController | null>(null);

// Error handling for component updates
const componentError = ref<string | null>(null);

// Load search parameters from localStorage
const loadSearchParams = () => {
  if (import.meta.client) {
    try {
      const stored = localStorage.getItem("hotelSearchParams");
      if (stored) {
        const parsed = JSON.parse(stored);
        searchParams.value = {
          checkIn: parsed.checkIn || "",
          checkOut: parsed.checkOut || "",
          cityId: parsed.cityId || 0,
          cityName: parsed.cityName || "",
          adults: parsed.adults || 1,
          rooms: parsed.rooms || 1,
          search: parsed.search || "",
          sortBy: parsed.sortBy || "",
          sortOrder: parsed.sortOrder || "",
          minPrice: parsed.minPrice || undefined,
          maxPrice: parsed.maxPrice || undefined,
        };
        hotelNameSearch.value = parsed.search || "";

        // Update temp states
        tempFilters.value = {
          minPrice: parsed.minPrice ? parsed.minPrice.toString() : "",
          maxPrice: parsed.maxPrice ? parsed.maxPrice.toString() : "",
        };

        // Find matching sort option and set selected option
        const matchingSortOption = sortOptions.find(
          (option) =>
            option.value === parsed.sortBy && option.order === parsed.sortOrder
        );
        tempSort.value = {
          selectedOption: matchingSortOption
            ? `${matchingSortOption.value}|${matchingSortOption.order}`
            : "",
        };
      }
    } catch (err) {
      console.error("Failed to load search params from localStorage:", err);
    }
  }
};

// Search hotels function with improved error handling
const searchHotels = async (page = 1, loadMore = false) => {
  if (!searchParams.value || !searchParams.value.cityId) {
    error.value = "No city selected for search";
    return;
  }

  // Cancel any existing request
  if (activeRequest.value) {
    activeRequest.value.abort();
    activeRequest.value = null;
  }

  // Create new abort controller for this request
  activeRequest.value = new AbortController();

  if (loadMore) {
    loadingMore.value = true;
  } else {
    hotelsLoading.value = true;
  }

  targetPage.value = page;
  error.value = null;

  try {
    const queryParams = new URLSearchParams({
      checkIn: searchParams.value.checkIn,
      checkOut: searchParams.value.checkOut,
      page: page.toString(),
      perpage: perPage.value.toString(),
    });

    if (hotelNameSearch.value.trim()) {
      queryParams.append("search", hotelNameSearch.value.trim());
    }

    // Add sort parameters
    if (searchParams.value.sortBy) {
      queryParams.append("sortBy", searchParams.value.sortBy);
    }
    if (searchParams.value.sortOrder) {
      queryParams.append("sortOrder", searchParams.value.sortOrder);
    }

    // Add filter parameters
    if (searchParams.value.minPrice) {
      queryParams.append("minPrice", searchParams.value.minPrice.toString());
    }
    if (searchParams.value.maxPrice) {
      queryParams.append("maxPrice", searchParams.value.maxPrice.toString());
    }

    console.log(`Fetching hotels for page ${page}...`);
    console.log("Query params:", Object.fromEntries(queryParams));
    console.log(
      "Full URL:",
      `${baseURL}/hotels/city/${searchParams.value.cityId}?${queryParams}`
    );
    const startTime = Date.now();

    const response = await $fetch<HotelApiResponse>(
      `${baseURL}/hotels/city/${searchParams.value.cityId}?${queryParams}`,
      {
        timeout: 30000,
        signal: activeRequest.value?.signal,
      }
    );

    // Check if request was aborted
    if (activeRequest.value?.signal.aborted) {
      console.log("Request was aborted");
      return;
    }

    const endTime = Date.now();
    const responseTime = endTime - startTime;
    console.log(`API request took ${responseTime}ms`);

    if (response.success) {
      if (loadMore) {
        // Append new hotels to existing list
        hotels.value = [...hotels.value, ...response.data.hotels];
      } else {
        // Replace hotels list
        hotels.value = response.data.hotels;
      }

      pagination.value = response.data.meta.pagination;
      currentPage.value = page;

      console.log(
        `Successfully loaded page ${page} with ${response.data.hotels.length} hotels`
      );
    } else {
      throw new Error(response.message || "Failed to fetch hotels");
    }
  } catch (err: unknown) {
    // Don't show error for aborted requests
    if (activeRequest.value?.signal.aborted) {
      console.log("Request was aborted, ignoring error");
      return;
    }

    console.error("Error fetching hotels:", err);

    if (err instanceof Error) {
      if (err.message.includes("timeout")) {
        error.value =
          "Request timed out. Please check your internet connection and try again.";
      } else if (err.message.includes("fetch")) {
        error.value = "Network error. Please check your internet connection.";
      } else {
        error.value = err.message;
      }
    } else {
      error.value = "Failed to load hotels. Please try again.";
    }

    if (!loadMore) {
      hotels.value = [];
      pagination.value = null;
    }

    if (page > 1) {
      toast.add({
        title: "Loading Error",
        description: "Failed to load the next page. Please try again.",
        color: "error",
      });
    }
  } finally {
    hotelsLoading.value = false;
    loadingMore.value = false;
    activeRequest.value = null;
  }
};

// Load more hotels function
const loadMore = async () => {
  if (!pagination.value || !pagination.value.next_page) {
    return;
  }

  await searchHotels(pagination.value.next_page, true);
};

// Clear hotel name search
const clearHotelSearch = () => {
  hotelNameSearch.value = "";
  handleSearch();
};

// Handle search with debouncing
const searchTimeout = ref<NodeJS.Timeout | null>(null);
const handleSearch = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }

  searchTimeout.value = setTimeout(() => {
    searchHotels(1);
  }, 300);
};

// Filter functions
const applyFilters = () => {
  // Apply temp filters to search params
  searchParams.value.minPrice = tempFilters.value.minPrice
    ? parseInt(tempFilters.value.minPrice)
    : undefined;
  searchParams.value.maxPrice = tempFilters.value.maxPrice
    ? parseInt(tempFilters.value.maxPrice)
    : undefined;

  // Close modal and search
  showFilterModal.value = false;
  searchHotels(1);

  // toast.add({
  //   title: "Filters Applied",
  //   description: "Hotels filtered successfully",
  //   color: "success",
  // });
};

const resetFilters = () => {
  // Reset temp filters
  tempFilters.value = {
    minPrice: "",
    maxPrice: "",
  };

  // Reset search params
  searchParams.value.minPrice = undefined;
  searchParams.value.maxPrice = undefined;

  // Close modal and search
  showFilterModal.value = false;
  searchHotels(1);

  toast.add({
    title: "Filters Reset",
    description: "All filters have been cleared",
    color: "neutral",
  });
};

// Sort functions
const applySort = () => {
  if (!tempSort.value.selectedOption) {
    return;
  }

  // Parse the selected option (format: "value|order")
  const [sortBy, sortOrder] = tempSort.value.selectedOption.split("|");

  // Apply temp sort to search params
  searchParams.value.sortBy = sortBy;
  searchParams.value.sortOrder = sortOrder;

  // Close modal and search
  showSortModal.value = false;
  searchHotels(1);

  // Find the option label for the toast
  const selectedOption = sortOptions.find(
    (option) => option.value === sortBy && option.order === sortOrder
  );

  // toast.add({
  //   title: "Sort Applied",
  //   description: `Hotels sorted by ${
  //     selectedOption?.label || "selected option"
  //   }`,
  //   color: "success",
  // });
};

const resetSort = () => {
  // Reset temp sort
  tempSort.value = {
    selectedOption: "",
  };

  // Reset search params
  searchParams.value.sortBy = "";
  searchParams.value.sortOrder = "";

  // Close modal and search
  showSortModal.value = false;
  searchHotels(1);

  toast.add({
    title: "Sort Reset",
    description: "Default sorting applied",
    color: "neutral",
  });
};

// Handle per page change
const _handlePerPageChange = () => {
  searchHotels(1);
};

// Simplified pagination handler without debouncing
const goToPage = (page: number) => {
  console.log(
    `Attempting to go to page ${page}, current loading state: ${hotelsLoading.value}`
  );

  // Prevent navigation if already loading or invalid page
  if (hotelsLoading.value) {
    console.log("Already loading, ignoring pagination request");
    return;
  }

  if (!pagination.value) {
    console.log("No pagination data available");
    return;
  }

  // Ensure page is a valid number
  if (typeof page !== "number" || isNaN(page)) {
    console.log(`Invalid page number: ${page}`);
    return;
  }

  if (page < 1 || page > pagination.value.total_pages) {
    console.log(
      `Invalid page ${page}, total pages: ${pagination.value.total_pages}`
    );
    return;
  }

  if (page === currentPage.value) {
    console.log(`Already on page ${page}`);
    return;
  }

  console.log(`Navigating to page ${page}`);
  searchHotels(page);
};

// Handle favorite toggle
const handleFavoriteToggle = (data: {
  hotelId: number;
  isFavorite: boolean;
}) => {
  console.log("Favorite toggled:", data);

  try {
    const hotelIndex = hotels.value.findIndex((h) => h.id === data.hotelId);
    if (hotelIndex !== -1 && hotels.value[hotelIndex]) {
      hotels.value[hotelIndex].isFavourite = data.isFavorite;
    }

    const hotel = hotels.value[hotelIndex];
    toast.add({
      title: data.isFavorite ? "Added to favorites" : "Removed from favorites",
      description: `${hotel?.name || "Hotel"} ${
        data.isFavorite ? "added to" : "removed from"
      } your favorites.`,
      color: data.isFavorite ? "success" : "neutral",
    });
  } catch (error) {
    console.error("Error toggling favorite:", error);
    toast.add({
      title: "Error",
      description: "Failed to update favorite status. Please try again.",
      color: "error",
    });
  }
};

// Handle hotel click
const handleHotelClick = (hotel: Hotel) => {
  console.log("Hotel clicked:", hotel);

  if (!hotel || !hotel.code) {
    console.error("Invalid hotel data:", hotel);
    toast.add({
      title: "Error",
      description: "Invalid hotel data. Please try again.",
      color: "error",
    });
    return;
  }

  try {
    router.push(`/hotels/${hotel.code}`);
  } catch (error) {
    console.error("Error navigating to hotel:", error);
    toast.add({
      title: "Navigation Error",
      description: "Failed to navigate to hotel details. Please try again.",
      color: "error",
    });
  }
};

// Handle component errors
const _handleComponentError = (error: Error) => {
  console.error("Component error:", error);
  componentError.value = error.message;
  // Show user-friendly error message
  toast.add({
    title: "Application Error",
    description: "Something went wrong. Please refresh the page.",
    color: "error",
  });
};

// Add global error handler for unhandled errors
const _errorHandler = (error: Error, instance: unknown, errorInfo: string) => {
  console.error("Vue error caught:", error, errorInfo);

  // Handle specific error types
  if (error.message.includes("Cannot read properties of null")) {
    console.warn(
      "Null reference error - likely accessing reactive data before initialization"
    );
  }

  // Prevent error propagation in production
  if (process.env.NODE_ENV === "production") {
    toast.add({
      title: "Something went wrong",
      description: "Please refresh the page and try again.",
      color: "error",
    });
  }
};

// Use nextTick to ensure DOM updates are handled properly
const _safeNextTick = async (callback: () => void) => {
  try {
    await nextTick();
    callback();
  } catch (error) {
    console.error("Error in nextTick callback:", error);
    _errorHandler(error as Error, null, "nextTick callback");
  }
};

// Set up error handling
onErrorCaptured((error, instance, info) => {
  _errorHandler(error, instance, info);
  return false; // Prevent error from propagating
});

// Cleanup on unmount
onUnmounted(() => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }
  if (activeRequest.value) {
    activeRequest.value.abort();
  }
});

// Load search params and fetch hotels on mount
onMounted(async () => {
  try {
    // Check geolocation availability
    _checkGeolocation();

    loadSearchParams();

    // Debug available sort options
    console.log("Sort options on mount:", availableSortOptions.value);
    console.log("Temp sort on mount:", tempSort.value);

    // Ensure searchParams is properly initialized
    if (!searchParams.value?.cityId) {
      error.value = "No search parameters found. Please start a new search.";
      return;
    }

    // Initialize target page
    targetPage.value = 1;

    // Fetch hotels with error handling
    await searchHotels();
  } catch (err) {
    console.error("Error during component mount:", err);
    error.value = "Failed to initialize search. Please refresh the page.";
  }
});

// Watch for changes in pagination to handle reactive updates safely
watch(
  () => pagination.value,
  (newPagination) => {
    if (newPagination) {
      // Ensure currentPage is within valid range
      if (currentPage.value > newPagination.total_pages) {
        currentPage.value = newPagination.total_pages;
      }
      if (currentPage.value < 1) {
        currentPage.value = 1;
      }
    }
  },
  { deep: true }
);

// Watch for changes in hotels array to handle updates safely
watch(
  () => hotels.value,
  (newHotels) => {
    if (newHotels && Array.isArray(newHotels)) {
      // Ensure all hotels have required properties
      newHotels.forEach((hotel, index) => {
        if (!hotel || typeof hotel !== "object") {
          console.warn(`Invalid hotel data at index ${index}:`, hotel);
        }
      });
    }
  },
  { deep: true }
);

// SEO
useHead({
  title: computed(
    () => `Hotels in ${searchParams.value?.cityName || "City"} - 9jahotel`
  ),
  meta: [
    {
      name: "description",
      content: computed(
        () =>
          `Find and book hotels in ${
            searchParams.value?.cityName || "your selected city"
          } with great prices and reviews.`
      ),
    },
  ],
});
</script>
