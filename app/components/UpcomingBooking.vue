<template>
  <div class="bg-white rounded-xl shadow-lg overflow-hidden max-w-md mx-auto">
    <!-- Days to go banner -->
    <div
      v-if="daysToGo > 0"
      class="bg-teal-50 border border-teal-100 px-4 py-3 text-center"
    >
      <p class="text-teal-600 font-semibold text-lg">
        {{ daysToGo }} {{ daysToGo === 1 ? "day" : "days" }} to go
      </p>
    </div>

    <!-- Main content -->
    <div class="p-4">
      <!-- Hotel info section -->
      <div class="flex gap-3 mb-4">
        <div class="flex-shrink-0">
          <NuxtImg
            :src="booking.hotel.backgroundImage || defaultHotelImg"
            :alt="booking.hotel.name"
            class="w-20 h-20 object-cover rounded-lg"
          />
        </div>
        <div class="flex-1 min-w-0">
          <h3 class="font-bold text-lg text-gray-900 mb-1 truncate">
            {{ booking.hotel.name }}
          </h3>
          <!-- Star rating -->
          <div class="flex gap-1 mb-2">
            <UIcon
              v-for="star in 5"
              :key="star"
              name="i-lucide-star"
              class="w-4 h-4 text-yellow-400 fill-current"
            />
          </div>
          <div class="flex items-center text-gray-600 mb-1">
            <UIcon name="i-lucide-map-pin" class="w-4 h-4 mr-1 flex-shrink-0" />
            <span class="text-sm truncate">{{ hotelLocation }}</span>
          </div>
          <p class="text-xs text-gray-500">
            Booking reference: #{{ booking.reference }}
          </p>
        </div>
      </div>

      <!-- Booking details -->
      <div class="space-y-3">
        <!-- Dates -->
        <div class="flex items-start gap-3">
          <div
            class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0"
          >
            <UIcon name="i-lucide-calendar" class="w-4 h-4 text-gray-600" />
          </div>
          <div>
            <p class="text-sm font-medium text-gray-700 mb-1">Dates</p>
            <p class="text-base font-semibold text-gray-900">
              {{ formatDateRange(booking.checkedIn, booking.checkedOut) }} ({{
                nightsCount
              }}
              {{ nightsCount === 1 ? "night" : "nights" }})
            </p>
          </div>
        </div>

        <!-- Room -->
        <div class="flex items-start gap-3">
          <div
            class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0"
          >
            <UIcon name="i-lucide-key" class="w-4 h-4 text-gray-600" />
          </div>
          <div>
            <p class="text-sm font-medium text-gray-700 mb-1">Room</p>
            <p class="text-base font-semibold text-gray-900">
              {{ roomDetails }}
            </p>
          </div>
        </div>

        <!-- Guests -->
        <div class="flex items-start gap-3">
          <div
            class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0"
          >
            <UIcon name="i-lucide-users" class="w-4 h-4 text-gray-600" />
          </div>
          <div>
            <p class="text-sm font-medium text-gray-700 mb-1">Guests</p>
            <p class="text-base font-semibold text-gray-900">
              {{ guestInfo }}
            </p>
          </div>
        </div>

        <!-- Total paid -->
        <div class="flex items-start gap-3">
          <div
            class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0"
          >
            <UIcon name="i-lucide-credit-card" class="w-4 h-4 text-gray-600" />
          </div>
          <div>
            <p class="text-sm font-medium text-gray-700 mb-1">
              Total {{ booking.status === "paid" ? "paid" : "amount" }}
            </p>
            <p class="text-xl font-bold text-gray-900">
              ₦{{ booking.amount.toLocaleString() }}
            </p>
          </div>
        </div>
      </div>

      <!-- Status badge -->
      <div class="mt-4 mb-4">
        <span
          class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
          :class="statusBadgeClasses"
        >
          {{ statusText }}
        </span>
      </div>

      <!-- View Details button -->
      <!-- <UButton
        color="primary"
        variant="solid"
        size="lg"
        class="w-full block bg-primary hover:bg-primary-700 text-white font-semibold py-3"
        @click="$emit('viewDetails', booking)"
      >
        View Details
      </UButton> -->
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
}

const props = defineProps<Props>();

// Define emits
defineEmits<{
  viewDetails: [booking: Booking];
}>();

const defaultHotelImg =
  "https://res.cloudinary.com/dcm83lp3b/image/upload/v1752510054/9jahotel_sm8jgq.svg";

// Computed properties
const daysToGo = computed(() => {
  const today = new Date();
  const checkInDate = new Date(props.booking.checkedIn);
  const diffTime = checkInDate.getTime() - today.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return Math.max(0, diffDays);
});

const nightsCount = computed(() => {
  const checkIn = new Date(props.booking.checkedIn);
  const checkOut = new Date(props.booking.checkedOut);
  const diffTime = checkOut.getTime() - checkIn.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
});

const hotelLocation = computed(() => {
  const address = props.booking.hotel.address;
  if (address.toLowerCase().includes("lagos")) {
    return "Lagos, Nigeria";
  } else if (address.toLowerCase().includes("abuja")) {
    return "Abuja, Nigeria";
  } else if (address.toLowerCase().includes("kano")) {
    return "Kano, Nigeria";
  } else if (address.toLowerCase().includes("port harcourt")) {
    return "Port Harcourt, Nigeria";
  } else {
    const parts = address.split(",");
    return parts.length > 1
      ? parts[parts.length - 1].trim() + ", Nigeria"
      : "Nigeria";
  }
});

const roomDetails = computed(() => {
  const bookingsCount = props.booking.bookings.length;
  if (bookingsCount === 1) {
    return `Standard Category - Room ${
      props.booking.bookings[0].code.split("-")[1]
    }`;
  } else {
    return `${bookingsCount} Rooms - Standard Category`;
  }
});

const guestInfo = computed(() => {
  const roomCount = props.booking.bookings.length;
  const adultCount = roomCount * 1; // Assuming 2 adults per room
  return `${adultCount} Adult${adultCount !== 1 ? "s" : ""}`;
});

const statusText = computed(() => {
  switch (props.booking.status.toLowerCase()) {
    case "paid":
    case "completed":
      return "Confirmed";
    case "unpaid":
    case "upcoming":
      return "Pending Payment";
    case "cancelled":
      return "Cancelled";
    default:
      return (
        props.booking.status.charAt(0).toUpperCase() +
        props.booking.status.slice(1)
      );
  }
});

const statusBadgeClasses = computed(() => {
  switch (props.booking.status.toLowerCase()) {
    case "paid":
    case "completed":
      return "bg-green-100 text-green-800";
    case "unpaid":
    case "upcoming":
      return "bg-yellow-100 text-yellow-800";
    case "cancelled":
      return "bg-red-100 text-red-800";
    default:
      return "bg-gray-100 text-gray-800";
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
</script>

<style scoped>
.fill-current {
  fill: currentColor;
}
</style>
