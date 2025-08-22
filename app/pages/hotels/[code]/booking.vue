<template>
  <div class="h-full flex flex-col">
    <!-- Fixed Header -->
    <div
      class="flex-shrink-0 gap-x-4 flex justify-between p-4 bg-gray-900/95 backdrop-blur-sm border-b border-gray-700 z-[60] w-full"
    >
      <UButton
        icon="i-lucide-arrow-left"
        variant="ghost"
        color="white"
        @click="$router.back()"
      />
      <div class="flex flex-1 justify-center items-center gap-2">
        <div class="text-center">
          <h1 class="font-semibold text-white">Booking Details</h1>
        </div>
      </div>
      <div class="w-10" />
    </div>

    <!-- Content Area -->
    <div class="flex-1 overflow-y-auto bg-white/10 backdrop-blur-sm">
      <!-- Hotel Info Section -->
      <div
        class="bg-white/75 backdrop-blur-sm mx-4 mt-4 rounded-lg overflow-hidden"
      >
        <div class="flex p-4">
          <NuxtImg
            :src="
              hotelImage ||
              'https://res.cloudinary.com/dcm83lp3b/image/upload/v1752508514/slide_image_upd9ht.webp'
            "
            alt="Hotel"
            class="w-20 h-20 rounded-lg object-cover"
          />
          <div class="ml-4 flex-1">
            <h2 class="font-semibold text-black text-lg leading-5">
              {{ hotelName }}
            </h2>
            <div class="flex items-center mt-1">
              <div class="flex items-center">
                <UIcon
                  v-for="i in 5"
                  :key="i"
                  name="i-lucide-star"
                  class="w-3 h-3 text-yellow-500 fill-current"
                />
              </div>
            </div>
            <div class="mt-2">
              <div
                class="bg-primary-500 text-white text-xs px-2 py-1 rounded-sm inline-block font-medium"
              >
                {{ categoryName }} (₦{{
                  selectedRooms.length > 0
                    ? (selectedRooms[0].price || 0).toLocaleString()
                    : "0"
                }}) - Room {{ selectedRoomNumbers.join(", ") }}
              </div>
            </div>
            <div class="mt-2 text-sm text-black">
              <div class="text-sm">
                📅 {{ formatDate(checkIn) }} - {{ formatDate(checkOut) }}
              </div>
              <div class="text-sm">
                👥 {{ guestCount }} Adults • {{ nightCount }} Nights
              </div>
            </div>
            <UButton
              variant="ghost"
              size="xs"
              color="primary"
              class="mt-2"
              @click="editBooking"
            >
              Edit
            </UButton>
          </div>
        </div>
      </div>

      <!-- Guest Information Form -->
      <div class="px-4 py-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-black font-semibold">Guest Information</h3>
        </div>

        <form
          class="space-y-4 shadow-xl border-white p-4 rounded-lg bg-white/30"
          @submit.prevent="validateAndContinue"
        >
          <!-- First Name -->
          <div>
            <UFormField label="First Name" class="text-black w-full">
              <UInput
                v-model="form.firstName"
                :placeholder="
                  userStore.isAuthenticated
                    ? 'Pre-filled from account'
                    : 'Enter your first name'
                "
                :color="errors.firstName ? 'red' : 'primary'"
                :disabled="userStore.isAuthenticated"
                :class="{ readonly: userStore.isAuthenticated }"
                size="lg"
                required
                class="w-full"
              />
              <template #trailing>
                <UIcon
                  v-if="form.firstName && !errors.firstName"
                  name="i-lucide-check-circle"
                  class="w-5 h-5 text-primary-500"
                />
                <UIcon
                  v-if="userStore.isAuthenticated"
                  name="i-lucide-lock"
                  class="w-4 h-4 text-gray-400"
                />
              </template>
            </UFormField>
            <p v-if="errors.firstName" class="text-red-400 text-xs mt-1">
              {{ errors.firstName }}
            </p>
          </div>

          <!-- Last Name -->
          <div>
            <UFormField label="Last Name" class="text-black">
              <UInput
                v-model="form.lastName"
                :placeholder="
                  userStore.isAuthenticated
                    ? 'Pre-filled from account'
                    : 'Enter your last name'
                "
                :color="errors.lastName ? 'red' : 'primary'"
                :disabled="userStore.isAuthenticated"
                :class="{ readonly: userStore.isAuthenticated }"
                size="lg"
                required
                class="w-full"
              />
              <template #trailing>
                <UIcon
                  v-if="form.lastName && !errors.lastName"
                  name="i-lucide-check-circle"
                  class="w-5 h-5 text-primary-500"
                />
                <UIcon
                  v-if="userStore.isAuthenticated"
                  name="i-lucide-lock"
                  class="w-4 h-4 text-gray-400"
                />
              </template>
            </UFormField>
            <p v-if="errors.lastName" class="text-red-400 text-xs mt-1">
              {{ errors.lastName }}
            </p>
          </div>

          <!-- Email Address -->
          <div>
            <UFormField label="Email Address" class="text-black">
              <UInput
                v-model="form.email"
                :placeholder="
                  userStore.isAuthenticated
                    ? 'Pre-filled from account'
                    : 'Enter your email address'
                "
                type="email"
                :color="errors.email ? 'red' : 'primary'"
                :disabled="userStore.isAuthenticated"
                :class="{ readonly: userStore.isAuthenticated }"
                size="lg"
                required
                class="w-full"
                @blur="!userStore.isAuthenticated && checkEmailExists()"
              />
              <template #trailing>
                <UIcon
                  v-if="
                    form.email &&
                    !errors.email &&
                    !emailChecking &&
                    !userStore.isAuthenticated
                  "
                  name="i-lucide-check-circle"
                  class="w-5 h-5 text-primary-500"
                />
                <UIcon
                  v-if="emailChecking"
                  name="i-lucide-loader-2"
                  class="w-5 h-5 text-gray-400 animate-spin"
                />
                <UIcon
                  v-if="userStore.isAuthenticated"
                  name="i-lucide-lock"
                  class="w-4 h-4 text-gray-400"
                />
              </template>
            </UFormField>
            <p v-if="errors.email" class="text-red-400 text-xs mt-1">
              {{ errors.email }}
            </p>
            <div v-if="showLoadCustomer && foundCustomer" class="mt-2 text-xs">
              <span class="text-white">
                Account exists,
                <span
                  class="text-primary-600 texi-sm font-semibold cursor-pointer underline"
                  @click="loadCustomerDetails"
                  >load your details</span
                >
              </span>
            </div>
          </div>

          <!-- Phone Number -->
          <div>
            <UFormField label="Phone Number" class="text-black">
              <UInput
                v-model="form.phone"
                :placeholder="
                  userStore.isAuthenticated
                    ? 'Pre-filled from account'
                    : 'Enter your phone number'
                "
                type="tel"
                :color="errors.phone ? 'red' : 'primary'"
                :disabled="userStore.isAuthenticated"
                :class="{ readonly: userStore.isAuthenticated }"
                size="lg"
                required
                class="w-full"
                @blur="!userStore.isAuthenticated && checkPhoneExists()"
              />
              <template #trailing>
                <UIcon
                  v-if="
                    form.phone &&
                    !errors.phone &&
                    !phoneChecking &&
                    !userStore.isAuthenticated
                  "
                  name="i-lucide-check-circle"
                  class="w-5 h-5 text-primary-500"
                />
                <UIcon
                  v-if="phoneChecking"
                  name="i-lucide-loader-2"
                  class="w-5 h-5 text-gray-400 animate-spin"
                />
                <UIcon
                  v-if="userStore.isAuthenticated"
                  name="i-lucide-lock"
                  class="w-4 h-4 text-gray-400"
                />
              </template>
            </UFormField>
            <p v-if="errors.phone" class="text-red-400 text-xs mt-1">
              {{ errors.phone }}
            </p>
          </div>
        </form>
      </div>

      <!-- Terms & Policies -->
      <div class="px-4 py-4 space-y-4">
        <!-- Cancellation Policy -->
        <UAccordion :items="policyItems" class="text-black" />
      </div>

      <!-- Price Details -->
      <div class="px-4 py-4">
        <div class="bg-white/50 backdrop-blur-sm rounded-lg p-4">
          <h4 class="text-black font-semibold mb-4">Price Details</h4>

          <div class="space-y-2 text-xs">
            <div class="flex justify-between text-black font-medium">
              <span>
                {{ categoryName }} (₦{{
                  selectedRooms.length > 0
                    ? (selectedRooms[0].price || 0).toLocaleString()
                    : "0"
                }}) *
                {{ selectedRooms.length }}
              </span>
              <span
                >₦{{
                  selectedRooms
                    .reduce((total, room) => total + (room.price || 0), 0)
                    .toLocaleString()
                }}</span
              >
            </div>
            <div class="flex justify-between text-black font-medium">
              <span>Number of nights</span>
              <span class="font-semibold"
                >{{ nightCount }}
                {{ nightCount === 1 ? "night" : "nights" }}</span
              >
            </div>
            <div class="flex justify-between text-black font-medium">
              <span>Subtotal</span>
              <span class="font-semibold"
                >₦{{ subtotal.toLocaleString() }}</span
              >
            </div>
            <div
              v-if="totalDiscount > 0"
              class="flex justify-between text-green-700"
            >
              <span>Online discount ({{ averageDiscountPercentage }}%)</span>
              <span class="font-semibold"
                >-₦{{ totalDiscount.toLocaleString() }}</span
              >
            </div>
            <div
              v-if="couponApplied && couponInfo"
              class="flex justify-between text-green-700"
            >
              <span>Coupon discount</span>
              <span class="font-semibold"
                >-₦{{ couponInfo.amountWorth.toLocaleString() }}</span
              >
            </div>
            <div class="border-t border-gray-600 pt-2 mt-2">
              <div
                class="flex justify-between text-black font-semibold text-sm"
              >
                <span>Total</span>
                <span class="font-semibold"
                  >₦{{ totalAmount.toLocaleString() }}</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Coupon Row -->
      <div class="px-4 pb-4">
        <div class="flex items-center w-full gap-2">
          <UInput
            v-model="couponCode"
            :disabled="couponApplied"
            placeholder="Enter coupon code"
            size="lg"
            class="flex-1"
          />
          <UButton
            color="success"
            label="Apply"
            variant="solid"
            size="lg"
            :loading="couponChecking"
            :disabled="couponApplied || !couponCode"
            @click="checkCoupon"
          />
        </div>
        <div v-if="couponApplied && couponInfo" class="">
          <div
            class="text-xs text-black underline cursor-pointer"
            @click="removeCoupon"
          >
            Remove coupon
          </div>
        </div>
      </div>
    </div>

    <!-- Fixed Continue Button -->
    <div class="flex-shrink-0 p-4 bg-gray-900/95 border-t border-gray-700">
      <UButton
        color="primary"
        variant="solid"
        size="lg"
        class="w-full block"
        :loading="submitting"
        @click="validateAndContinue"
      >
        Confirm Booking
      </UButton>
      <div
        class="flex items-center justify-center gap-2 mt-2 text-xs text-white"
      >
        <UIcon name="i-lucide-shield-check" class="w-4 h-4" />
        <span>Secure payment</span>
        <UIcon name="i-lucide-zap" class="w-4 h-4" />
        <span>Instant confirmation</span>
      </div>
    </div>

    <!-- Login Modal - Disabled for now -->
    <!--
    <UModal
      v-model="showLoginModal"
      prevent-close
      :ui="{ wrapper: 'flex items-center justify-center min-h-full p-4' }"
    >
      <UCard
        :ui="{
          ring: '',
          divide: 'divide-y divide-gray-100 dark:divide-gray-800',
          body: { padding: 'px-4 py-5 sm:p-6' },
          header: { padding: 'px-4 py-5 sm:px-6' },
        }"
        class="max-w-md w-full"
      >
        <template #header>
          <div class="flex items-center justify-between">
            <h3
              class="text-base font-semibold leading-6 text-gray-900 dark:text-white"
            >
              {{ modalMode === "login" ? "Account Found" : "Forgot Password" }}
            </h3>
            <UButton
              color="gray"
              variant="ghost"
              icon="i-heroicons-x-mark-20-solid"
              class="-my-1"
              @click="closeLoginModal"
            />
          </div>
        </template>

        <div v-if="modalMode === 'login'" class="space-y-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">
            We found an account associated with this {{ accountFoundType }}.
            Please log in to continue with your booking.
          </p>

          <form class="space-y-4" @submit.prevent="loginFromModal">
            <UFormField label="Email" name="email">
              <UInput
                v-model="loginForm.email"
                placeholder="Enter your email address"
                type="email"
                required
                :color="loginError ? 'red' : 'primary'"
              />
            </UFormField>

            <UFormField label="Password" name="password">
              <UInput
                v-model="loginForm.password"
                placeholder="Enter your password"
                type="password"
                required
                :color="loginError ? 'red' : 'primary'"
              />
            </UFormField>

            <UAlert
              v-if="loginError"
              icon="i-heroicons-exclamation-triangle"
              color="red"
              variant="soft"
              :title="loginError"
            />

            <div class="flex items-center justify-between text-sm">
              <UButton
                variant="link"
                color="primary"
                size="sm"
                @click="modalMode = 'forgot'"
              >
                Forgot Password?
              </UButton>
            </div>

            <UButton
              type="submit"
              color="primary"
              variant="solid"
              size="lg"
              class="w-full"
              :loading="loggingIn"
            >
              Log In & Continue Booking
            </UButton>
          </form>

          <div class="text-center pt-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Don't have an account?
              <UButton
                variant="link"
                color="primary"
                size="sm"
                @click="proceedAsGuest"
              >
                Continue as Guest
              </UButton>
            </p>
          </div>
        </div>

        <div v-else-if="modalMode === 'forgot'" class="space-y-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Enter your email address and we'll send you a link to reset your
            password.
          </p>

          <form class="space-y-4" @submit.prevent="handleForgotPassword">
            <UFormField label="Email" name="email">
              <UInput
                v-model="forgotPasswordForm.email"
                placeholder="Enter your email address"
                type="email"
                required
                :color="forgotPasswordError ? 'red' : 'primary'"
              />
            </UFormField>

            <UAlert
              v-if="forgotPasswordError"
              icon="i-heroicons-exclamation-triangle"
              color="red"
              variant="soft"
              :title="forgotPasswordError"
            />

            <UAlert
              v-if="forgotPasswordSuccess"
              icon="i-heroicons-check-circle"
              color="green"
              variant="soft"
              :title="forgotPasswordSuccess"
            />

            <UButton
              type="submit"
              color="primary"
              variant="solid"
              size="lg"
              class="w-full"
              :loading="sendingReset"
            >
              Send Reset Link
            </UButton>
          </form>

          <div class="text-center pt-4">
            <UButton
              variant="link"
              color="primary"
              size="sm"
              @click="modalMode = 'login'"
            >
              Back to Login
            </UButton>
          </div>
        </div>
      </UCard>
    </UModal>
    -->
  </div>
</template>

<script setup>
const userStore = useUserStore();
const bookingStore = useBookingStore();
const user = computed(() => userStore.user);

definePageMeta({ layout: false });
setPageLayout(computed(() => (user.value ? "auth" : "default")));

const router = useRouter();
const config = useRuntimeConfig();
const baseURL = config.public.apiBase || "/api";

// Reactive data
const form = reactive({
  firstName: "",
  lastName: "",
  email: "",
  phone: "",
});

const errors = reactive({
  firstName: "",
  lastName: "",
  email: "",
  phone: "",
});

// Coupon logic
const couponCode = ref("");
const couponApplied = ref(false);
const couponInfo = ref(null);
const couponChecking = ref(false);

const checkCoupon = async () => {
  if (!couponCode.value.trim()) return;
  couponChecking.value = true;
  try {
    // Get total amount for booking
    const amount = totalAmount.value;
    // Get bearer token from userStore (assume userStore.token or userStore.getToken())
    const token =
      userStore.token ||
      (typeof userStore.getToken === "function" ? userStore.getToken() : "");
    const response = await $fetch(`${baseURL}/bookings/coupon/check`, {
      method: "POST",
      body: { code: couponCode.value, amount },
    });
    if (response.success && response.data) {
      couponApplied.value = true;
      couponInfo.value = response.data;
      // Save coupon info to bookingStore for checkout page
      bookingStore.setBooking({
        ...bookingStore.currentBooking,
        couponId: response.data.couponId,
        couponInfo: response.data,
      });
      // Persist coupon info to localStorage
      localStorage.setItem(
        "bookingCoupon",
        JSON.stringify({
          couponId: response.data.couponId,
          couponInfo: response.data,
          couponCode: couponCode.value,
        })
      );
      showToast(response.message || "Coupon applied.", "success");
    } else {
      showToast(response.message || "Invalid coupon.", "error");
    }
  } catch (error) {
    showToast(error?.data?.message || "Failed to check coupon.", "error");
  } finally {
    couponChecking.value = false;
  }
};

const removeCoupon = () => {
  couponApplied.value = false;
  couponInfo.value = null;
  couponCode.value = "";
  // Remove coupon info from bookingStore
  bookingStore.setBooking({
    ...bookingStore.currentBooking,
    couponId: null,
    couponInfo: null,
  });
  // Remove coupon info from localStorage
  localStorage.removeItem("bookingCoupon");
};

function showToast(msg, type = "info") {
  // Use Nuxt UI toast or fallback
  if (typeof useToast === "function") {
    useToast().add({ title: msg, color: type });
  } else if (window.$toast) {
    window.$toast(msg, { type });
  } else {
    alert(msg);
  }
}

const accountFoundType = ref("");
const emailChecking = ref(false);
const phoneChecking = ref(false);
const submitting = ref(false);

// New: found customer logic
const foundCustomer = ref(null);
const showLoadCustomer = ref(false);

const checkEmailExists = async () => {
  // Only for unauthenticated users
  if (userStore.isAuthenticated) return;
  if (!form.email || !form.email.includes("@")) return;

  emailChecking.value = true;
  foundCustomer.value = null;
  showLoadCustomer.value = false;
  try {
    const response = await $fetch(
      `${baseURL}/customers/find-by-email?email=${encodeURIComponent(
        form.email
      )}`,
      {
        method: "GET",
      }
    );
    if (response.success && response.data) {
      foundCustomer.value = response.data;
      showLoadCustomer.value = true;
    }
  } catch {
    // If 404, do nothing
    foundCustomer.value = null;
    showLoadCustomer.value = false;
  } finally {
    emailChecking.value = false;
  }
};

const loadCustomerDetails = () => {
  if (foundCustomer.value) {
    form.firstName = foundCustomer.value.firstName || "";
    form.lastName = foundCustomer.value.lastName || "";
    form.email = foundCustomer.value.email || "";
    form.phone = foundCustomer.value.phone || "";
    showLoadCustomer.value = false;
  }
};

// Booking data from localStorage
const hotelName = ref("");
const hotelImage = ref("");
const categoryName = ref("");
const selectedRooms = ref([]);
const selectedRoomNumbers = computed(() =>
  selectedRooms.value.map((room) => room.number)
);
const checkIn = ref("");
const checkOut = ref("");
const guestCount = ref(2);

// Computed properties
const nightCount = computed(() => {
  if (!checkIn.value || !checkOut.value) return 1;
  const start = new Date(checkIn.value);
  const end = new Date(checkOut.value);
  const diffTime = Math.abs(end - start);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays || 1;
});

const baseRoomRate = computed(() => {
  if (selectedRooms.value.length === 0) return 0;
  return selectedRooms.value.reduce((total, room) => {
    return total + (room.price || 0);
  }, 0);
});

// Neglect kobos in all price calculations (round down to nearest naira)
const subtotal = computed(() => {
  return Math.floor(baseRoomRate.value * nightCount.value);
});

const totalDiscount = computed(() => {
  if (selectedRooms.value.length === 0) return 0;
  const discount =
    selectedRooms.value.reduce((total, room) => {
      const price = room.price || 0;
      const discount = room.onlinePercentage || 0;
      const discountAmount = (price * discount) / 100;
      return total + discountAmount;
    }, 0) * nightCount.value;
  return Math.floor(discount);
});

const averageDiscountPercentage = computed(() => {
  if (selectedRooms.value.length === 0) return 0;
  const totalPercentage = selectedRooms.value.reduce((total, room) => {
    return total + (room.onlinePercentage || 0);
  }, 0);
  return Math.round(totalPercentage / selectedRooms.value.length);
});

const couponAmount = computed(() => {
  return couponApplied.value && couponInfo.value?.amountWorth
    ? couponInfo.value.amountWorth
    : 0;
});

const roomRate = computed(() => {
  return Math.floor(subtotal.value - totalDiscount.value);
});

const totalAmount = computed(() => {
  // Subtract coupon discount from roomRate
  return Math.max(0, Math.floor(roomRate.value - couponAmount.value));
});

// Policy items for accordion
const policyItems = [
  {
    label: "Cancellation Policy",
    icon: "i-lucide-calendar-x",
    content: "Free cancellation until 48 hours before check-in.",
    defaultOpen: false,
  },
  {
    label: "House Rules",
    icon: "i-lucide-home",
    content: "Check-in: 3:00 PM - 11:00 PM, No pets allowed",
    defaultOpen: false,
  },
  {
    label: "Payment Terms",
    icon: "i-lucide-credit-card",
    content: "Full payment will be processed upon booking confirmation.",
    defaultOpen: false,
  },
];

// Function to prefill user data when authenticated
const prefillUserData = () => {
  if (userStore.isAuthenticated && user.value) {
    form.firstName = user.value.firstName || "";
    form.lastName = user.value.lastName || "";
    form.email = user.value.email || "";
    form.phone = user.value.phone || "";

    console.log("Pre-filled user data:", {
      firstName: form.firstName,
      lastName: form.lastName,
      email: form.email,
      phone: form.phone,
    });
  }
};

const loadBookingData = () => {
  if (typeof window !== "undefined") {
    try {
      // Load search params
      const searchParams = JSON.parse(
        localStorage.getItem("hotelSearchParams") || "{}"
      );
      hotelName.value = searchParams.hotelName || "";
      categoryName.value = searchParams.categoryName || "";
      checkIn.value = searchParams.checkIn || "";
      checkOut.value = searchParams.checkOut || "";
      guestCount.value = searchParams.guestCount || 2;

      // Load selected rooms
      const rooms = JSON.parse(localStorage.getItem("selectedRooms") || "[]");
      selectedRooms.value = rooms;

      // Load hotel image from localStorage if available
      let img = "";
      try {
        const hotelDetails = JSON.parse(
          localStorage.getItem("hotelDetails") || "null"
        );
        if (hotelDetails && hotelDetails.image) {
          img = hotelDetails.image;
        }
      } catch (e) {
        img = "";
      }
      hotelImage.value =
        img ||
        searchParams.hotelImage ||
        "https://res.cloudinary.com/dcm83lp3b/image/upload/v1752508514/slide_image_upd9ht.webp";

      // Calculate pricing
      calculatePricing();
    } catch (error) {
      console.error("Error loading booking data:", error);
    }
  }
};

onMounted(async () => {
  // Load user data from storage first
  await userStore.loadFromStorage();

  // Then load booking data and prefill form
  loadBookingData();
  prefillUserData();

  // Load coupon info from localStorage if present
  const savedCoupon = localStorage.getItem("bookingCoupon");
  if (savedCoupon) {
    try {
      const couponData = JSON.parse(savedCoupon);
      if (couponData.couponId && couponData.couponInfo) {
        couponApplied.value = true;
        couponInfo.value = couponData.couponInfo;
        couponCode.value = couponData.couponCode || "";
        bookingStore.setBooking({
          ...bookingStore.currentBooking,
          couponId: couponData.couponId,
          couponInfo: couponData.couponInfo,
        });
      }
    } catch (e) {
      // Ignore parse errors
    }
  }
});

// Watch for user changes and re-prefill form
watch(
  () => user.value,
  (newUser) => {
    if (newUser) {
      prefillUserData();
    }
  },
  { immediate: true }
);

const calculatePricing = () => {
  // No additional calculations needed - everything is handled by computed properties
};

const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
  });
};

const checkPhoneExists = async () => {
  // Skip check if user is already logged in
  if (userStore.isAuthenticated) return;

  if (!form.phone) return;

  phoneChecking.value = true;
  try {
    const response = await $fetch(`${baseURL}/auth/check-phone`, {
      method: "POST",
      body: { phone: form.phone },
    });

    if (response.exists) {
      accountFoundType.value = "phone number";
      // loginForm.email = form.email; // Use email for login even when phone exists
      // forgotPasswordForm.email = form.email;
      // showLoginModal.value = true; // Disabled for now
    }
  } catch (error) {
    console.error("Phone check error:", error);
  } finally {
    phoneChecking.value = false;
  }
};

const validateForm = () => {
  // Reset errors
  Object.keys(errors).forEach((key) => (errors[key] = ""));

  let isValid = true;

  if (!form.firstName.trim()) {
    errors.firstName = "First name is required";
    isValid = false;
  }

  if (!form.lastName.trim()) {
    errors.lastName = "Last name is required";
    isValid = false;
  }

  if (!form.email.trim()) {
    errors.email = "Email is required";
    isValid = false;
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.email = "Please enter a valid email";
    isValid = false;
  }

  if (!form.phone.trim()) {
    errors.phone = "Phone number is required";
    isValid = false;
  }

  return isValid;
};

const validateAndContinue = async () => {
  if (!validateForm()) return;

  submitting.value = true;
  try {
    // Extract room IDs from selected rooms
    const roomIds = selectedRooms.value.map((room) => room.id || room.number);

    // Prepare booking data in the required format
    const bookingData = {
      checkIn: checkIn.value,
      checkOut: checkOut.value,
      guest: {
        firstName: form.firstName,
        lastName: form.lastName,
        email: form.email,
        phone: form.phone,
      },
      roomIds: roomIds,
      type: "PayNow", // Make PayNow the default payment type
      total: totalAmount.value,
    };

    // Include both couponId and couponInfo if coupon is applied
    if (couponApplied.value && couponInfo.value?.couponId) {
      bookingData.couponId = couponInfo.value.couponId;
      bookingData.couponInfo = couponInfo.value; // Add this line
    }

    // Save booking to store
    bookingStore.setBooking(bookingData);

    // Navigate to checkout page
    navigateTo("/checkout");
  } catch (error) {
    console.error("Booking error:", error);
    alert("Failed to prepare booking. Please try again.");
  } finally {
    submitting.value = false;
  }
};

const editBooking = () => {
  router.back();
};
</script>

<style scoped>
.bg-white\/10 {
  background-color: rgba(255, 255, 255, 0.1);
}

.bg-white\/20 {
  background-color: rgba(255, 255, 255, 0.2);
}

/* Style for readonly inputs to make them look distinct but still visible */
:deep(.readonly input) {
  background-color: rgba(255, 255, 255, 0.05) !important;
  border-color: rgba(255, 255, 255, 0.2) !important;
  color: rgba(255, 255, 255, 0.8) !important;
}

:deep(input[readonly]) {
  background-color: rgba(255, 255, 255, 0.05) !important;
  border-color: rgba(255, 255, 255, 0.2) !important;
  color: rgba(255, 255, 255, 0.8) !important;
  cursor: default !important;
}
</style>
