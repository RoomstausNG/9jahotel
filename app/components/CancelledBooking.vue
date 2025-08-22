<template>
  <div class="bg-white rounded-xl shadow-lg overflow-hidden w-full mx-auto">
    <!-- Hotel Image Section -->
    <div class="relative">
      <div class="relative h-48 overflow-hidden">
        <img
          :src="booking.hotel.backgroundImage || defaultHotelImg"
          :alt="booking.hotel.name"
          class="w-full h-full object-cover"
        />
        <!-- Dark overlay for cancelled bookings -->
        <div class="absolute inset-0 bg-black bg-opacity-30"></div>

        <!-- Cancelled badge -->
        <div
          class="absolute top-10 left-10 transform -translate-x-1/2 -translate-y-1/2 -rotate-45 z-10"
        >
          <div
            class="bg-red-600/90 text-white text-center px-16 py-1.5 rounded-lg min-w-max"
          >
            <span
              class="text-sm font-medium block tracking-wider whitespace-nowrap"
            >
              CANCELLED
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="p-4">
      <!-- Hotel name -->
      <h3 class="font-bold text-xl text-gray-900 mb-2">
        {{ booking.hotel.name }}
      </h3>

      <!-- Location -->
      <div class="flex items-center text-gray-600 mb-3">
        <UIcon name="i-lucide-map-pin" class="w-4 h-4 mr-2 flex-shrink-0" />
        <span class="text-sm">{{ hotelLocation }}</span>
      </div>

      <!-- Booking dates -->
      <div class="flex items-center text-gray-600 mb-3">
        <UIcon name="i-lucide-calendar" class="w-4 h-4 mr-2 flex-shrink-0" />
        <span class="text-sm">{{
          formatDateRange(booking.checkedIn, booking.checkedOut)
        }}</span>
      </div>

      <!-- Cancellation date -->
      <div class="flex items-center text-gray-500 mb-4">
        <UIcon name="i-lucide-clock" class="w-4 h-4 mr-2 flex-shrink-0" />
        <span class="text-sm"
          >Cancelled on {{ formatCancellationDate(booking.updatedAt) }}</span
        >
      </div>

      <!-- Price section -->
      <div class="mb-4">
        <p class="text-3xl font-bold text-gray-900 mb-2">
          ₦{{ booking.amount.toLocaleString() }}
        </p>

        <!-- Refund status -->
        <!-- <div class="flex items-center gap-2 mb-1">
          <UIcon name="i-lucide-check-circle" class="w-5 h-5 text-green-500" />
          <span class="text-green-600 font-medium text-sm"
            >Full refund processed</span
          >
        </div>

        <p class="text-gray-500 text-sm">
          Refunded in {{ refundDays }} business days
        </p> -->
      </div>

      <!-- Action buttons -->
      <!-- <div class="space-y-3">
        <UButton
          color="primary"
          variant="solid"
          size="lg"
          class="w-full bg-teal-600 hover:bg-teal-700 text-white font-semibold py-3"
          @click="$emit('bookAgain', booking)"
        >
          Book Again
        </UButton>

        <UButton
          color="primary"
          variant="outline"
          size="lg"
          class="w-full border-teal-600 text-teal-600 hover:bg-teal-50 font-semibold py-3"
          @click="$emit('findSimilar', booking)"
        >
          Find Similar
        </UButton>
      </div> -->
    </div>
  </div>
</template>

<script setup lang="ts">
interface Customer {
  id: number;
  firstname: string;
  lastname: string;
  email: string;
  phone: string;
  avatar: string | null;
}

interface Hotel {
  id: number;
  name: string;
  code: string;
  email: string;
  address: string;
  phone: string;
  backgroundImage: string | null;
}

interface BookingItem {
  id: number;
  code: string;
}

interface Booking {
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
  customer: Customer;
  hotel: Hotel;
  bookings: BookingItem[];
}

interface Props {
  booking: Booking;
  refundDays?: number;
}

const props = withDefaults(defineProps<Props>(), {
  refundDays: 3,
});

// Define emits
defineEmits<{
  bookAgain: [booking: Booking];
  findSimilar: [booking: Booking];
}>();

const defaultHotelImg =
  "https://res.cloudinary.com/dcm83lp3b/image/upload/v1752510054/9jahotel_sm8jgq.svg";

// Computed properties
const hotelLocation = computed(() => {
  const address = props.booking.hotel.address;
  if (
    address.toLowerCase().includes("victoria island") ||
    address.toLowerCase().includes("vi")
  ) {
    return "Victoria Island, Lagos";
  } else if (address.toLowerCase().includes("ikeja")) {
    return "Ikeja, Lagos";
  } else if (address.toLowerCase().includes("lagos")) {
    return "Lagos, Nigeria";
  } else if (address.toLowerCase().includes("abuja")) {
    return "Abuja, Nigeria";
  } else if (address.toLowerCase().includes("kano")) {
    return "Kano, Nigeria";
  } else if (address.toLowerCase().includes("port harcourt")) {
    return "Port Harcourt, Nigeria";
  } else {
    // Extract the last part of the address as location
    const parts = address.split(",");
    return parts.length > 1
      ? parts[parts.length - 1].trim() + ", Nigeria"
      : "Nigeria";
  }
});

// Helper functions
function formatDateRange(checkedIn: string, checkedOut: string): string {
  const checkInDate = new Date(checkedIn);
  const checkOutDate = new Date(checkedOut);

  const options: Intl.DateTimeFormatOptions = {
    month: "short",
    day: "numeric",
    year: "numeric",
  };

  const checkInFormatted = checkInDate.toLocaleDateString("en-US", options);
  const checkOutFormatted = checkOutDate.toLocaleDateString("en-US", options);

  // If same month and year, show abbreviated format
  if (
    checkInDate.getMonth() === checkOutDate.getMonth() &&
    checkInDate.getFullYear() === checkOutDate.getFullYear()
  ) {
    const checkInDay = checkInDate.getDate();
    const checkOutDay = checkOutDate.getDate();
    const month = checkInDate.toLocaleDateString("en-US", { month: "short" });
    const year = checkInDate.getFullYear();

    return `${month} ${checkInDay}-${checkOutDay}, ${year}`;
  }

  return `${checkInFormatted} - ${checkOutFormatted}`;
}

function formatCancellationDate(dateStr: string): string {
  const date = new Date(dateStr);
  return date.toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
}
</script>

<style scoped>
/* Cancelled badge rotation */
.transform.-rotate-12 {
  transform: rotate(-12deg);
}

/* Ensure proper contrast for overlay */
.bg-opacity-30 {
  background-color: rgba(0, 0, 0, 0.3);
}
</style>
