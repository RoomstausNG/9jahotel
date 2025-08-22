<template>
  <div class="relative">
    <UButton
      size="xl"
      icon="i-lucide-users"
      :label="guestButtonLabel"
      variant="outline"
      color="gray"
      class="w-full justify-start text-left"
      @click="toggleDropdown"
    />

    <!-- Dropdown overlay -->
    <div
      v-if="isGuestDropdownOpen"
      class="fixed inset-0 z-40 bg-black/20"
      @click="closeDropdown"
    />

    <!-- Dropdown content -->
    <div
      v-if="isGuestDropdownOpen"
      class="absolute z-50 mt-2 w-full max-w-xs rounded-lg shadow-xl bg-white border border-gray-200 p-4 space-y-4"
    >
      <!-- Adults Counter -->
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-gray-700">Adults</span>
        <div class="flex items-center gap-2">
          <UButton
            icon="i-lucide-minus"
            size="xs"
            variant="outline"
            :disabled="localGuestConfig.adults <= 1"
            @click.stop="decrementAdults"
          />
          <span class="w-8 text-center font-medium text-sm">{{
            localGuestConfig.adults
          }}</span>
          <UButton
            icon="i-lucide-plus"
            size="xs"
            variant="outline"
            :disabled="localGuestConfig.adults >= 5"
            @click.stop="incrementAdults"
          />
        </div>
      </div>

      <!-- Rooms Counter (if you want to add it) -->
      <!-- <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-gray-700">Rooms</span>
        <div class="flex items-center gap-2">
          <UButton
            icon="i-lucide-minus"
            size="xs"
            variant="outline"
            :disabled="localGuestConfig.rooms <= 1"
            @click.stop="decrementRooms"
          />
          <span class="w-8 text-center font-medium text-sm">{{
            localGuestConfig.rooms
          }}</span>
          <UButton
            icon="i-lucide-plus"
            size="xs"
            variant="outline"
            :disabled="localGuestConfig.rooms >= 5"
            @click.stop="incrementRooms"
          />
        </div>
      </div> -->

      <UButton
        label="Done"
        color="primary"
        size="sm"
        class="w-full block"
        @click="closeDropdown"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ adults: 1, rooms: 1 }),
  },
});

const emit = defineEmits(["update:modelValue"]);

const isGuestDropdownOpen = ref(false);

// Create a local reactive copy
const localGuestConfig = ref({ ...props.modelValue });

// Watch for external changes to modelValue
watch(
  () => props.modelValue,
  (newValue) => {
    localGuestConfig.value = { ...newValue };
  },
  { deep: true }
);

// Watch for local changes and emit them
watch(
  localGuestConfig,
  (newValue) => {
    emit("update:modelValue", { ...newValue });
  },
  { deep: true }
);

const guestButtonLabel = computed(() => {
  const adults = localGuestConfig.value.adults;
  // const rooms = localGuestConfig.value.rooms;
  return `${adults} Adult${adults > 1 ? "s" : ""}`;
});

const toggleDropdown = () => {
  isGuestDropdownOpen.value = !isGuestDropdownOpen.value;
};

const closeDropdown = () => {
  isGuestDropdownOpen.value = false;
};

const incrementAdults = () => {
  if (localGuestConfig.value.adults < 5) {
    localGuestConfig.value.adults++;
  }
};

const decrementAdults = () => {
  if (localGuestConfig.value.adults > 1) {
    localGuestConfig.value.adults--;
  }
};

const incrementRooms = () => {
  if (localGuestConfig.value.rooms < 5) {
    localGuestConfig.value.rooms++;
  }
};

const decrementRooms = () => {
  if (localGuestConfig.value.rooms > 1) {
    localGuestConfig.value.rooms--;
  }
};
</script>
