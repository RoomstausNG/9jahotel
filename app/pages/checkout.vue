<template>
  <div class="h-full flex flex-col bg-black/50 backdrop-blur-sm relative">
    <!-- Payment Verification Overlay -->
    <div
      v-if="paymentProcessing"
      class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center"
    >
      <div
        class="bg-white/10 backdrop-blur-md rounded-2xl p-8 max-w-sm w-full mx-4 text-center border border-white/20"
      >
        <!-- Animated Payment Icon -->
        <div class="relative mb-6">
          <div
            class="w-20 h-20 mx-auto bg-primary-600/20 rounded-full flex items-center justify-center"
          >
            <Icon
              name="i-lucide-credit-card"
              class="w-10 h-10 text-primary-400"
            />
          </div>
          <!-- Pulse Animation -->
          <div class="absolute inset-0 w-20 h-20 mx-auto">
            <div
              class="w-full h-full bg-primary-400/30 rounded-full animate-ping"
            />
          </div>
        </div>

        <!-- Loading Text -->
        <h3 class="text-white text-xl font-semibold mb-2">
          {{ paymentStep }}
        </h3>
        <p class="text-gray-300 text-sm mb-6">
          {{ paymentMessage }}
        </p>

        <!-- Loading Progress -->
        <div class="w-full bg-gray-700 rounded-full h-2 mb-4">
          <div
            class="bg-primary-500 h-2 rounded-full transition-all duration-1000 ease-out"
            :style="{ width: `${loadingProgress}%` }"
          />
        </div>

        <!-- Animated Dots -->
        <div class="flex justify-center space-x-1">
          <div class="w-2 h-2 bg-primary-400 rounded-full animate-bounce"/>
          <div
            class="w-2 h-2 bg-primary-400 rounded-full animate-bounce"
            style="animation-delay: 0.1s"
          />
          <div
            class="w-2 h-2 bg-primary-400 rounded-full animate-bounce"
            style="animation-delay: 0.2s"
          />
        </div>

        <!-- Warning Text -->
        <p class="text-gray-400 text-xs mt-6">
          Please do not close this window or navigate away
        </p>
      </div>
    </div>

    <!-- Paystack Payment Popup handled in JS -->
    <!-- Error Alert -->
    <UAlert
      v-if="showErrorAlert"
      :title="'Booking Error'"
      :description="errorAlertMsg"
      color="red"
      variant="solid"
      class="fixed top-0 left-0 right-0 z-50"
      closable
      :ui="{ wrapper: 'flex items-center justify-center min-h-full p-4' }"
      @close="showErrorAlert = false"
    />

    <!-- Sticky Header -->
    <header
      class="sticky top-0 left-0 right-0 z-40 flex items-center justify-between p-4 bg-gray-900 border-b border-gray-700"
      :class="{ 'pointer-events-none opacity-75': paymentProcessing }"
    >
      <UButton
        icon="i-lucide-arrow-left"
        variant="ghost"
        color="white"
        :disabled="paymentProcessing"
        @click="$router.back()"
      />
      <h1 class="text-white font-semibold text-lg">Check Out</h1>
      <div class="w-10" />
    </header>

    <!-- Scrollable Main Content -->
    <main
      class="flex-1 overflow-y-auto p-4"
      :class="{ 'pointer-events-none opacity-75': paymentProcessing }"
    >
      <p class="text-white text-sm mb-6">
        Select the way you will like to make your payment
      </p>

      <!-- Payment Options -->
      <div class="space-y-3 mb-8">
        <!-- Pay Now -->
        <div
          class="flex items-center justify-between p-4 bg-black/50 rounded-lg cursor-pointer transition-all duration-200"
          :class="{
            'border-primary-600 border-2': selectedPaymentType === 'PayNow',
            'cursor-not-allowed opacity-50': paymentProcessing,
            'hover:bg-black/70': !paymentProcessing,
          }"
          @click="!paymentProcessing && (selectedPaymentType = 'PayNow')"
        >
          <div class="flex items-center space-x-3">
            <div
              class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
              :class="
                selectedPaymentType === 'PayNow'
                  ? 'border-primary-600'
                  : 'border-gray-500'
              "
            >
              <div
                v-if="selectedPaymentType === 'PayNow'"
                class="w-2 h-2 bg-primary-600 rounded-full"
              />
            </div>
            <div>
              <p class="text-white font-medium">Pay Now</p>
              <p class="text-gray-300 text-sm leading-4">
                Booking Guaranteed. Fully Refundable if you Cancel
              </p>
            </div>
          </div>
        </div>

        <!-- Instant Pay - Only show if rooms have nonrefundable percentage -->
        <div
          v-if="hasNonRefundableRooms"
          class="flex items-center justify-between p-4 bg-black/50 rounded-lg cursor-pointer transition-all duration-200"
          :class="{
            'border-primary-600 border-2':
              selectedPaymentType === 'NonRefundable',
            'cursor-not-allowed opacity-50': paymentProcessing,
            'hover:bg-black/70': !paymentProcessing,
          }"
          @click="!paymentProcessing && (selectedPaymentType = 'NonRefundable')"
        >
          <div class="flex items-center space-x-3">
            <div
              class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
              :class="
                selectedPaymentType === 'NonRefundable'
                  ? 'border-primary-600'
                  : 'border-gray-500'
              "
            >
              <div
                v-if="selectedPaymentType === 'NonRefundable'"
                class="w-2 h-2 bg-primary-600 rounded-full"
              />
            </div>
            <div>
              <p class="text-white font-medium">Instant Pay</p>
              <p class="text-gray-300 text-sm leading-4">
                Booking Guaranteed. Non-Refundable
              </p>
            </div>
          </div>
        </div>

        <!-- Pay On Arrival -->
        <div
          class="flex items-center justify-between p-4 bg-black/50 rounded-lg cursor-pointer transition-all duration-200"
          :class="{
            'border-primary-600 border-2': selectedPaymentType === 'PayAtHotel',
            'cursor-not-allowed opacity-50': paymentProcessing,
            'hover:bg-black/70': !paymentProcessing,
          }"
          @click="!paymentProcessing && (selectedPaymentType = 'PayAtHotel')"
        >
          <div class="flex items-center space-x-3">
            <div
              class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
              :class="
                selectedPaymentType === 'PayAtHotel'
                  ? 'border-primary-600'
                  : 'border-gray-500'
              "
            >
              <div
                v-if="selectedPaymentType === 'PayAtHotel'"
                class="w-2 h-2 bg-primary-600 rounded-full"
              />
            </div>
            <div>
              <p class="text-white font-medium">Pay On Arrival</p>
              <p class="text-gray-300 text-sm leading-4">
                Booking Not Guaranteed. Hotel may Cancel
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Summary -->
      <div class="bg-black/50 rounded-lg p-4 mb-6 backdrop-blur-sm">
        <h3 class="text-white font-semibold mb-4">Payment Summary</h3>

        <div
          v-if="roomDetails && roomDetails.length > 0"
          class="space-y-2 text-sm"
        >
          <!-- Room Category and Total Rooms -->
          <div class="flex justify-between text-gray-300">
            <span>
              {{ roomCategory }} (₦{{ averageRoomPrice.toLocaleString() }}) *
              {{ numberOfRooms }}
            </span>
            <span
              >₦{{
                roomDetails
                  .reduce((total, room) => total + (room.price || 0), 0)
                  .toLocaleString()
              }}</span
            >
          </div>

          <!-- Check-in/Check-out with Nights -->
          <div class="flex justify-between text-gray-300">
            <span
              >{{ formatDate(bookingStore.currentBooking?.checkIn) }} -
              {{ formatDate(bookingStore.currentBooking?.checkOut) }}</span
            >
            <span
              >{{ numberOfNights }}
              {{ numberOfNights === 1 ? "night" : "nights" }}</span
            >
          </div>

          <!-- Subtotal (before discount) -->
          <div class="flex justify-between text-gray-300">
            <span>Subtotal</span>
            <span class="line-through"
              >₦{{ originalTotal.toLocaleString() }}</span
            >
          </div>

          <!-- Regular Discount (Online/Non-refundable) -->
          <div
            v-if="regularDiscountAmount > 0"
            class="flex justify-between text-green-500"
          >
            <span>{{ discountLabel }}</span>
            <span class="bg-white text-green-500 font-medium px-1 rounded-sm"
              >-₦{{ regularDiscountAmount.toLocaleString() }}</span
            >
          </div>

          <!-- Coupon Discount: always show if applied -->
          <div
            v-if="hasCouponDiscount"
            class="flex justify-between text-green-500"
          >
            <span>Coupon discount</span>
            <span class="bg-white text-green-500 font-medium px-1 rounded-sm"
              >-₦{{ couponDiscountAmount.toLocaleString() }}</span
            >
          </div>

          <!-- Services charge -->
          <div class="flex justify-between text-gray-300">
            <span>Services charge</span>
            <span>₦0</span>
          </div>

          <div class="border-t border-gray-700 pt-3 mt-3">
            <div class="flex justify-between text-white font-semibold">
              <span>Total</span>
              <span>₦{{ finalTotal.toLocaleString() }}</span>
            </div>
          </div>

          <!-- Debug Info (Remove in production) -->
          <div v-if="debugMode" class="mt-4 p-2 bg-gray-800 rounded text-xs">
            <div class="text-yellow-400 font-semibold mb-2">Debug Info:</div>
            <div class="text-white">
              Coupon ID: {{ currentCouponId || "None" }}
            </div>
            <div class="text-white">
              Coupon Amount: ₦{{ currentCouponAmount.toLocaleString() }}
            </div>
            <div class="text-white">Has Coupon: {{ hasCouponDiscount }}</div>
            <div class="text-white">
              BookingStore Coupon:
              {{ JSON.stringify(bookingStore.currentBooking?.couponInfo) }}
            </div>
          </div>
        </div>

        <!-- Loading state -->
        <div v-else class="text-center text-gray-400 py-4">
          Loading room details...
        </div>
      </div>
    </main>

    <!-- Sticky Footer Button -->
    <footer
      class="sticky bottom-0 left-0 right-0 bg-white p-4 border-t border-gray-700 shadow-lg z-40"
      :class="{ 'opacity-75': paymentProcessing }"
    >
      <UButton
        color="primary"
        variant="solid"
        size="xl"
        class="w-full block transition-all duration-200"
        :loading="processing || paymentProcessing"
        :disabled="
          !roomDetails || roomDetails.length === 0 || paymentProcessing
        "
        @click="proceedToPayment"
      >
        <template v-if="paymentProcessing"> Verifying Payment... </template>
        <template v-else> Check Out </template>
      </UButton>
    </footer>
  </div>
</template>

<script setup>
import PaystackPop from "@paystack/inline-js";
const userStore = useUserStore();
const user = computed(() => userStore.user);

const bookingStore = useBookingStore();
const router = useRouter();

definePageMeta({ layout: false });
setPageLayout(computed(() => (user.value ? "auth" : "default")));

// Debug mode - set to false in production
const debugMode = ref(false);

// Set PayNow as the default payment option
const selectedPaymentType = ref("PayNow");
const processing = ref(false);
// New payment processing state for the overlay
const paymentProcessing = ref(false);
const paymentStep = ref("");
const paymentMessage = ref("");
const loadingProgress = ref(0);

// Remove alert state, use toast instead
const toast = useToast();

// Make roomDetails reactive and handle SSR properly
const roomDetails = ref([]);

// Reactive coupon data
const currentCouponId = ref(null);
const currentCouponAmount = ref(0);

// Function to load and sync coupon data
const loadCouponData = () => {
  // First try to get from bookingStore
  let couponId = bookingStore.currentBooking?.couponId;
  let couponAmount = bookingStore.currentBooking?.couponInfo?.amountWorth || 0;

  // If not in bookingStore, try localStorage
  if (!couponId) {
    try {
      const savedCoupon = localStorage.getItem("bookingCoupon");
      if (savedCoupon) {
        const couponData = JSON.parse(savedCoupon);
        if (couponData.couponId && couponData.couponInfo) {
          couponId = couponData.couponId;
          couponAmount = couponData.couponInfo.amountWorth || 0;

          // Update bookingStore with the coupon data
          bookingStore.setBooking({
            ...bookingStore.currentBooking,
            couponId: couponData.couponId,
            couponInfo: couponData.couponInfo,
          });
        }
      }
    } catch (e) {
      console.warn("Error loading coupon from localStorage:", e);
    }
  }

  // Update reactive references
  currentCouponId.value = couponId;
  currentCouponAmount.value = couponAmount;
};

onMounted(async () => {
  // Load booking data first
  bookingStore.loadFromStorage();

  // Small delay to ensure bookingStore is properly loaded
  await nextTick();

  // Load coupon data after booking data is loaded
  loadCouponData();

  // Load room details from localStorage
  try {
    const rooms = JSON.parse(localStorage.getItem("selectedRooms") || "[]");
    roomDetails.value = Array.isArray(rooms) ? rooms : [];
  } catch (error) {
    console.error("Error loading room details:", error);
    roomDetails.value = [];
  }

  // If no booking data, redirect back
  if (!bookingStore.hasBooking || roomDetails.value.length === 0) {
    router.push("/hotels");
  }

  // Force reactivity update
  await nextTick();

  // Log for debugging (remove in production)
  console.log("Booking data loaded:", bookingStore.currentBooking);
  console.log("Coupon data loaded:", {
    couponId: currentCouponId.value,
    couponAmount: currentCouponAmount.value,
  });
});

// Watch for changes in bookingStore and reload coupon data
watch(
  () => bookingStore.currentBooking,
  () => {
    loadCouponData();
  },
  { deep: true }
);

// Helper function to simulate progress
const simulateProgress = (targetProgress, duration = 1000) => {
  const startProgress = loadingProgress.value;
  const progressDiff = targetProgress - startProgress;
  const startTime = Date.now();

  const updateProgress = () => {
    const elapsed = Date.now() - startTime;
    const progress = Math.min(elapsed / duration, 1);

    loadingProgress.value = startProgress + progressDiff * progress;

    if (progress < 1) {
      requestAnimationFrame(updateProgress);
    }
  };

  requestAnimationFrame(updateProgress);
};

// Check if any rooms have nonrefundable percentage
const hasNonRefundableRooms = computed(() => {
  // Hide non-refundable if coupon is applied
  if (currentCouponId.value) return false;
  return roomDetails.value.some(
    (room) => (room.nonRefundablePercentage || 0) > 0
  );
});

// Calculate number of rooms
const numberOfRooms = computed(() => roomDetails.value.length);

// Get room category from localStorage - make it reactive
const roomCategory = ref("Standard Room");

onMounted(() => {
  try {
    const searchParams = JSON.parse(
      localStorage.getItem("hotelSearchParams") || "{}"
    );
    roomCategory.value = searchParams.categoryName || "Standard Room";
  } catch (error) {
    console.error("Error loading room category:", error);
    roomCategory.value = "Standard Room";
  }
});

// Calculate average room price
const averageRoomPrice = computed(() => {
  if (roomDetails.value.length === 0) return 0;
  const totalPrice = roomDetails.value.reduce(
    (total, room) => total + (room.price || 0),
    0
  );
  return Math.round(totalPrice / roomDetails.value.length);
});

const numberOfNights = computed(() => {
  const booking = bookingStore.currentBooking;
  if (!booking?.checkIn || !booking?.checkOut) return 1;

  const checkIn = new Date(booking.checkIn);
  const checkOut = new Date(booking.checkOut);
  const diffTime = Math.abs(checkOut - checkIn);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays || 1;
});

// Format date function
const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
  });
};

// Calculate original total (before any discount)
const originalTotal = computed(() => {
  return roomDetails.value.reduce((total, room) => {
    return total + (room.price || 0) * numberOfNights.value;
  }, 0);
});

// Computed properties for coupon
const hasCouponDiscount = computed(() => {
  return currentCouponId.value && currentCouponAmount.value > 0;
});

const couponDiscountAmount = computed(() => {
  return hasCouponDiscount.value ? currentCouponAmount.value : 0;
});

// Calculate regular discount (online/non-refundable) separate from coupon
const regularDiscountAmount = computed(() => {
  let discount = 0;
  if (selectedPaymentType.value === "NonRefundable") {
    // Use nonrefundable percentage
    discount = roomDetails.value.reduce((total, room) => {
      const price = room.price || 0;
      const percent = room.nonRefundablePercentage || 0;
      const discountAmount = ((price * percent) / 100) * numberOfNights.value;
      return total + discountAmount;
    }, 0);
  } else {
    // Use online percentage for PayNow and PayAtHotel
    discount = roomDetails.value.reduce((total, room) => {
      const price = room.price || 0;
      const percent = room.onlinePercentage || 0;
      const discountAmount = ((price * percent) / 100) * numberOfNights.value;
      return total + discountAmount;
    }, 0);
  }
  // Neglect kobos: round down to nearest naira
  return Math.floor(discount);
});

// Total discount amount (regular + coupon)
const totalDiscountAmount = computed(() => {
  return regularDiscountAmount.value + couponDiscountAmount.value;
});

// Discount label based on payment type
const discountLabel = computed(() => {
  if (roomDetails.value.length === 0) return "";

  if (selectedPaymentType.value === "NonRefundable") {
    const avgDiscount =
      roomDetails.value.reduce((total, room) => {
        return total + (room.nonRefundablePercentage || 0);
      }, 0) / roomDetails.value.length;
    return `Non-refundable discount (${Math.round(avgDiscount)}%)`;
  } else {
    const avgDiscount =
      roomDetails.value.reduce((total, room) => {
        return total + (room.onlinePercentage || 0);
      }, 0) / roomDetails.value.length;
    return `Online discount (${Math.round(avgDiscount)}%)`;
  }
});

// Final total after all discounts
const finalTotal = computed(() => {
  return Math.max(
    0,
    Math.floor(originalTotal.value - totalDiscountAmount.value)
  );
});

// Watch payment type changes and update booking store
watch(selectedPaymentType, (newType) => {
  if (bookingStore.currentBooking) {
    const updatedBooking = {
      ...bookingStore.currentBooking,
      type: newType,
      total: finalTotal.value,
    };
    bookingStore.setBooking(updatedBooking);
  }
});

const proceedToPayment = async () => {
  if (!bookingStore.currentBooking || !roomDetails.value.length) {
    toast.add({
      title: "Booking Error",
      description: "No booking data found. Please try again.",
      color: "red",
      timeout: 5000,
    });
    return;
  }

  processing.value = true;

  // Start payment processing overlay only for online payments
  const paymentType = selectedPaymentType.value;
  const requiresPayment =
    paymentType === "PayNow" || paymentType === "NonRefundable";

  if (requiresPayment) {
    paymentProcessing.value = true;
    paymentStep.value = "Initializing Payment";
    paymentMessage.value = "Setting up secure payment with Paystack...";
    loadingProgress.value = 0;
    simulateProgress(20);
  }

  try {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.public.apiBase;
    const userEmail = bookingStore.currentBooking.guest.email;

    // Track actual paid amount
    let paidAmount = finalTotal.value;

    // If payment type is PayNow or NonRefundable, handle payment with Paystack popup
    if (requiresPayment) {
      // Initialize payment
      const paymentInitBody = {
        amount: finalTotal.value,
        email: userEmail,
      };

      paymentStep.value = "Connecting to Paystack";
      paymentMessage.value = "Establishing secure connection...";
      simulateProgress(40);

      const paymentInitRes = await $fetch(
        `${apiBaseUrl}/payment/paystack/init`,
        {
          method: "POST",
          body: paymentInitBody,
        }
      );

      if (!paymentInitRes.success || !paymentInitRes.data) {
        throw new Error(
          paymentInitRes.message || "Failed to initialize payment"
        );
      }

      localStorage.setItem(
        "paystackPaymentInit",
        JSON.stringify(paymentInitRes)
      );

      const accessCode = paymentInitRes.data.access_code;
      const reference = paymentInitRes.data.reference;

      paymentStep.value = "Ready to Pay";
      paymentMessage.value = "Opening secure payment window...";
      simulateProgress(60);

      // Small delay to show the message
      await new Promise((resolve) => setTimeout(resolve, 800));

      // Open Paystack popup and wait for completion
      await new Promise((resolve, reject) => {
        const paystack = new PaystackPop();

        paymentStep.value = "Processing Payment";
        paymentMessage.value = "Complete your payment in the popup window";
        simulateProgress(80);

        paystack.resumeTransaction(accessCode, {
          onSuccess: () => {
            paymentStep.value = "Payment Successful";
            paymentMessage.value = "Verifying your payment...";
            simulateProgress(90);
            resolve();
          },
          onCancel: () => {
            toast.add({
              title: "Payment Cancelled",
              description: "You cancelled the payment process.",
              color: "red",
              timeout: 5000,
            });
            reject(new Error("Payment was cancelled."));
          },
          onError: (err) => {
            toast.add({
              title: "Payment Error",
              description: err?.message || "An error occurred with Paystack.",
              color: "red",
              timeout: 5000,
            });
            reject(new Error(err?.message || "Paystack error"));
          },
        });
      });

      // After payment, verify using the reference
      paymentStep.value = "Verifying Payment";
      paymentMessage.value = "Confirming payment with bank...";

      const verifyRes = await $fetch(
        `${apiBaseUrl}/payment/paystack/verify/${reference}`
      );

      if (!verifyRes.success) {
        throw new Error(verifyRes.message || "Payment verification failed");
      }

      localStorage.setItem("paystackPaymentVerify", JSON.stringify(verifyRes));
      simulateProgress(95);

      // Use actual paid amount from Paystack verification response if available
      if (verifyRes.data && verifyRes.data.amount) {
        // Paystack returns amount in kobo, convert to naira
        paidAmount = Math.floor(verifyRes.data.amount / 100);
      }
    }

    // Prepare booking data for API
    paymentStep.value = "Creating Booking";
    paymentMessage.value = "Finalizing your reservation...";

    const bookingData = {
      checkIn: bookingStore.currentBooking.checkIn,
      checkOut: bookingStore.currentBooking.checkOut,
      firstname: bookingStore.currentBooking.guest.firstName,
      lastname: bookingStore.currentBooking.guest.lastName,
      email: bookingStore.currentBooking.guest.email,
      phone: bookingStore.currentBooking.guest.phone,
      roomIds: roomDetails.value.map((room) => room.id),
      type: selectedPaymentType.value,
      total: paidAmount,
      // Mark as paid if payment was successful for PayNow/NonRefundable
      ...(requiresPayment ? { status: "paid" } : {}),
      // Include coupon if applied
      ...(currentCouponId.value ? { couponId: currentCouponId.value } : {}),
    };

    const bookingResponse = await $fetch(`${apiBaseUrl}/bookings`, {
      method: "POST",
      body: bookingData,
    });

    if (bookingResponse.success) {
      const responseData = bookingResponse.data;
      bookingStore.setBookingResponse(responseData);
      localStorage.setItem("bookingResponse", JSON.stringify(bookingResponse));

      try {
        const searchParams = JSON.parse(
          localStorage.getItem("hotelSearchParams") || "{}"
        );
        const updatedBooking = {
          ...bookingStore.currentBooking,
          categoryName: searchParams.categoryName || roomCategory.value,
        };
        bookingStore.setBooking(updatedBooking);
      } catch (error) {
        console.warn("Could not update booking with category name:", error);
      }

      if (requiresPayment) {
        paymentStep.value = "Booking Complete";
        paymentMessage.value = "Redirecting to confirmation...";
        simulateProgress(100);

        // Small delay to show completion
        await new Promise((resolve) => setTimeout(resolve, 1000));
      }

      router.push("/booking-confirmation");
    } else {
      throw new Error(bookingResponse.message || "Failed to create booking");
    }
  } catch (error) {
    let errorMsg = "Failed to create booking. Please try again.";
    if (error && error.data && error.data.message) {
      errorMsg = error.data.message;
    } else if (error && error.message) {
      errorMsg = error.message;
    }
    console.error("Booking creation error:", error);
    toast.add({
      title: "Booking Error",
      description: errorMsg,
      color: "red",
      timeout: 5000,
    });
  } finally {
    processing.value = false;
    paymentProcessing.value = false;
    loadingProgress.value = 0;
  }
};
</script>

<style scoped>
.bg-gray-900 {
  background-color: #111827;
}

.bg-gray-800 {
  background-color: #1f2937;
}

.border-gray-700 {
  border-color: #374151;
}

.text-gray-300 {
  color: #d1d5db;
}

.text-gray-400 {
  color: #9ca3af;
}

/* Custom animations */
@keyframes ping {
  75%,
  100% {
    transform: scale(2);
    opacity: 0;
  }
}

.animate-ping {
  animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes bounce {
  0%,
  100% {
    transform: translateY(-25%);
    animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
  }
  50% {
    transform: none;
    animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
  }
}

.animate-bounce {
  animation: bounce 1s infinite;
}
</style>
