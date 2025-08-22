<template>
  <div
    class="h-full overflow-y-auto backdrop-brightness-75 flex flex-col justify-around"
  >
    <!-- Location Permission Alert -->
    <!-- <div v-if="showLocationAlert" class="p-4">
      <UAlert
        icon="i-lucide-info"
        :color="locationAlert.color"
        variant="solid"
        :title="locationAlert.title"
        :description="locationAlert.description"
        :close="{
          icon: 'i-lucide-x',
          color: 'neutral',
          variant: 'link',
        }"
        @update:open="showLocationAlert = false"
      />
    </div> -->

    <!-- City Confirmation Alert Overlay -->
    <div
      v-if="showCityConfirmationAlert && potentialCity"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <div class="flex items-start gap-3">
          <Icon
            name="i-lucide-map-pin"
            class="text-primary size-6 mt-1 flex-shrink-0"
          />
          <div class="flex-1">
            <h3 class="font-semibold text-neutral-900 mb-2">
              Confirm Your Location
            </h3>
            <p class="text-neutral-600 mb-6 text-sm">
              We think you might be in {{ potentialCity.name }}. Is this
              correct?
            </p>
            <div class="flex gap-3">
              <UButton
                label="Yes"
                color="success"
                size="xs"
                class="max-w-max"
                @click="confirmCity"
              />
              <UButton
                label="Choose manually"
                color="neutral"
                variant="outline"
                size="xs"
                class="max-w-max"
                @click="rejectCity"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <BrandLogo v-if="!user" />

    <!-- Search Section - Reduced top padding from p-6 to p-3 pt-3 -->
    <div class="p-3 pt-3 spacey-8 -mt-8">
      <div
        class="bg-white/40 backdrop-blur-xs border border-white rounded-xl p-4 space-y-5"
      >
        <h2 class="font-semibold text-neutral-900 mb-8">
          Find Your Perfect Stay
        </h2>
        <!-- Location Search -->
        <div class="space-y-2">
          <USelectMenu
            v-model="selectedCity"
            v-model:search-term="searchLocation"
            size="xl"
            :items="cities"
            :loading="citiesLoading || geolocationLoading"
            :placeholder="
              selectedCity ? selectedCity.name : 'Where are you going?'
            "
            searchable
            clearable
            ignore-filter
            :search-input="{
              placeholder: selectedCity
                ? selectedCity.name
                : 'Abuja, Lagos, PHA',
              icon: 'i-lucide-map-pin',
            }"
            by="id"
            option-attribute="name"
            :icon="selectedCity ? 'i-lucide-map-pin' : 'i-lucide-map-pin'"
            class="w-full text-sm"
            @click="clearSearchText"
          >
            <template #item-leading>
              <Icon name="i-lucide-map-pin" class="text-neutral-400" />
            </template>
            <template #item-label="{ item }">
              {{ item.name }}
            </template>
            <template #label="{ selectedOption }">
              <span v-if="selectedOption">{{ selectedOption.name }}</span>
            </template>
          </USelectMenu>
        </div>

        <!-- Date Range Selection -->
        <div class="space-y-2 bg-white rounded-sm">
          <UPopover v-model:open="datePopoverOpen">
            <UButton
              size="xl"
              icon="i-lucide-calendar"
              variant="outline"
              color="neutral"
              class="w-full text-sm cursor-pointer justify-start bg-transparent"
              :class="
                !dateRange?.start || !dateRange?.end
                  ? 'text-neutral-500'
                  : 'text-neutral-400'
              "
            >
              <template v-if="dateRange.start">
                <template v-if="dateRange.end">
                  {{ df.format(dateRange.start.toDate(getLocalTimeZone())) }} -
                  {{ df.format(dateRange.end.toDate(getLocalTimeZone())) }}
                </template>
                <template v-else>
                  {{ df.format(dateRange.start.toDate(getLocalTimeZone())) }}
                </template>
              </template>
              <template v-else> Select dates </template>
            </UButton>

            <template #content>
              <UCalendar
                v-model="dateRange"
                class="p-2"
                :min-value="minDate"
                range
              />
            </template>
          </UPopover>
        </div>

        <!-- Guests Selection -->
        <div class="space-y-1 bg-white rounded-lg relative">
          <GuestSelector v-model="guestConfig" />
        </div>

        <!-- Search Button -->
        <UButton
          label="Search Hotels"
          color="primary"
          size="lg"
          class="w-full block text-center items-center font-medium"
          :loading="searchLoading"
          @click="searchHotels"
        />
      </div>
    </div>

    <!-- Rest of the content with some padding -->
    <div class="p-4 space-y-6">
      <!-- Nearby Hotels (shown when user location is available) -->
      <div v-if="nearbyHotels.length > 0" class="space-y-4">
        <HotelList
          :hotels="nearbyHotels"
          :loading="nearbyHotelsLoading"
          title="Nearby Hotels"
          :show-header="true"
          :enable-sort="false"
          :enable-view-toggle="true"
          default-view-mode="list"
          @favorite-toggle="handleFavoriteToggle"
          @hotel-click="handleHotelClick"
        />
      </div>

      <!-- No Hotels Message (only show after location request process is complete) -->
      <div
        v-else-if="
          !nearbyHotelsLoading && !userLocation && locationRequestCompleted
        "
        class="text-center py-8 bg-black/10 rounded-lg backdrop-blur-sm"
      >
        <div class="space-y-2 text-white">
          <Icon name="i-lucide-map-pin" class="mx-auto size-12 opacity-50" />
          <p class="text-lg font-medium">Enable Location Access</p>
          <p class="text-sm px-1.5">
            Allow location access to see nearby hotels and get personalized
            recommendations.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  DateFormatter,
  getLocalTimeZone,
  today,
  type CalendarDate,
} from "@internationalized/date";
import type { Hotel, City } from "~/types/hotel";

// Define RangeValue type locally since import is unavailable
type RangeValue<T> = { start: T | undefined; end: T | undefined };

// Get user data from your auth store/composable
const userStore = useUserStore();
const user = computed(() => userStore.user);

// Toast for notifications
const toast = useToast();

// State for the location alert
const showLocationAlert = ref(false);
const locationAlert = reactive({
  title: "",
  description: "",
  color: "primary" as "primary" | "warning",
});

// Add state to track if location request process is completed
const locationRequestCompleted = ref(false);

function triggerLocationAlert(
  title: string,
  description: string,
  color: "primary" | "warning" = "primary"
) {
  locationAlert.title = title;
  locationAlert.description = description;
  locationAlert.color = color;
  showLocationAlert.value = true;
}

// Type for Nominatim API response for better type safety
interface NominatimAddress {
  city?: string;
  town?: string;
  village?: string;
  state?: string;
  county?: string;
}

interface NominatimResponse {
  address: NominatimAddress;
}

// Type for our city API response
interface CityApiResponse {
  success: boolean;
  data: {
    cities: City[];
  };
}

/*
  LOCATION SYSTEM ARCHITECTURE:
  
  1. DEVICE COORDINATES (24-hour storage):
     - Saved to: 'device_user_location' localStorage key
     - Contains: { coordinates, timestamp, expiresAt }
     - Behavior: Only prompt device when no valid stored coordinates exist
     - Purpose: Avoid constantly asking user for location permission
  
  2. OPENSTREETMAP CITY DATA (runs every time):
     - NOT stored in localStorage
     - Always called fresh when coordinates are available
     - Purpose: Get current/accurate city name based on coordinates
     - Behavior: Calls API every page load for fresh city detection
  
  This separation ensures:
  - User privacy (device location not permanently stored)
  - Fresh city data (always current from OpenStreetMap)
  - Better UX (no repeated location prompts within 24 hours)
*/

// Location storage utilities - ONLY for device coordinates
const DEVICE_LOCATION_KEY = "device_user_location";
const LOCATION_EXPIRY_HOURS = 24;

interface DeviceLocationData {
  coordinates: { latitude: number; longitude: number };
  timestamp: number;
  expiresAt: number;
}

const saveDeviceLocation = (coords: {
  latitude: number;
  longitude: number;
}) => {
  if (import.meta.client) {
    const now = Date.now();
    const expiresAt = now + LOCATION_EXPIRY_HOURS * 60 * 60 * 1000; // 24 hours

    const locationData: DeviceLocationData = {
      coordinates: coords,
      timestamp: now,
      expiresAt,
    };

    localStorage.setItem(DEVICE_LOCATION_KEY, JSON.stringify(locationData));
  }
};

const getDeviceLocation = (): {
  latitude: number;
  longitude: number;
} | null => {
  if (import.meta.client) {
    try {
      const stored = localStorage.getItem(DEVICE_LOCATION_KEY);
      if (!stored) return null;

      const locationData: DeviceLocationData = JSON.parse(stored);
      const now = Date.now();

      // Check if expired
      if (now > locationData.expiresAt) {
        localStorage.removeItem(DEVICE_LOCATION_KEY);
        return null;
      }

      return locationData.coordinates;
    } catch (error) {
      console.error("Error reading device location:", error);
      localStorage.removeItem(DEVICE_LOCATION_KEY);
      return null;
    }
  }
  return null;
};

const clearDeviceLocation = () => {
  if (import.meta.client) {
    localStorage.removeItem(DEVICE_LOCATION_KEY);
  }
};

// OpenStreetMap city detection - runs every time, no storage
const getCityFromCoordinates = async (coords: {
  latitude: number;
  longitude: number;
}): Promise<string | null> => {
  try {
    const response = await $fetch<NominatimResponse>(
      "https://nominatim.openstreetmap.org/reverse",
      {
        params: {
          format: "jsonv2",
          lat: coords.latitude,
          lon: coords.longitude,
        },
      }
    );

    console.log("OpenStreetMap response:", response);

    const address = response.address;
    const cityName =
      address.city ||
      address.town ||
      address.village ||
      address.state ||
      address.county;

    return cityName || null;
  } catch (error) {
    console.error("OpenStreetMap API error:", error);
    return null;
  }
};

// Function to manually request fresh location (can be called from UI if needed)
const requestFreshLocation = async () => {
  clearDeviceLocation(); // Clear 24-hour device storage
  userCoordinates.value = null;
  selectedCity.value = undefined;
  searchLocation.value = "";
  locationRequestCompleted.value = false;

  // Trigger the location request process
  geolocationLoading.value = true;
  isGeolocating.value = true;

  try {
    // Get fresh device coordinates
    const coords = await requestLocation();
    userCoordinates.value = coords;

    // Always call OpenStreetMap for fresh city data (not stored)
    const cityName = await getCityFromCoordinates(coords);

    if (cityName) {
      searchLocation.value = cityName;
    }
  } catch (error) {
    console.error("Fresh location request failed:", error);
  } finally {
    geolocationLoading.value = false;
  }
};

// Function to find closest matching city
const findClosestCity = (searchTerm: string, cityList: City[]): City | null => {
  if (!cityList || cityList.length === 0) return null;

  const normalizedSearch = searchTerm.toLowerCase().trim();

  // First try exact match
  let match = cityList.find(
    (city) => city.name.toLowerCase() === normalizedSearch
  );

  if (match) return match;

  // Then try starts with
  match = cityList.find((city) =>
    city.name.toLowerCase().startsWith(normalizedSearch)
  );

  if (match) return match;

  // Then try contains
  match = cityList.find((city) =>
    city.name.toLowerCase().includes(normalizedSearch)
  );

  if (match) return match;

  // Finally try fuzzy match (remove common words)
  const cleanSearch = normalizedSearch
    .replace(/\b(state|city|town|area)\b/g, "")
    .trim();
  if (cleanSearch !== normalizedSearch) {
    match = cityList.find(
      (city) =>
        city.name.toLowerCase().includes(cleanSearch) ||
        cleanSearch.includes(city.name.toLowerCase())
    );
  }

  return match || null;
};

// Conditionally set layout based on authentication status
definePageMeta({
  layout: false, // Disable automatic layout
});

// Set layout dynamically based on user authentication
setPageLayout(computed(() => (user.value ? "auth" : "default")));

// Get today's date
const todayDate = today(getLocalTimeZone());
const tomorrowDate = todayDate.add({ days: 1 });

// Date formatter for display
const df = new DateFormatter("en-US", {
  dateStyle: "medium",
});

// Date range for calendar (using proper CalendarDate format)
const dateRange = shallowRef<RangeValue<CalendarDate>>({
  start: todayDate,
  end: tomorrowDate,
});

// State to control the date popover
const datePopoverOpen = ref(false);

// Watch for changes in date range to auto-close popover
watch(
  dateRange,
  (newValue) => {
    // Auto-close popover when both start and end dates are selected
    if (newValue?.start && newValue?.end) {
      // Use setTimeout to allow user to see the selection briefly
      setTimeout(() => {
        datePopoverOpen.value = false;
      }, 300); // Brief delay for better UX
    }
  },
  { deep: true }
);

// Minimum date (today)
const minDate = todayDate;

// Guest configuration
interface GuestConfig {
  adults: number;
  rooms: number;
}
const guestConfig = reactive<GuestConfig>({
  adults: 1,
  rooms: 1,
});

// Hotel interaction handlers
const handleFavoriteToggle = (data: { hotel: Hotel; isFavorite: boolean }) => {
  console.log("Favorite toggled:", data);
  toast.add({
    title: data.isFavorite ? "Added to favorites" : "Removed from favorites",
    description: `${data.hotel.name} ${
      data.isFavorite ? "added to" : "removed from"
    } your favorites.`,
    color: data.isFavorite ? "success" : "neutral",
  });
};

const handleHotelClick = (hotel: Hotel) => {
  console.log("Hotel clicked:", hotel);
  // Navigation is handled by the HotelCard component
};

// Search loading state
const searchLoading = ref(false);

// API Base URL
const config = useRuntimeConfig();
const baseURL = config.public.apiBase || "/api";

// --- CITY SEARCH LOGIC ---

// State for the search menu
const searchLocation = ref("");
const selectedCity = ref<City | undefined>(undefined);

// Function to clear search text
const clearSearchText = () => {
  searchLocation.value = "";
};

// The `useLazyFetch` composable will automatically handle debouncing and re-fetching
const { data: cities, status: citiesStatus } = useLazyFetch(
  `${baseURL}/cities`,
  {
    query: computed(() => ({
      ...(searchLocation.value && { search: searchLocation.value }),
    })),
    default: () => [] as City[],
    // The response from the API is CityApiResponse, but the transform converts it to City[]
    transform: (response: CityApiResponse): City[] => {
      if (response?.success && Array.isArray(response?.data?.cities)) {
        return response.data.cities;
      }
      return [];
    },
    immediate: true,
  }
);

// Manual watcher for the search term
watch(searchLocation, (newValue) => {
  if (newValue !== selectedCity.value?.name) {
    // useLazyFetch will automatically re-fetch when query changes
  }
});

// Watch for city selection to clear search text
watch(selectedCity, (newCity) => {
  if (newCity) {
    // Clear search text after city is selected
    searchLocation.value = "";
  }
});

const citiesLoading = computed(() => citiesStatus.value === "pending");

// --- END CITY SEARCH LOGIC ---

// Get location and set default city
const { requestLocation } = useAdvancedGeolocation();
const geolocationLoading = ref(false);
const isGeolocating = ref(false); // Flag to indicate geolocation search is active
const potentialCity = ref<City | null>(null);
const userCoordinates = ref<{ latitude: number; longitude: number } | null>(
  null
);

// User location computed - uses device location (24-hour storage) or current session
const userLocation = computed(() => {
  // First check if we have fresh coordinates from current session
  if (userCoordinates.value) {
    return userCoordinates.value;
  }
  // Then check stored device location (24-hour expiry)
  return getDeviceLocation();
});

// Nearby hotels state
const nearbyHotels = ref<Hotel[]>([]);
const nearbyHotelsLoading = ref(false);

// Fetch nearby hotels when user location is available
const fetchNearbyHotels = async () => {
  if (!userLocation.value) {
    nearbyHotels.value = [];
    return;
  }

  nearbyHotelsLoading.value = true;

  try {
    const params = {
      lat: userLocation.value.latitude,
      long: userLocation.value.longitude,
      checkIn: dateRange.value.start?.toString() || todayDate.toString(),
      checkOut: dateRange.value.end?.toString() || tomorrowDate.toString(),
    };

    const response = await $fetch<{
      success: boolean;
      data: { hotels: Hotel[] };
    }>(`${baseURL}/hotels/nearby`, {
      params,
    });

    if (response.success && response.data?.hotels) {
      // Limit to 12 hotels as requested
      nearbyHotels.value = response.data.hotels.slice(0, 12);
    } else {
      nearbyHotels.value = [];
    }
  } catch (error) {
    console.error("Error fetching nearby hotels:", error);
    nearbyHotels.value = [];
  } finally {
    nearbyHotelsLoading.value = false;
  }
};

// Watch for changes in user location or date range to fetch nearby hotels
watch(
  [userLocation, dateRange],
  () => {
    if (userLocation.value) {
      fetchNearbyHotels();
    }
  },
  { immediate: true, deep: true }
);

// State for city confirmation alert
const showCityConfirmationAlert = ref(false);

// This watcher will auto-select the closest city when found via geolocation
watch(cities, (newCities) => {
  if (isGeolocating.value && newCities && newCities.length > 0) {
    // Get the search term that triggered this
    const currentSearchTerm = searchLocation.value;

    // Find the closest matching city
    const closestMatch = findClosestCity(currentSearchTerm, newCities);

    if (closestMatch) {
      // Auto-select the closest match
      selectedCity.value = closestMatch;
      searchLocation.value = ""; // Clear search text

      // Save DEVICE coordinates (not OpenStreetMap data) for 24 hours
      if (userCoordinates.value) {
        saveDeviceLocation(userCoordinates.value);
      }

      // Show success message
      toast.add({
        title: "Location Detected",
        description: `Automatically selected ${closestMatch.name} based on your location.`,
        color: "success",
      });
    } else {
      // Show confirmation dialog if no good match found
      potentialCity.value = newCities[0] || null;
      showCityConfirmationAlert.value = true;
    }

    // Reset the flag to prevent this from triggering on manual searches
    isGeolocating.value = false;
    locationRequestCompleted.value = true;
  } else if (isGeolocating.value && newCities && newCities.length === 0) {
    // No cities found during geolocation search
    isGeolocating.value = false;
    locationRequestCompleted.value = true;
  }
});

// Handle city confirmation
const confirmCity = async () => {
  if (potentialCity.value) {
    selectedCity.value = potentialCity.value;
    searchLocation.value = ""; // Clear search text immediately

    // Save DEVICE coordinates for 24 hours
    if (userCoordinates.value) {
      saveDeviceLocation(userCoordinates.value);
    }
  }
  showCityConfirmationAlert.value = false;
  // Mark location request as completed
  locationRequestCompleted.value = true;
};

const rejectCity = () => {
  showCityConfirmationAlert.value = false;
  // Clear temporary coordinates since user rejected the location
  userCoordinates.value = null;
  // Don't save to device location storage
  // Mark location request as completed
  locationRequestCompleted.value = true;
};

onMounted(() => {
  // AUTOMATIC LOCATION PERMISSION LOGIC

  // Step 1: Check if we already have stored device location (24-hour expiry)
  const existingDeviceLocation = getDeviceLocation();

  if (existingDeviceLocation) {
    // ✅ We have valid device location, use it (NO permission prompt)
    userCoordinates.value = existingDeviceLocation;
    locationRequestCompleted.value = true;

    // Always call OpenStreetMap to get current city name (this runs every time)
    getCityFromCoordinates(existingDeviceLocation).then((cityName) => {
      if (cityName) {
        searchLocation.value = cityName;

        // Wait for cities to load, then try to find and select the city
        watch(
          cities,
          (cityList) => {
            if (cityList && cityList.length > 0) {
              const matchedCity = findClosestCity(cityName, cityList);
              if (matchedCity) {
                selectedCity.value = matchedCity;
                searchLocation.value = ""; // Clear search text after selection

                toast.add({
                  title: "Location Loaded",
                  description: `Using your saved location: ${matchedCity.name}`,
                  color: "success",
                });
              }
            }
          },
          { immediate: true }
        );
      }
    });

    return; // Exit early, no need to request device location
  }

  // Step 2: 🚨 AUTOMATIC PERMISSION REQUEST (when no stored location exists)
  setTimeout(async () => {
    console.log("🚨 AUTOMATICALLY REQUESTING LOCATION PERMISSION...");
    geolocationLoading.value = true;
    isGeolocating.value = true;

    try {
      // 🔔 THIS IS WHERE THE BROWSER PROMPTS FOR LOCATION PERMISSION
      const coords = await requestLocation();
      console.log("✅ Permission granted! Fresh device coordinates:", coords);

      // Store coordinates temporarily in session
      userCoordinates.value = coords;

      // Call OpenStreetMap to get city name (this runs every time, not stored)
      const cityName = await getCityFromCoordinates(coords);

      if (cityName) {
        console.log("🏙️ City from OpenStreetMap:", cityName);
        // Trigger the city search via useLazyFetch
        searchLocation.value = cityName;
      } else {
        isGeolocating.value = false;
        locationRequestCompleted.value = true;
        triggerLocationAlert(
          "Location Error",
          "Could not determine a city from your location.",
          "warning"
        );
      }
    } catch (error: unknown) {
      console.log("❌ Permission denied or location error");
      isGeolocating.value = false;
      locationRequestCompleted.value = true;
      let message =
        "Could not determine your location. Please select a city manually.";
      if (error instanceof GeolocationPositionError && error.code === 1) {
        message =
          "Location access denied. You can still search for a city manually.";
      }
      triggerLocationAlert("Location Notice", message, "primary");
    } finally {
      geolocationLoading.value = false;
      if (!isGeolocating.value) {
        locationRequestCompleted.value = true;
      }
    }
  }, 3000); // 🕒 3-second delay before requesting permission
});

// Search function
const searchHotels = async () => {
  if (!selectedCity.value) {
    toast.add({
      title: "Location Required",
      description: "Please select a city to search for hotels.",
      color: "error",
    });
    return;
  }

  if (!dateRange.value?.start || !dateRange.value?.end) {
    toast.add({
      title: "Dates Required",
      description: "Please select check-in and check-out dates.",
      color: "error",
    });
    return;
  }

  searchLoading.value = true;

  try {
    // Prepare search parameters
    const searchParams = {
      checkIn: dateRange.value.start.toString(),
      checkOut: dateRange.value.end.toString(),
      cityId: selectedCity.value.id,
      cityName: selectedCity.value.name,
      adults: guestConfig.adults,
      rooms: guestConfig.rooms,
      search: "",
    };

    // Save search parameters to localStorage
    if (import.meta.client) {
      localStorage.setItem("hotelSearchParams", JSON.stringify(searchParams));
    }

    // Navigate to search results page
    await navigateTo("/hotels/search");
  } catch (error) {
    console.error("Search error:", error);
    toast.add({
      title: "Search Failed",
      description: "Unable to start hotel search. Please try again.",
      color: "error",
    });
  } finally {
    searchLoading.value = false;
  }
};

// SEO
useHead({
  title: "9jahotel",
  meta: [
    {
      name: "description",
      content:
        "Find your perfect stay with StayGlass - Book amazing hotels worldwide.",
    },
  ],
});
</script>
