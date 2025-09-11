// Clears all localStorage and sessionStorage, logs out user, and redirects to login
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
    navigateTo("/auth/login");
  } else if (typeof window !== "undefined") {
    window.location.href = "/auth/login";
  }
}

// Helper for handling API response errors with 401 handling
export function handleApiError(error: any) {
  // Check for 401 Unauthorized error
  if (error.response?.status === 401 || error.statusCode === 401) {
    console.error("Authentication error detected:", error);
    handleAuthFailure();
    return;
  }

  // Return the error for further handling
  return error;
}
