<template>
  <button
    :class="[
      isFavorite ? 'text-red-500' : 'text-gray-600',
      'focus:outline-none',
    ]"
    class="cursor-pointer"
    :aria-label="isFavorite ? 'Remove from favorites' : 'Add to favorites'"
    @click="toggleFavorite"
  >
    <UIcon name="material-symbols:favorite" class="w-6 h-6" />
  </button>
</template>

<script setup lang="ts">
const props = defineProps({
  hotelId: {
    type: Number,
    required: true,
  },
  initialFavorite: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:is-favorite"]);
const isFavorite = ref(props.initialFavorite);
const toast = useToast();
const config = useRuntimeConfig();
const baseURL = config.public.apiBase || "/api";

const toggleFavorite = async () => {
  const token = localStorage.getItem("token");
  if (!token) {
    toast.add({
      title: "Login Required",
      description: "You must login to add hotels to favorites.",
      color: "warning",
      icon: "material-symbols:lock-outline",
    });
    return;
  }
  try {
    const endpoint = `${baseURL}/hotels/toggle-favourite`;
    const body = { hotelId: props.hotelId };
    console.log("FavoriteToggle API call:", endpoint, body);
    await $fetch(endpoint, {
      method: "POST",
      body,
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });
    isFavorite.value = !isFavorite.value;
    emit("update:is-favorite", isFavorite.value);
    toast.add({
      icon: isFavorite.value
        ? "material-symbols:check-circle-outline-rounded"
        : "material-symbols:bookmark-check",
      title: isFavorite.value
        ? "Hotel added to favorites"
        : "Hotel removed from favorites",
      color: isFavorite.value ? "success" : "info",
    });
  } catch {
    toast.add({
      title: "Favorite Error",
      description: "Failed to update favorite status.",
      color: "error",
    });
  }
};
</script>
