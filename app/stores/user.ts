export interface User {
  id: number;
  firstName: string;
  lastName: string;
  name?: string; // Full name or display name
  referralCode?: string;
  referenceCode?: string; // Alternative naming for reference code
  email: string;
  phone: string;
  parentId: number | null;
  avatar: string | null;
  address: string | null;
  latitude: number | null;
  longitude: number | null;
  tokenVersion: number;
}

export const useUserStore = defineStore("user", {
  state: () => ({
    user: null as null | User,
    token: null as null | string,
  }),
  getters: {
    isAuthenticated: (state) => !!(state.user && state.token),
  },
  actions: {
    async setUser(user: User) {
      this.user = user;
      localStorage.setItem("user", JSON.stringify(user));
    },
    async setToken(token: string) {
      this.token = token;
      localStorage.setItem("token", token);
    },
    async loadFromStorage() {
      const user = localStorage.getItem("user");
      const token = localStorage.getItem("token");
      this.user = user ? (JSON.parse(user) as User) : null;
      this.token = token || null;
    },
    async logout() {
      try {
        // Call logout API endpoint if token exists
        if (this.token) {
          const config = useRuntimeConfig();
          const apiBaseUrl = config.public.apiBase;

          await $fetch(`${apiBaseUrl}/auth/logout`, {
            method: "POST",
            headers: {
              Authorization: `Bearer ${this.token}`,
            },
          });
        }
      } catch (error) {
        // Continue with local logout even if API call fails
        console.error("Server logout failed:", error);
      } finally {
        // Always clear local storage and state
        this.user = null;
        this.token = null;
        localStorage.removeItem("user");
        localStorage.removeItem("token");

        // Clear any other auth-related data
        localStorage.removeItem("authState");
        localStorage.removeItem("refreshToken");
      }
    },
  },
});
