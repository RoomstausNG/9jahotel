<template>
  <NuxtLoadingIndicator />
  <UApp :toaster="{ position: 'top-right' }">
    <!-- Splash Screen -->
    <SplashScreen
      v-if="showSplash"
      :duration="3000"
      :min-duration="2000"
      @complete="hideSplash"
    />

    <!-- Main App -->
    <div v-show="isAppReady">
      <NuxtLayout>
        <NuxtPage />
      </NuxtLayout>
    </div>
  </UApp>
</template>

<script setup>
// import { toaster } from "#build/ui"; // Unused import removed

const { showSplash, isAppReady, hideSplash, autoHideSplash } =
  useSplashScreen();

// Failsafe: Auto-hide splash after 5 seconds
autoHideSplash(5000);

// Optionally, you can add app initialization logic here
onMounted(async () => {
  try {
    // Add any app initialization logic here
    // For example: loading user preferences, checking auth, etc.
    await new Promise((resolve) => setTimeout(resolve, 1000)); // Simulate loading

    // The splash screen will hide automatically when its animation completes
  } catch (error) {
    console.error("App initialization error:", error);
    // Hide splash even if there's an error
    hideSplash();
  }
});
</script>
