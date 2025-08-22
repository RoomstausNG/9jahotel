export default defineNuxtRouteMiddleware(async () => {
  const userStore = useUserStore();
  // Ensure the store has loaded
  await userStore.loadFromStorage();
  if (userStore.token) {
    return navigateTo("/home");
  }
});
