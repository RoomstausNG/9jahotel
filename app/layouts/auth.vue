<template>
  <div
    class="flex flex-col max-w-lg h-screen bg-[url('https://res.cloudinary.com/dcm83lp3b/image/upload/v1752508514/slide_image_upd9ht.webp')] relative mx-auto lg: bg-cover"
  >
    <!-- Fixed Header -->
    <header
      class="fixed top-0 left-0 right-0 lg:left-auto lg:right-auto lg: lg:mx-auto z-50 border-b border-gray-200 h-[70px] shadow-sm"
    >
      <div
        class="flex items-center justify-between px-4 h-full bg-primary dark:bg-primary text-white"
      >
        <!-- Greeting -->
        <div class="flex-1">
          <div class="flex items-center">
            <span class="font-medium text-sm text-white">
              Good {{ timeOfDay }}, {{ userName }}
            </span>
          </div>
        </div>

        <!-- Right Icons -->
        <div class="flex items-center">
          <div class="flex items-center gap-2">
            <!-- Notification Icon -->
            <!-- <div class="relative p-1 cursor-pointer">
              <UIcon name="i-lucide-bell" class="text-gray-700 h-8" />
              <div
                class="absolute top-0 right-0 bg-red-500 text-white text-xs font-semibold w-4 h-4 rounded-full flex items-center justify-center"
              >
                1
              </div>
            </div> -->

            <!-- Profile Avatar -->
            <UPopover>
              <UAvatar
                :src="user?.avatar"
                :alt="user?.name || userName"
                :text="userInitial"
                size="md"
                class="cursor-pointer"
              />

              <template #content>
                <div class="p-4 w-64">
                  <!-- User Info -->
                  <div
                    class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-200"
                  >
                    <div class="flex-1">
                      <h3 class="font-semibold text-gray-900">
                        {{ user?.firstName || userName }}
                      </h3>
                      <p class="text-sm text-black">
                        {{ user?.email }}
                      </p>
                    </div>
                  </div>

                  <!-- User Reference Code -->
                  <div
                    v-if="user?.referralCode"
                    class="mb-4 p-3 bg-primary rounded-lg"
                  >
                    <div class="flex items-center justify-between">
                      <div>
                        <p
                          class="text-xs text-primary-400 uppercase tracking-wide"
                        >
                          Referral Code
                        </p>
                        <p class="font-mono text-sm font-medium text-gray-900">
                          {{ user?.referralCode || "STG001" }}
                        </p>
                      </div>
                      <UButton
                        variant="ghost"
                        class="text-white"
                        size="xs"
                        icon="i-lucide-copy"
                        @click="copyReferralCode"
                      />
                    </div>
                  </div>

                  <!-- Menu Items -->
                  <div class="space-y-1">
                    <UButton
                      variant="ghost"
                      class="w-full justify-start"
                      icon="i-lucide-user"
                      @click="navigateToProfile"
                    >
                      View Profile
                    </UButton>

                    <UButton
                      variant="ghost"
                      class="w-full justify-start text-red-600 hover:text-red-700"
                      icon="i-lucide-log-out"
                      @click="handleLogout"
                    >
                      Logout
                    </UButton>
                  </div>
                </div>
              </template>
            </UPopover>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <main
      class="flex-1 pt-[70px] pb-20 overflow-y-auto backdrop-brightness-90 bg-gradient-to-br from-primary/20 to-primary-900/30 backdrop-blur"
    >
      <div class="h-full w-full py-4 relative">
        <slot />
      </div>
    </main>

    <!-- Fixed Footer Navigation -->
    <footer
      class="fixed bg-primary dark:bg-primary bottom-0 left-0 right-0 lg:left-auto lg:right-auto lg:mx-auto z-50 border-t border-gray-200 h-20 shadow-sm"
    >
      <nav class="flex items-center justify-around h-full px-2 py-2">
        <NuxtLink
          to="/home"
          class="flex flex-col items-center justify-center gap-1 px-2 py-2 transition-all duration-200 rounded-lg min-w-[60px] hover:bg-white/10"
          :class="
            route.path === '/home'
              ? 'text-white bg-primary/20'
              : 'text-white/70 hover:text-white'
          "
        >
          <Icon name="i-lucide-home" class="text-xl" />
          <span class="text-xs font-medium">Home</span>
        </NuxtLink>

        <NuxtLink
          to="/favourites"
          class="flex flex-col items-center justify-center gap-1 px-2 py-2 transition-all duration-200 rounded-lg min-w-[60px] hover:bg-white/10"
          :class="
            route.path === '/favourites'
              ? 'text-white bg-primary/20'
              : 'text-white/70 hover:text-white'
          "
        >
          <Icon name="i-lucide-heart" class="text-xl" />
          <span class="text-xs font-medium">Favourite</span>
        </NuxtLink>

        <NuxtLink
          to="/bookings"
          class="flex flex-col items-center justify-center gap-1 px-2 py-2 transition-all duration-200 rounded-lg min-w-[60px] hover:bg-white/10"
          :class="
            route.path === '/bookings'
              ? 'text-white bg-primary/20'
              : 'text-white/70 hover:text-white'
          "
        >
          <Icon name="i-lucide-calendar" class="text-xl" />
          <span class="text-xs font-medium">Bookings</span>
        </NuxtLink>

        <NuxtLink
          to="/profile"
          class="flex flex-col items-center justify-center gap-1 px-2 py-2 transition-all duration-200 rounded-lg min-w-[60px] hover:bg-white/10"
          :class="
            route.path === '/profile'
              ? 'text-white bg-primary/20'
              : 'text-white/70 hover:text-white'
          "
        >
          <Icon name="i-lucide-user" class="text-xl" />
          <span class="text-xs font-medium">Profile</span>
        </NuxtLink>
      </nav>
    </footer>
  </div>
</template>

<script setup>
const route = useRoute();
const router = useRouter();
const toast = useToast();

// Get user data from your auth store/composable
const userStore = useUserStore();
const user = computed(() => userStore.user);

// Get user name and initial
const userName = computed(
  () => user.value?.firstName || user.value?.name || "Sarah"
);
const userInitial = computed(() => userName.value.charAt(0).toUpperCase());

// Get time of day for greeting
const timeOfDay = computed(() => {
  const hour = new Date().getHours();
  if (hour < 12) return "morning";
  if (hour < 17) return "afternoon";
  return "evening";
});

// Navigation methods
const navigateToProfile = () => {
  router.push("/profile");
};

const _navigateToSettings = () => {
  router.push("/settings");
};

// Copy referral code to clipboard
const copyReferralCode = async () => {
  try {
    const referralCode = user.value?.referralCode || "STG001";
    await navigator.clipboard.writeText(referralCode);
    toast.add({
      title: "Referral code copied!",
      description: `${referralCode} has been copied to your clipboard.`,
      icon: "i-lucide-check-circle",
      color: "green",
    });
  } catch (error) {
    console.error("Failed to copy referral code:", error);
    toast.add({
      title: "Failed to copy",
      description: "Could not copy referral code to clipboard.",
      icon: "i-lucide-x-circle",
      color: "red",
    });
  }
};

// Logout functionality
const handleLogout = async () => {
  try {
    await userStore.logout();
    toast.add({
      title: "Logged out successfully",
      description: "You have been logged out of your account.",
      icon: "i-lucide-check-circle",
      color: "green",
    });
    router.push("/home");
  } catch (error) {
    console.error("Logout failed:", error);
    toast.add({
      title: "Logout failed",
      description: "Something went wrong while logging out.",
      icon: "i-lucide-x-circle",
      color: "red",
    });
  }
};
</script>

<style scoped>
/* Safe Area Support for iOS devices */
@supports (padding: env(safe-area-inset-top)) {
  header {
    padding-top: env(safe-area-inset-top);
    height: calc(70px + env(safe-area-inset-top));
  }

  main {
    padding-top: calc(70px + env(safe-area-inset-top));
    padding-bottom: calc(80px + env(safe-area-inset-bottom));
  }

  footer {
    padding-bottom: env(safe-area-inset-bottom);
    height: calc(80px + env(safe-area-inset-bottom));
  }
}

/* Ensure fixed elements respect container width on larger screens */
@media (min-width: 1024px) {
  header,
  footer {
    left: 50%;
    transform: translateX(-50%);
    max-width: 384px; /* lg: */
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .bg-gray-50 {
    background-color: #0f172a;
  }

  .text-gray-900 {
    color: #f8fafc;
  }

  .text-gray-500 {
    color: #94a3b8;
  }

  .hover\:text-blue-500:hover {
    color: #60a5fa;
  }

  .hover\:bg-blue-50:hover {
    background-color: #1e3a8a;
  }
}
</style>
