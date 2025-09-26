import { handleAuthFailure } from "@/utils/handleAuthFailure";
import type { FetchError } from "ofetch";

export default defineNuxtPlugin((nuxtApp) => {
  // Add global hook for API errors
  nuxtApp.hook("app:error", (error) => {
    // Check if it's a fetch error with 401 status
    if ((error as FetchError).statusCode === 401) {
      handleAuthFailure();
    }
  });

  // Add interceptor for fetch requests
  const { public: config } = useRuntimeConfig();
  const fetchOptions = {
    onResponseError({ response }) {
      if (response.status === 401) {
        handleAuthFailure();
      }
    },
  };

  // Provide a helper for authenticated requests
  return {
    provide: {
      authFetch: (request: string, options: any = {}) => {
        const token = localStorage.getItem("token");

        // Merge headers with authorization
        const headers = {
          ...(options.headers || {}),
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
        };

        // Add our global error handling
        return $fetch(request, {
          ...options,
          headers,
          ...fetchOptions,
        });
      },
    },
  };
});
