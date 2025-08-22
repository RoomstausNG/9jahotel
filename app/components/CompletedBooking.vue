<template>
  <div class="bg-white rounded-xl shadow-lg overflow-hidden max-w-2xl">
    <!-- Hotel Image Section -->
    <div class="relative">
      <div class="relative h-64 overflow-hidden">
        <img
          :src="booking.hotel.backgroundImage || defaultHotelImg"
          :alt="booking.hotel.name"
          class="w-full h-full object-cover"
        />
        <!-- Dark overlay for better text visibility -->
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>

        <!-- Completed badge -->
        <div class="absolute top-4 left-4">
          <div
            class="flex items-center gap-2 bg-primary text-white px-3 py-2 rounded-full"
          >
            <UIcon name="i-lucide-check" class="w-4 h-4" />
            <span class="text-sm font-medium">Completed</span>
          </div>
        </div>

        <!-- Hotel info overlay -->
        <div class="absolute bottom-4 left-4 right-4 text-white">
          <h3 class="text-2xl font-bold mb-2">{{ booking.hotel.name }}</h3>

          <div class="flex items-center gap-1 mb-3">
            <UIcon name="i-lucide-map-pin" class="w-4 h-4" />
            <span class="text-sm">{{ hotelLocation }}</span>
          </div>

          <!-- Star rating -->
          <div class="flex items-center gap-1 mb-3">
            <UIcon
              v-for="star in 5"
              :key="star"
              name="i-lucide-star"
              :class="[
                'w-4 h-4',
                star <= Math.floor(hotelRating)
                  ? 'text-primary-400 fill-current'
                  : 'text-gray-400',
              ]"
            />
          </div>

          <!-- Date and nights -->
          <div class="flex items-center gap-2 text-sm">
            <UIcon name="i-lucide-calendar" class="w-4 h-4" />
            <span>{{
              formatDateRange(booking.checkedIn, booking.checkedOut)
            }}</span>
            <span class="text-primary-300">•</span>
            <span
              >{{ nightsCount }}
              {{ nightsCount === 1 ? "night" : "nights" }}</span
            >
          </div>
        </div>
      </div>
    </div>

    <!-- Booking Details Section -->
    <div class="p-4">
      <div class="flex justify-between items-start mb-4">
        <!-- Left side - Room and guests info -->
        <div class="space-y-2">
          <div class="flex items-center gap-2 text-gray-700">
            <UIcon name="i-lucide-bed" class="w-4 h-4" />
            <span class="font-medium">{{ roomDetails }}</span>
          </div>
          <div class="flex items-center gap-2 text-gray-700">
            <UIcon name="i-lucide-users" class="w-4 h-4" />
            <span>{{ guestInfo }}</span>
          </div>
        </div>

        <!-- Right side - Price and reference -->
        <div class="text-right">
          <p class="text-2xl font-bold text-gray-900 mb-1">
            ₦{{ booking.amount.toLocaleString() }}
          </p>
          <p class="text-sm text-gray-500">#{{ booking.reference }}</p>
        </div>
      </div>

      <!-- User rating -->
      <div
        v-if="userRating"
        class="flex items-center gap-2 mb-4 p-3 bg-gray-50 rounded-lg"
      >
        <UIcon
          name="i-lucide-star"
          class="w-5 h-5 text-primary-600 fill-current"
        />
        <span class="text-gray-700">
          You rated this
          <span class="font-semibold text-primary-600">{{ userRating }}★</span>
        </span>
      </div>

      <!-- Action buttons -->
      <div class="flex gap-3">
        <UButton
          color="primary"
          variant="solid"
          size="lg"
          class="flex-1 bg-primary hover:bg-primary-700 text-white font-semibold py-3"
          @click="$emit('bookAgain', booking)"
        >
          <UIcon name="i-lucide-rotate-ccw" class="w-4 h-4 mr-2" />
          Book Again
        </UButton>

        <UButton
          color="gray"
          variant="outline"
          size="lg"
          class="flex-1 border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold py-3"
          @click="$emit('viewDetails', booking)"
        >
          <UIcon name="i-lucide-file-text" class="w-4 h-4 mr-2" />
          View Details
        </UButton>
      </div>
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
  userRating?: number;
}

interface Props {
  booking: Booking;
  userRating?: number;
}

const props = withDefaults(defineProps<Props>(), {
  userRating: 4.7,
});

// Define emits
defineEmits<{
  bookAgain: [booking: Booking];
  viewDetails: [booking: Booking];
}>();

const defaultHotelImg =
  "https://res.cloudinary.com/dcm83lp3b/image/upload/v1752510054/9jahotel_sm8jgq.svg";

// Computed properties
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
    return "Executive Suite";
  } else {
    return `${bookingsCount} Rooms - Executive Suite`;
  }
});

const guestInfo = computed(() => {
  const roomCount = props.booking.bookings.length;
  const adultCount = roomCount * 1;
  return `${adultCount} Adult${adultCount !== 1 ? "s" : ""}`;
});

const hotelRating = computed(() => {
  return 4.5;
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

.bg-opacity-40 {
  background-color: rgba(0, 0, 0, 0.4);
}

.text-primary-400.fill-current {
  fill: currentColor;
}
</style>
