export default defineNuxtRouteMiddleware(async () => {
  const userStore = useUserStore();
  if (!userStore.token) {
    // Ensure the store has loaded
    await userStore.loadFromStorage();
    if (!userStore.token) {
      return navigateTo("/auth/login");
    }
  }
});
