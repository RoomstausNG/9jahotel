<template>
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto text-center">
      <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-6">
        Welcome to 9jahotel
      </h1>

      <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">
        Your premium hotel booking experience starts here.
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <UCard>
          <template #header>
            <h3 class="text-lg font-semibold">Splash Screen Demo</h3>
          </template>

          <p class="text-gray-600 dark:text-gray-400 mb-4">
            Test the splash screen by reloading the page or clicking the button
            below.
          </p>

          <UButton @click="showSplashDemo" color="primary" block>
            Show Splash Screen
          </UButton>
        </UCard>

        <UCard>
          <template #header>
            <h3 class="text-lg font-semibold">Loading Overlay Demo</h3>
          </template>

          <p class="text-gray-600 dark:text-gray-400 mb-4">
            Demonstrate loading overlays for async operations.
          </p>

          <UButton
            @click="showLoadingDemo"
            color="primary"
            variant="outline"
            block
          >
            Show Loading Overlay
          </UButton>
        </UCard>
      </div>

      <!-- Features Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
        <div class="text-center p-6">
          <Icon
            name="i-lucide-hotel"
            class="text-4xl text-primary-500 mx-auto mb-4"
          />
          <h3 class="text-lg font-semibold mb-2">Premium Hotels</h3>
          <p class="text-gray-600 dark:text-gray-400">
            Discover luxury accommodations across Nigeria
          </p>
        </div>

        <div class="text-center p-6">
          <Icon
            name="i-lucide-star"
            class="text-4xl text-primary-500 mx-auto mb-4"
          />
          <h3 class="text-lg font-semibold mb-2">Top Rated</h3>
          <p class="text-gray-600 dark:text-gray-400">
            Highly rated hotels with excellent reviews
          </p>
        </div>

        <div class="text-center p-6">
          <Icon
            name="i-lucide-shield-check"
            class="text-4xl text-primary-500 mx-auto mb-4"
          />
          <h3 class="text-lg font-semibold mb-2">Secure Booking</h3>
          <p class="text-gray-600 dark:text-gray-400">
            Safe and secure booking with instant confirmation
          </p>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <LoadingOverlay
      v-if="showLoading"
      :title="loadingTitle"
      :description="loadingDescription"
    />
  </div>
</template>

<script setup>
const { resetSplash } = useSplashScreen();

const showLoading = ref(false);
const loadingTitle = ref("Loading...");
const loadingDescription = ref("Please wait while we process your request.");

const showSplashDemo = () => {
  // Reset and show splash screen
  resetSplash();
  // Reload the page to trigger splash screen
  window.location.reload();
};

const showLoadingDemo = async () => {
  showLoading.value = true;
  loadingTitle.value = "Processing Request";
  loadingDescription.value = "Searching for available hotels...";

  // Simulate API call
  await new Promise((resolve) => setTimeout(resolve, 2000));

  loadingTitle.value = "Almost Done";
  loadingDescription.value = "Finalizing your results...";

  await new Promise((resolve) => setTimeout(resolve, 1000));

  showLoading.value = false;
};

// SEO meta
useHead({
  title: "9jahotel - Premium Hotel Booking",
  meta: [
    {
      name: "description",
      content:
        "Discover and book luxury hotels across Nigeria with 9jahotel. Premium accommodations, secure booking, and excellent service.",
    },
  ],
});
</script>
