<template>
  <div class="min-h-screen bg-white/60 backdrop-blur-sm relative pb-24">
    <!-- Status Tabs -->
    <div
      class="flex z-50 justify-center w-full py-3 gap-4 mb-6 sticky top-0 bg-white/80 backdrop-blur border-b border-gray-200"
    >
      <UButton
        v-for="tab in statusTabs"
        :key="tab.value"
        :color="tab.value === status ? 'primary' : 'gray'"
        variant="solid"
        size="md"
        class="px-4 font-semibold"
        @click="changeStatus(tab.value)"
      >
        {{ tab.label }}
      </UButton>
    </div>

    <!-- Error Message -->
    <div
      v-if="error"
      class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 mx-4"
    >
      <div class="flex items-center">
        <UIcon name="i-lucide-alert-circle" class="w-5 h-5 text-red-500 mr-2" />
        <p class="text-red-700">{{ error }}</p>
      </div>
      <UButton
        color="red"
        variant="ghost"
        size="sm"
        class="mt-2"
        @click="retryFetch"
      >
        Try Again
      </UButton>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <LoadingOverlay />
    </div>

    <!-- Content Area -->
    <div v-else class="px-4">
      <!-- Empty State -->
      <div
        v-if="bookings.length === 0 && !error"
        class="text-center text-gray-500 py-12"
      >
        <UIcon
          name="i-lucide-calendar-x"
          class="w-16 h-16 text-gray-400 mx-auto mb-4"
        />
        <h3 class="text-lg font-medium text-gray-900 mb-2">
          No bookings found
        </h3>
        <p class="text-gray-500">
          No {{ status }} bookings to display at the moment.
        </p>
      </div>

      <!-- Bookings Grid -->
      <div v-else class="grid gap-6 max-w-6xl mx-auto">
        <!-- Upcoming Bookings -->
        <template v-if="status === 'upcoming'">
          <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <UpcomingBooking
              v-for="booking in bookings"
              :key="booking.id"
              :booking="booking"
              @view-details="handleViewDetails"
            />
          </div>
        </template>

        <!-- Completed Bookings -->
        <template v-else-if="status === 'completed'">
          <div class="grid gap-6 md:grid-cols-1 lg:grid-cols-2">
            <CompletedBooking
              v-for="booking in bookings"
              :key="booking.id"
              :booking="booking"
              :user-rating="booking.userRating || 4.5"
              @book-again="handleBookAgain"
              @view-details="handleViewDetails"
            />
          </div>
        </template>

        <!-- Cancelled Bookings -->
        <template v-else-if="status === 'cancelled'">
          <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <CancelledBooking
              v-for="booking in bookings"
              :key="booking.id"
              :booking="booking"
              :refund-days="3"
              @book-again="handleBookAgain"
              @find-similar="handleFindSimilar"
            />
          </div>
        </template>

        <!-- Fallback: Original List View -->
        <template v-else>
          <div class="grid gap-6">
            <div
              v-for="booking in bookings"
              :key="booking.id"
              class="bg-white rounded-lg shadow p-4 flex flex-col md:flex-row gap-4 items-center"
            >
              <NuxtImg
                :src="booking.hotel.backgroundImage || defaultHotelImg"
                :alt="booking.hotel.name"
                class="w-24 h-24 object-cover rounded-lg border"
              />
              <div class="flex-1">
                <div class="flex justify-between items-center mb-2">
                  <h2 class="font-bold text-lg text-primary-800">
                    {{ booking.hotel.name }}
                  </h2>
                  <span
                    class="px-2 py-1 rounded-full text-xs font-semibold"
                    :class="statusColor(booking.status)"
                  >
                    {{ booking.status.toUpperCase() }}
                  </span>
                </div>
                <div class="text-xs text-gray-600 mb-1">
                  <UIcon name="i-lucide-map-pin" class="w-4 h-4 inline mr-1" />
                  {{ booking.hotel.address }}
                </div>
                <div class="text-xs text-gray-600 mb-1">
                  <UIcon name="i-lucide-calendar" class="w-4 h-4 inline mr-1" />
                  {{ formatDate(booking.checkedIn) }} -
                  {{ formatDate(booking.checkedOut) }}
                </div>
                <div class="text-xs text-gray-600 mb-1">
                  <UIcon name="i-lucide-hash" class="w-4 h-4 inline mr-1" />
                  Ref: {{ booking.reference }}
                </div>
                <div class="text-xs text-gray-600 mb-1">
                  <UIcon name="i-lucide-users" class="w-4 h-4 inline mr-1" />
                  {{ booking.customer.firstname }}
                  {{ booking.customer.lastname }}
                </div>
                <div class="text-xs text-gray-600 mb-1">
                  <UIcon name="i-lucide-phone" class="w-4 h-4 inline mr-1" />
                  {{ booking.customer.phone }}
                </div>
                <div class="text-xs text-gray-600 mb-1">
                  <UIcon
                    name="i-lucide-credit-card"
                    class="w-4 h-4 inline mr-1"
                  />
                  ₦{{ booking.amount.toLocaleString() }}
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Pagination Controls -->
      <div
        v-if="meta.total_pages > 1"
        class="flex justify-center items-center gap-4 mt-8 pb-4"
      >
        <UButton
          icon="i-lucide-chevron-left"
          :disabled="meta.current_page === 1 || loading"
          size="sm"
          @click="changePage(meta.current_page - 1)"
        />
        <span class="text-sm font-medium"
          >Page {{ meta.current_page }} of {{ meta.total_pages }}</span
        >
        <UButton
          icon="i-lucide-chevron-right"
          :disabled="meta.current_page === meta.total_pages || loading"
          size="sm"
          @click="changePage(meta.current_page + 1)"
        />
      </div>

      <!-- Load More Button -->
      <div v-if="meta.has_more_pages" class="flex justify-center mt-4 pb-8">
        <UButton
          color="primary"
          variant="outline"
          :loading="loadingMore"
          :disabled="loading"
          @click="loadMore"
        >
          Load More
        </UButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Import the booking components
import UpcomingBooking from "~/components/UpcomingBooking.vue";
import CompletedBooking from "~/components/CompletedBooking.vue";
import CancelledBooking from "~/components/CancelledBooking.vue";

definePageMeta({
  layout: "auth",
});

const defaultHotelImg =
  "https://res.cloudinary.com/dcm83lp3b/image/upload/v1752510054/9jahotel_sm8jgq.svg";

const statusTabs = [
  { label: "Upcoming", value: "upcoming" },
  { label: "Completed", value: "completed" },
  { label: "Cancelled", value: "cancelled" },
];

type Booking = {
  id: number;
  reference: string;
  amount: number;
  commissionAmount: number;
  hotelPayable: number;
  checkedIn: string;
  checkedOut: string;
  type: string;
  createdAt: string;
  updatedAt: string;
  status: string;
  userRating?: number; // Optional user rating for completed bookings
  customer: {
    id: number;
    firstname: string;
    lastname: string;
    email: string;
    phone: string;
    avatar: string | null;
  };
  hotel: {
    id: number;
    name: string;
    code: string;
    email: string;
    address: string;
    phone: string;
    backgroundImage: string | null;
  };
  bookings: Array<{ id: number; code: string }>;
};

type Pagination = {
  total: number;
  count: number;
  per_page: number;
  current_page: number;
  total_pages: number;
  has_more_pages: boolean;
  first_page?: boolean;
  last_page?: boolean;
  next_page?: number | null;
  prev_page?: number | null;
};

type ApiResponse = {
  success: boolean;
  message: string;
  data: {
    bookings: Booking[];
    meta: {
      pagination: Pagination;
    };
  };
};

const status = ref("upcoming");
const bookings = ref<Booking[]>([]);
const meta = ref<Pagination>({
  total: 0,
  count: 0,
  per_page: 10,
  current_page: 1,
  total_pages: 1,
  has_more_pages: false,
});
const loading = ref(false);
const loadingMore = ref(false);
const error = ref<string | null>(null);

function statusColor(status: string) {
  switch (status) {
    case "paid":
    case "completed":
      return "bg-green-100 text-green-700";
    case "cancelled":
      return "bg-red-100 text-red-700";
    case "unpaid":
    case "upcoming":
      return "bg-yellow-100 text-yellow-700";
    default:
      return "bg-gray-100 text-gray-700";
  }
}

function formatDate(dateStr: string) {
  if (!dateStr) return "";
  const date = new Date(dateStr);
  return date.toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
}

async function fetchBookings(reset = true) {
  const isLoadMore = !reset;

  if (isLoadMore) {
    loadingMore.value = true;
  } else {
    loading.value = true;
  }

  error.value = null;

  const page = reset ? 1 : meta.value.current_page + 1;
  const config = useRuntimeConfig();

  // Try multiple possible token keys
  let token =
    localStorage.getItem("token") ||
    localStorage.getItem("authToken") ||
    localStorage.getItem("accessToken") ||
    localStorage.getItem("access_token");

  const apiBaseUrl = config.public.apiBase || "/api";

  // Debug logging
  console.log("🔍 Debug Info:", {
    tokenExists: !!token,
    apiBaseUrl,
    status: status.value,
    page,
  });

  try {
    if (!token) {
      throw new Error("No authentication token found. Please login first.");
    }

    // Clean token (remove Bearer prefix if already there)
    token = token.replace(/^Bearer\s+/i, "").trim();

    const res = await $fetch<ApiResponse>("/bookings", {
      baseURL: apiBaseUrl,
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      params: {
        status: status.value,
        page,
        perPage: 10,
      },
      timeout: 15000,
    });

    // Validate response structure
    if (!res || !res.success || !res.data) {
      throw new Error(res?.message || "Invalid response format");
    }

    const newBookings = res.data.bookings || [];
    const newMeta = res.data.meta?.pagination;

    if (!newMeta) {
      throw new Error("Invalid pagination data");
    }

    // Update bookings and pagination
    bookings.value = reset ? newBookings : [...bookings.value, ...newBookings];
    meta.value = newMeta;
  } catch (err: any) {
    console.error("❌ API Error:", {
      message: err.message,
      status: err.response?.status,
      data: err.response?._data,
      url: `${apiBaseUrl}/bookings`,
    });

    // Handle different error types
    let errorMessage = "Failed to load bookings";

    if (err.response) {
      const status = err.response.status;
      const data = err.response._data;

      switch (status) {
        case 401:
          errorMessage = "Authentication failed. Please login again.";
          // Clear all storage and redirect to home
          try {
            // Import utility dynamically to avoid SSR issues
            handleAuthFailure();
          } catch (e) {
            // Fallback: clear storage and redirect manually
            if (typeof window !== "undefined") {
              localStorage.clear();
              sessionStorage.clear();
              window.location.href = "/";
            }
          }
          break;
        case 403:
          errorMessage = "Access denied";
          break;
        case 404:
          errorMessage = "Bookings not found";
          break;
        case 500:
          errorMessage = "Server error. Please try again later.";
          break;
        default:
          errorMessage =
            data?.message || `Error ${status}: ${err.response.statusText}`;
      }
    } else if (err.message?.includes("timeout")) {
      errorMessage = "Request timed out. Please check your connection.";
    } else if (
      err.message?.includes("network") ||
      err.message?.includes("fetch")
    ) {
      errorMessage = "Network error. Please check your connection.";
    } else {
      errorMessage = err.message || errorMessage;
    }

    error.value = errorMessage;

    // Reset data only on initial fetch error, not on load more
    if (reset) {
      bookings.value = [];
      meta.value = {
        total: 0,
        count: 0,
        per_page: 10,
        current_page: 1,
        total_pages: 1,
        has_more_pages: false,
      };
    }
  } finally {
    loading.value = false;
    loadingMore.value = false;
  }
}

function changeStatus(newStatus: string) {
  if (status.value !== newStatus && !loading.value) {
    status.value = newStatus;
    fetchBookings(true);
  }
}

function changePage(page: number) {
  if (
    page !== meta.value.current_page &&
    page >= 1 &&
    page <= meta.value.total_pages &&
    !loading.value
  ) {
    meta.value.current_page = page;
    fetchBookings(true);
  }
}

function loadMore() {
  if (meta.value.has_more_pages && !loading.value && !loadingMore.value) {
    fetchBookings(false);
  }
}

function retryFetch() {
  error.value = null;
  fetchBookings(true);
}

// Add auth check function
function checkAuthStatus() {
  const token =
    localStorage.getItem("token") ||
    localStorage.getItem("authToken") ||
    localStorage.getItem("accessToken");

  if (!token) {
    error.value = "Please login to view your bookings.";
    return false;
  }

  // Check if token is JWT and if it's expired
  try {
    if (token.includes(".")) {
      const payload = JSON.parse(atob(token.split(".")[1]));
      const now = Math.floor(Date.now() / 1000);

      if (payload.exp && payload.exp < now) {
        error.value = "Your session has expired. Please login again.";
        localStorage.removeItem("token");
        return false;
      }
    }
  } catch (e) {
    console.log("Token validation error:", e);
  }

  return true;
}

// Event handlers for component actions
function handleViewDetails(booking: Booking) {
  console.log("View details for booking:", booking.id);
  // Navigate to booking details page
  // navigateTo(`/bookings/${booking.id}`);
}

function handleBookAgain(booking: Booking) {
  console.log("Book again for hotel:", booking.hotel.id);
  // Navigate to hotel booking page with pre-filled data
  // navigateTo(`/hotels/${booking.hotel.id}/book`);
}

function handleFindSimilar(booking: Booking) {
  console.log("Find similar hotels to:", booking.hotel.name);
  // Navigate to search results with similar criteria
  // navigateTo(`/search?location=${booking.hotel.address}&checkin=${booking.checkedIn}&checkout=${booking.checkedOut}`);
}

onMounted(() => {
  // Check auth before fetching
  if (checkAuthStatus()) {
    fetchBookings(true);
  }
});
</script>

<style scoped>
.bg-primary-800 {
  background-color: #134e4a;
}
.text-primary-800 {
  color: #134e4a;
}
</style>
