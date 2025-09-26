<template>
  <div
    class="cursor-pointer hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1 relative border border-white/20 bg-white/60 backdrop-blur-sm rounded-lg overflow-hidden"
  >
    <!-- Discount Badge -->
    <div
      v-if="hotel.onlinePercentage && hotel.onlinePercentage > 0"
      class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded z-10"
    >
      {{ hotel.onlinePercentage }}% OFF
    </div>

    <!-- Hotel Image -->
    <div class="relative h-48 bg-gray-200 overflow-hidden w-full">
      <NuxtImg
        v-if="hotel.image"
        :src="hotel.image"
        :alt="hotel.name"
        class="w-full h-full object-cover"
      />
      <div
        v-else
        class="w-full h-full flex items-center justify-center bg-gray-100"
      >
        <Icon name="i-lucide-building" class="text-gray-400 text-4xl" />
      </div>

      <!-- Heart/Favorite Icon -->
      <FavoriteToggle
        class="absolute top-3 right-3 z-20"
        :hotel-id="hotel.id"
        :initial-favorite="hotel.isFavourite"
        @update:is-favorite="handleFavoriteChange"
      />
    </div>

    <!-- Hotel Details -->
    <div class="p-4 space-y-3">
      <!-- Hotel Name and Rating -->
      <div class="space-y-1">
        <h3 class="font-semibold text-black leading-tight">
          {{ hotel.name }}
        </h3>

        <!-- Rating and Reviews -->
        <div class="flex items-center space-x-2">
          <div v-if="hotel.starRating" class="flex text-yellow-500">
            <UIcon
              v-for="i in 5"
              :key="i"
              name="material-symbols:kid-star-sharp"
              class="w-3 h-3"
              :class="i <= hotel.starRating ? 'fill-current' : 'text-gray-300'"
            />
          </div>
          <span
            v-if="hotel.reviews?.averageRating"
            class="text-sm font-medium text-gray-700"
          >
            {{ hotel.reviews.averageRating }}/5
          </span>
          <!-- <span v-if="hotel.reviews?.total > 0" class="text-sm text-gray-500">
            ({{ hotel.reviews.total }} reviews)
          </span> -->
        </div>
      </div>

      <!-- Location -->
      <div class="flex items-start gap-2 text-gray-700">
        <Icon name="i-lucide-map-pin" class="w-4 h-4" />
        <span class="text-xs leading-3">
          {{ hotel.address || hotel.city?.name || "Location not specified" }}
        </span>
      </div>

      <!-- Amenities -->
      <div
        v-if="hotel.amenities && hotel.amenities.length > 0"
        class="flex items-center space-x-4 text-sm text-gray-600"
      >
        <div
          v-if="hotel.amenities.includes('wifi')"
          class="flex items-center space-x-1"
        >
          <Icon name="i-lucide-wifi" class="w-4 h-4" />
          <span>WiFi</span>
        </div>
        <div
          v-if="hotel.amenities.includes('breakfast')"
          class="flex items-center space-x-1"
        >
          <Icon name="i-lucide-coffee" class="w-4 h-4" />
          <span>Breakfast</span>
        </div>
        <div
          v-if="hotel.amenities.includes('pool')"
          class="flex items-center space-x-1"
        >
          <Icon name="i-lucide-waves" class="w-4 h-4" />
          <span>Pool</span>
        </div>
        <div
          v-if="hotel.amenities.includes('parking')"
          class="flex items-center space-x-1"
        >
          <Icon name="i-lucide-car" class="w-4 h-4" />
          <span>Parking</span>
        </div>
      </div>

      <!-- Price and Action -->
      <div class="flex items-center justify-between pt-2">
        <div class="space-y-1">
          <div class="flex flex-col">
            <span
              v-if="hotel.onlinePercentage && hotel.onlinePercentage > 0"
              class="text-sm font-medium text-gray-500 line-through"
            >
              ₦{{ formatPrice(hotel.roomPrice) }}
            </span>
            <div class="flex items-baseline space-x-1">
              <span class="font-bold text-primary-800">
                ₦{{
                  formatPrice(
                    calculateDiscountedPrice(
                      hotel.roomPrice,
                      hotel.onlinePercentage
                    )
                  )
                }}
              </span>
              <span class="text-xs block -mt-2 text-gray-600 font-medium"
                >/ night</span
              >
            </div>
          </div>
        </div>

        <UButton
          color="primary"
          variant="solid"
          size="sm"
          class="px-2"
          @click.stop="handleViewDealClick"
        >
          View Deal
        </UButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Hotel } from "~/types/hotel";

const props = defineProps({
  hotel: {
    type: Object as PropType<Hotel>,
    required: true,
  },
});

const emit = defineEmits(["favorite-toggled", "hotel-clicked"]);

const handleFavoriteChange = (isFavorite: boolean) => {
  emit("favorite-toggled", {
    hotelId: props.hotel.id,
    isFavorite,
  });
};

// Calculate discounted price
const calculateDiscountedPrice = (
  price: number,
  percentage?: number
): number => {
  if (typeof percentage !== "number" || percentage <= 0) {
    return price;
  }
  const discount = (price * percentage) / 100;
  return price - discount;
};

// Format price with commas for display
const formatPrice = (price: number): string => {
  return price.toLocaleString();
};


const handleViewDealClick = () => {
  emit("hotel-clicked", props.hotel);

  // Try to get check-in/check-out from localStorage
  let checkIn, checkOut;
  try {
    const params = JSON.parse(
      localStorage.getItem("hotelSearchParams") || "{}"
    );
    checkIn = params.checkIn;
    checkOut = params.checkOut;
  } catch {
    checkIn = undefined;
    checkOut = undefined;
  }

  // If both dates exist, navigate directly
  if (checkIn && checkOut) {
    navigateTo(
      `/hotels/${props.hotel.code}?checkIn=${checkIn}&checkOut=${checkOut}`
    );
    return;
  }

};

</script>
