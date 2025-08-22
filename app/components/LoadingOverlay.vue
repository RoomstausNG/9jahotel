<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
  >
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-2xl mx-4 text-center"
    >
      <!-- Loading Icon -->
      <div class="w-16 h-16 mx-auto mb-4 relative">
        <div
          class="absolute inset-0 border-4 border-primary-200 rounded-full"
        ></div>
        <div
          class="absolute inset-0 border-4 border-primary-500 rounded-full animate-spin border-t-transparent"
        ></div>
      </div>

      <!-- Loading Text -->
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
        {{ title || "Loading..." }}
      </h3>

      <p v-if="description" class="text-sm text-gray-600 dark:text-gray-400">
        {{ description }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  title?: string;
  description?: string;
}

defineProps<Props>();
</script>
