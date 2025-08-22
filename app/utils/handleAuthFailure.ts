// Clears all localStorage and sessionStorage, logs out user, and redirects to home
export function handleAuthFailure() {
  if (typeof window !== "undefined") {
    localStorage.clear();
    sessionStorage.clear();
  }
  // If you use a store for user, clear it here
  try {
    const userStore =
      typeof useUserStore === "function" ? useUserStore() : null;
    if (userStore && typeof userStore.logout === "function") {
      userStore.logout();
    }
  } catch {}
  // Use navigateTo if available, else fallback to window.location
  if (typeof navigateTo === "function") {
    navigateTo("/");
  } else if (typeof window !== "undefined") {
    window.location.href = "/";
  }
}
