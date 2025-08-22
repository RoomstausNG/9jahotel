import { useUserStore } from "@/stores/user";

export default defineNuxtPlugin(async () => {
  // This plugin runs during app initialization
  const userStore = useUserStore();
  await userStore.loadFromStorage();

  // Client-side initialization
  console.log("9jahotel app initializing...");

  // Prevent flash of unstyled content
  if (typeof document !== "undefined") {
    document.documentElement.style.visibility = "visible";
  }
});
