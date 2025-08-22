import { ref } from "vue";
import type { Hotel } from "~/types/hotel";

export function useHotel(hotelCode: string) {
  const hotel = ref<Hotel | null>(null);
  const loading = ref(false);
  const error = ref<Error | null>(null);

  const config = useRuntimeConfig();
  const baseURL = config.public.apiBase || "/api";

  const fetchHotel = async () => {
    loading.value = true;
    error.value = null;
    try {
      // In a real app, you would fetch from an API
      // await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate network delay

      const response = await $fetch<{ data: Hotel }>(
        `${baseURL}/hotels/${hotelCode}`
      );
      hotel.value = response.data;
    } catch (e: unknown) {
      error.value = e as Error;
    } finally {
      loading.value = false;
    }
  };

  return {
    hotel,
    loading,
    error,
    fetchHotel,
  };
}
