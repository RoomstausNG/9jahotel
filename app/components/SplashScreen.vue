<template>
  <div
    v-if="isVisible"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-gradient-to-br from-primary-950 to-primary-900 transition-opacity duration-500"
    :class="{ 'opacity-0': fadeOut }"
  >
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute inset-0 bg-dots bg-repeat" />
    </div>

    <!-- Main Content -->
    <div class="relative text-center text-white">
      <!-- Logo/Icon -->
      <div class="mb-8">
        <div
          class="w-28 h-auto mx-auto mb-4 rounded-2xl backdrop-blur-sm flex items-center justify-center transform transition-transform duration-700"
          :class="{ 'scale-125': animateIcon }"
        >
          <NuxtImg
            src="https://res.cloudinary.com/dcm83lp3b/image/upload/v1752510054/9jahotel_sm8jgq.svg"
            alt="9ja Hotel Logo"
            class="w-20 h-20 object-contain rounded-sm"
          />
        </div>
      </div>

      <!-- Subtitle -->
      <p
        class="text-lg md:text-xl text-white/90 mb-8 transform transition-transform duration-700 delay-500"
        :class="{
          'translate-y-0 opacity-100': animateText,
          'translate-y-8 opacity-0': !animateText,
        }"
      >
        Your Gateway to Comfort
      </p>

      <!-- Loading Animation -->
      <div
        class="relative transform transition-transform duration-700 delay-700"
        :class="{
          'translate-y-0 opacity-100': animateLoader,
          'translate-y-8 opacity-0': !animateLoader,
        }"
      >
        <!-- Progress Bar -->
        <div
          class="w-48 h-1 bg-white/20 rounded-full mx-auto mb-4 overflow-hidden"
        >
          <div
            class="h-full bg-white rounded-full transition-all duration-1000 ease-out"
            :style="{ width: `${progress}%` }"
          />
        </div>

        <!-- Loading Text -->
        <p class="text-sm text-white/80 animate-pulse">
          {{ loadingText }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { NuxtImg } from "#components";
import { ref, onMounted, onUnmounted } from "vue";

interface Props {
  duration?: number;
  minDuration?: number;
}

const props = withDefaults(defineProps<Props>(), {
  duration: 3000,
  minDuration: 2000,
});

const emit = defineEmits<{
  complete: [];
}>();

const isVisible = ref(true);
const fadeOut = ref(false);
const animateIcon = ref(false);
const animateText = ref(false);
const animateLoader = ref(false);
const progress = ref(0);
const loadingText = ref("Loading...");

const loadingTexts = [
  "Loading...",
  "Preparing your experience...",
  "Almost ready...",
  "Welcome to 9jahotel!",
];

let progressInterval: NodeJS.Timeout;
let textInterval: NodeJS.Timeout;

onMounted(() => {
  // Start animations sequentially
  setTimeout(() => {
    animateIcon.value = true;
  }, 100);

  setTimeout(() => {
    animateText.value = true;
  }, 300);

  setTimeout(() => {
    animateLoader.value = true;
    startProgress();
  }, 700);
});

const startProgress = () => {
  let currentProgress = 0;
  let textIndex = 0;

  // Update progress bar
  progressInterval = setInterval(() => {
    currentProgress += Math.random() * 15 + 5;
    progress.value = Math.min(currentProgress, 100);

    if (progress.value >= 100) {
      clearInterval(progressInterval);
      setTimeout(() => {
        hideSplash();
      }, 500);
    }
  }, 200);

  // Update loading text
  textInterval = setInterval(() => {
    textIndex = (textIndex + 1) % loadingTexts.length;
    loadingText.value = loadingTexts[textIndex];
  }, 800);

  // Ensure minimum duration
  setTimeout(() => {
    if (progress.value < 100) {
      progress.value = 100;
      clearInterval(progressInterval);
      setTimeout(() => {
        hideSplash();
      }, 500);
    }
  }, props.minDuration);
};

const hideSplash = () => {
  clearInterval(progressInterval);
  clearInterval(textInterval);

  fadeOut.value = true;

  setTimeout(() => {
    isVisible.value = false;
    emit("complete");
  }, 500);
};

onUnmounted(() => {
  clearInterval(progressInterval);
  clearInterval(textInterval);
});
</script>

<style scoped>
/* Ensure splash screen covers everything */
.fixed {
  position: fixed !important;
}

/* Smooth animations */
.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.transition-opacity {
  transition-property: opacity;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
