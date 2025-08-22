<template>
  <div
    class="min-h-screen bg-black/30 backdrop-blur-sm relative overflow-hidden"
  >
    <!-- Background decorative elements -->
    <div class="absolute inset-0 overflow-hidden">
      <div
        class="absolute top-10 left-10 w-4 h-4 bg-yellow-400 rounded-full opacity-70"
      />
      <div
        class="absolute top-20 right-20 w-2 h-2 bg-white rounded-full opacity-50"
      />
      <div
        class="absolute top-32 left-1/4 w-3 h-3 bg-yellow-300 rounded-full opacity-60"
      />
      <div
        class="absolute bottom-40 right-10 w-6 h-6 bg-yellow-400 rounded-full opacity-40"
      />
      <div
        class="absolute bottom-20 left-16 w-2 h-2 bg-white rounded-full opacity-70"
      />
      <div
        class="absolute top-1/3 right-1/3 w-1 h-1 bg-yellow-200 rounded-full opacity-80"
      />
    </div>

    <div
      class="relative z-10 flex flex-col items-center justify-center min-h-screen p-6 pb-24"
    >
      <!-- Success Icon and Message -->
      <div class="text-center mb-8">
        <div
          class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4"
        >
          <UIcon name="i-lucide-check" class="w-10 h-10 text-primary" />
        </div>
        <h1 class="text-white text-2xl font-bold mb-2">Booking Confirmed!</h1>
        <p class="text-primary-100 text-sm">
          Reference Code: {{ bookingData?.reference || "Loading..." }}
        </p>
        <p class="text-primary-100 text-xs mt-1">
          <span v-if="bookingData?.status === 'paid'">
            Your reservation is guaranteed and secured
          </span>
          <span v-else> Your reservation is subject to cancellation </span>
        </p>
        <p class="text-primary-100 text-xs">
          Booked on {{ formatBookingDate(bookingData?.createdAt) }}
        </p>
      </div>

      <!-- Hotel Information -->
      <div
        class="w-full flex bg-white rounded-lg shadow-lg overflow-hidden mb-6 max-h-36"
      >
        <div class="relative w-1/3 max-h-36">
          <NuxtImg
            :src="
              bookingData?.hotel?.backgroundImage ||
              'https://res.cloudinary.com/dcm83lp3b/image/upload/v1752510054/9jahotel_sm8jgq.svg'
            "
            :alt="bookingData?.hotel?.name || 'Hotel'"
            class="w-full h-full object-cover"
          />
        </div>
        <div class="p-4 flex-1">
          <h2 class="text-gray-800 font-semibold mb-1 leading-4">
            {{ bookingData?.hotel?.name || "Hotel Name" }}
          </h2>
          <p class="text-gray-600 text-xs mb-2">
            <UIcon name="i-lucide-map-pin" class="w-4 h-4 inline mr-1" />
            <span>{{
              bookingData?.hotel?.address || "Address not available"
            }}</span>
          </p>
          <p class="text-gray-600 font-medium text-sm">
            <UIcon name="i-lucide-phone" class="w-4 h-4 inline mr-1" />
            <span>{{
              bookingData?.hotel?.phone || "Phone not available"
            }}</span>
          </p>
        </div>
      </div>

      <!-- Reservation Details -->
      <div class="w-full bg-white rounded-lg shadow-lg p-4 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-gray-800 font-semibold">Reservation Details</h3>
          <span
            class="bg-green-100 text-green-600 text-xs px-2 py-1 rounded-full font-medium"
          >
            {{ bookingData?.status?.toUpperCase() || "CONFIRMED" }}
          </span>
        </div>

        <div class="space-y-3">
          <!-- Check-in -->
          <div class="flex items-center text-sm">
            <UIcon name="i-lucide-calendar" class="w-4 h-4 text-primary mr-2" />
            <div>
              <p class="text-gray-600">Check-in</p>
              <p class="text-gray-800 font-medium">
                {{ formatReservationDate(bookingData?.checkedIn) }}
              </p>
              <p class="text-gray-500 text-xs">12:00 PM</p>
            </div>
          </div>

          <!-- Check-out -->
          <div class="flex items-center text-sm">
            <UIcon name="i-lucide-calendar" class="w-4 h-4 text-primary mr-2" />
            <div>
              <p class="text-gray-600">Check-out</p>
              <p class="text-gray-800 font-medium">
                {{ formatReservationDate(bookingData?.checkedOut) }}
              </p>
              <p class="text-gray-500 text-xs">11:00 AM</p>
            </div>
          </div>

          <!-- Guest and Room Info -->
          <div class="flex items-center text-sm">
            <UIcon name="i-lucide-users" class="w-4 h-4 text-primary mr-2" />
            <div>
              <p class="text-gray-600">{{ numberOfGuests }} Adults</p>
              <p class="text-gray-800 font-medium">{{ roomCategory }}</p>
              <p class="text-gray-500 text-xs">
                Room {{ roomNumbers.join(", ") }} • {{ numberOfNights }} nights
                <!-- {{ numberOfDays }} days -->
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Summary - Only show for paid bookings -->
      <div
        v-if="bookingData?.status === 'paid'"
        class="w-full bg-primary-700 rounded-lg shadow-lg p-4 mb-6"
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-white font-semibold">Payment</h3>
          <span
            class="bg-green-500 text-white text-xs px-2 py-1 rounded-full font-medium"
          >
            {{ getPaymentStatus(bookingData?.status) }}
          </span>
        </div>

        <div class="space-y-2">
          <!-- Total Amount -->
          <div class="text-white">
            <p class="text-2xl font-bold">
              ₦{{ bookingData?.amount?.toLocaleString() || "0" }}
            </p>
          </div>

          <!-- Payment Details -->
          <div class="space-y-1 list-outside text-sm">
            <div class="flex justify-between text-primary-100">
              <span>Room charges</span>
              <span>₦{{ (bookingData?.amount || 0).toLocaleString() }}</span>
            </div>
            <div class="flex justify-between text-primary-100">
              <span>Taxes included</span>
              <span>₦0.00</span>
            </div>
            <div class="flex justify-between text-primary-100">
              <span>Payment method</span>
              <span>{{ getPaymentMethodLabel(bookingData?.type) }}</span>
            </div>
          </div>

          <!-- Download Receipt -->
          <!-- <div class="pt-3 border-t border-primary">
            <button
              class="flex items-center text-sm hover:text-white"
              @click="downloadReceipt"
            >
              <UIcon name="i-lucide-download" class="w-4 h-4 mr-2" />
              Download Receipt
            </button>
          </div> -->
        </div>
      </div>

      <!-- Important Information -->
      <div class="w-full bg-black/50 rounded-lg shadow-lg p-4 mb-6 text-white">
        <div class="flex items-center mb-3">
          <UIcon name="i-lucide-info" class="w-5 h-5 text-primary-400 mr-2" />
          <h3 class="text-white font-semibold">Important Information</h3>
        </div>

        <!-- Check-in Instructions -->
        <div class="mb-4">
          <h4 class="font-medium text-sm mb-2">Check-in Instructions</h4>
          <ul class="space-y-1 list-outside text-primary-100 text-xs">
            <li>• Check-in time: 12:00 PM onwards</li>
            <li>• Required documents: Valid ID and confirmation number</li>
            <li>• Early check-in: Subject to availability</li>
          </ul>
        </div>

        <!-- Hotel Policies -->
        <div>
          <h4 class="font-medium text-sm mb-2">Hotel Policies</h4>
          <ul class="space-y-1 list-outside text-primary-100 text-xs">
            <li>• Cancellation: Free until 48 hours before check-in</li>
            <li>• Modification: Changes allowed up to 48 hours</li>
            <!-- <li>• No-show policy: Room held until 6:00 AM next day</li> -->
          </ul>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="w-full space-y-3">
        <UButton
          color="white"
          variant="solid"
          size="lg"
          class="w-full bg-white text-primary hover:bg-gray-50 block"
          @click="navigateToHome"
        >
          Continue
        </UButton>
      </div>
    </div>
  </div>
</template>

<script setup>
// Install with: npm install canvas-confetti
import confetti from "canvas-confetti";
const userStore = useUserStore();
const user = computed(() => userStore.user);

definePageMeta({ layout: false });
setPageLayout(computed(() => (user.value ? "auth" : "default")));

const router = useRouter();

const bookingStore = useBookingStore();
const bookingData = ref(null);
const roomDetails = ref([]);

onMounted(() => {
  // Fire confetti celebration after 3 seconds (mobile-friendly)
  setTimeout(() => {
    const duration = 1000 * 15; // ms
    const end = Date.now() + duration;
    const interval = setInterval(() => {
      confetti({
        particleCount: 18,
        spread: 70,
        origin: { y: 0.6 },
        scalar: 1.1,
        zIndex: 9999,
        colors: [
          "#fbbf24", // yellow
          "#f59e42", // orange
          "#fff", // white
          "#10b981", // green
          "#3b82f6", // blue
          "#ef4444", // red
          "#a21caf", // purple
          "#ff0000", // bright red
          "#eab308", // gold
          "#6366f1", // indigo
          "#f472b6", // pink
          "#ff2222",
          "#22d3ee", // cyan
          "#fde68a", // light yellow
          "#bbf7d0", // mint
          "#ef0044",
          "#f87171", // light red
          "#c026d3", // magenta
        ],
      });
      if (Date.now() > end) clearInterval(interval);
    }, 200);
  }, 1000);
  // Remove all data except user and token
  const keepKeys = ["user", "token"];
  Object.keys(localStorage).forEach((key) => {
    if (!keepKeys.includes(key)) {
      localStorage.removeItem(key);
    }
  });

  // Load booking data from bookingResponse
  let booking = null;
  try {
    const storedBooking = localStorage.getItem("bookingResponse");
    if (storedBooking) {
      const parsed = JSON.parse(storedBooking);
      booking = parsed.data ? parsed.data : parsed;
    }
  } catch (error) {
    console.error("Error loading booking data from localStorage:", error);
  }
  bookingData.value = booking;

  // Load room details from localStorage
  let rooms = [];
  try {
    const storedRooms = localStorage.getItem("selectedRooms");
    rooms = storedRooms ? JSON.parse(storedRooms) : [];
    if (!Array.isArray(rooms)) rooms = [];
  } catch (error) {
    rooms = [];
  }
  roomDetails.value = rooms;

  // If no booking data found, redirect to home after short delay
  if (!bookingData.value) {
    setTimeout(() => {
      if (!bookingData.value) {
        router.push("/");
      }
    }, 2000);
  }
});

// Computed properties for display
const numberOfGuests = computed(() => {
  return 2; // You can get this from booking store if needed
});

const roomCategory = computed(() => {
  // Prefer Pinia bookingStore, fallback to localStorage
  if (bookingStore.currentBooking && bookingStore.currentBooking.categoryName) {
    return bookingStore.currentBooking.categoryName;
  }
  try {
    const searchParams = JSON.parse(
      localStorage.getItem("hotelSearchParams") || "{}"
    );
    return searchParams.categoryName || "Standard Category";
  } catch {
    return "Standard Category";
  }
});

const roomNumbers = computed(() => {
  return (
    bookingData.value?.bookings?.map((booking) => {
      const roomId = booking.code.split("-").pop();
      return roomId;
    }) || []
  );
});

const numberOfNights = computed(() => {
  if (!bookingData.value?.checkedIn || !bookingData.value?.checkedOut) return 1;

  const checkIn = new Date(bookingData.value.checkedIn);
  const checkOut = new Date(bookingData.value.checkedOut);
  const diffTime = Math.abs(checkOut - checkIn);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays || 1;
});

// Format dates
const formatBookingDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return (
    date.toLocaleDateString("en-US", {
      month: "long",
      day: "numeric",
      year: "numeric",
    }) +
    " at " +
    date.toLocaleTimeString("en-US", {
      hour: "numeric",
      minute: "2-digit",
      hour12: true,
    })
  );
};

const formatReservationDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    weekday: "long",
    month: "long",
    day: "numeric",
    year: "numeric",
  });
};

// Get payment method label
const getPaymentMethodLabel = (type) => {
  switch (type) {
    case "PayNow":
      return "Refundable";
    case "NonRefundable":
      return "Non-refundable";
    case "PayAtHotel":
      return "Pay at Hotel";
    default:
      return "Pay at Hotel";
  }
};

// Get payment status label
const getPaymentStatus = (status) => {
  switch (status) {
    case "paid":
      return "PAID";
    case "confirmed":
      return "CONFIRMED";
    case "unpaid":
      return "PENDING";
    default:
      return "CONFIRMED";
  }
};

// Download receipt function
const downloadReceipt = () => {
  if (!bookingData.value) return;

  // Create receipt content
  const receiptContent = `
BOOKING RECEIPT
===============

Confirmation: ${bookingData.value.reference}
Hotel: ${bookingData.value.hotel?.name || "Hotel"}
Amount: ₦${bookingData.value.amount?.toLocaleString() || "0"}
Check-in: ${formatReservationDate(bookingData.value.checkedIn)}
Check-out: ${formatReservationDate(bookingData.value.checkedOut)}
Guest: ${bookingData.value.customer?.firstname} ${
    bookingData.value.customer?.lastname
  }
Payment Type: ${getPaymentMethodLabel(bookingData.value.type)}
Status: ${bookingData.value.status?.toUpperCase() || "CONFIRMED"}

Generated on: ${new Date().toLocaleDateString()}
  `;

  // Create and download receipt
  const blob = new Blob([receiptContent], { type: "text/plain" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.style.display = "none";
  a.href = url;
  a.download = `booking-receipt-${bookingData.value.reference}.txt`;
  document.body.appendChild(a);
  a.click();
  window.URL.revokeObjectURL(url);
  document.body.removeChild(a);
};

// Actions
// Final navigation function - clears ONLY booking-related data, preserves auth/user data
const navigateToHome = () => {
  try {
    console.log(
      "User clicked Continue - clearing all booking data except auth/user"
    );

    // Clear the booking response (final cleanup)
    bookingStore.clearBookingResponse();

    // Only clear booking-related localStorage keys, not auth/user
    const bookingKeys = [
      "bookingResponse",
      "currentBooking",
      "selectedRooms",
      "hotelSearchParams",
      "bookingData",
      "roomSearchResults",
      "searchFilters",
    ];

    bookingKeys.forEach((key) => {
      localStorage.removeItem(key);
    });

    // Final store cleanup
    bookingStore.clearBooking();

    console.log(
      "All booking data cleared successfully, user/auth data preserved"
    );
  } catch (error) {
    console.error("Error during final cleanup:", error);
  }

  // Navigate to home
  router.push("/home");
};

// Updated onMounted with more explicit clearing
onMounted(() => {
  console.log("Confirmation page loading...");

  // Load booking data from Pinia store first
  bookingStore.loadFromStorage();

  // Try to get booking details from store first
  bookingData.value = bookingStore.getBookingDetails();

  // If no data in store, try localStorage as fallback
  if (!bookingData.value) {
    try {
      const storedBooking = localStorage.getItem("bookingResponse");
      if (storedBooking) {
        const parsed = JSON.parse(storedBooking);
        bookingData.value = parsed.data ? parsed.data : parsed;

        if (bookingData.value) {
          bookingStore.setBookingResponse(bookingData.value);
        }
      }
    } catch (error) {
      console.error("Error loading booking data from localStorage:", error);
    }
  }

  // IMMEDIATE CLEANUP - Clear everything except booking response
  try {
    const itemsToClear = [
      "selectedRooms",
      "hotelSearchParams",
      "currentBooking",
      "bookingData",
      "roomSearchResults",
      "searchFilters",
    ];

    itemsToClear.forEach((item) => {
      if (localStorage.getItem(item)) {
        localStorage.removeItem(item);
        console.log(`Cleared ${item} from localStorage`);
      }
    });

    // Clear current booking from store but keep response
    if (bookingStore.currentBooking) {
      bookingStore.currentBooking = null;
    }
  } catch (error) {
    console.error("Error during confirmation page cleanup:", error);
  }

  // Clear room details since we don't need them anymore
  roomDetails.value = [];

  console.log("Booking response available:", !!bookingData.value);
  console.log("All other booking data cleared");

  // If no booking response found, redirect to home
  if (!bookingData.value) {
    console.warn("No booking response found, redirecting to home");
    setTimeout(() => {
      if (!bookingData.value) {
        router.push("/");
      }
    }, 2000);
  }
});

const _shareBooking = async () => {
  if (!bookingData.value) return;

  const shareData = {
    title: "Hotel Booking Confirmation",
    text: `Booking confirmed at ${
      bookingData.value.hotel?.name || "Hotel"
    } - Confirmation #${bookingData.value.reference}`,
    url: window.location.href,
  };

  try {
    if (navigator.share) {
      await navigator.share(shareData);
    } else {
      // Fallback: copy to clipboard
      await navigator.clipboard.writeText(
        `${shareData.text}\n${shareData.url}`
      );
      alert("Booking details copied to clipboard!");
    }
  } catch (error) {
    console.error("Error sharing:", error);
    alert("Unable to share booking details");
  }
};
</script>

<style scoped>
/* Additional styles for the confirmation page */
.min-h-screen {
  min-height: 100vh;
}

/* Custom gradient background */
.bg-gradient-to-br {
  background: linear-gradient(135deg, #0f766e 0%, #115e59 50%, #134e4a 100%);
}
</style>
