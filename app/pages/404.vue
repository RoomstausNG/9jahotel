<template>
  <div
    class="h-screen w-full flex flex-col items-center justify-center bg-black/90 text-white"
  >
    <div class="flex flex-col items-center">
      <div class="mb-8">
        <Icon
          name="i-lucide-alert-triangle"
          class="w-16 h-16 text-primary-500 animate-bounce"
        />
      </div>
      <h1 class="text-5xl font-bold mb-4">404</h1>
      <h2 class="text-2xl font-semibold mb-2">Page Not Found</h2>
      <p class="text-gray-300 mb-6">
        Sorry, the page you are looking for does not exist or has been moved.
      </p>
      <UButton color="primary" variant="solid" size="lg" @click="goHome">
        Go to Home
      </UButton>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from "vue-router";
const router = useRouter();
const goHome = () => {
  router.push("/");
};
</script>

<style scoped>
.bg-black\/90 {
  background-color: rgba(0, 0, 0, 0.9);
}
.animate-bounce {
  animation: bounce 1s infinite;
}
@keyframes bounce {
  0%,
  100% {
    transform: translateY(-15%);
    animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
  }
  50% {
    transform: none;
    animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
  }
}
</style>
