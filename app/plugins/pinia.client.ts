import { defineNuxtPlugin } from "#app";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";

export default defineNuxtPlugin((nuxtApp) => {
  // Pinia is auto-created by Nuxt 3, just add the plugin if available
  if (
    nuxtApp.$pinia &&
    typeof nuxtApp.$pinia === "object" &&
    "use" in nuxtApp.$pinia
  ) {
    // @ts-expect-error: Nuxt types may not include $pinia
    nuxtApp.$pinia.use(piniaPluginPersistedstate);
  }
});
