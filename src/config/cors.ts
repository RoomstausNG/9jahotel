import { env } from "./env";

/**
 * CORS configuration for the application
 * Supports both web frontend and mobile app requests
 */
export const corsConfig = {
  /**
   * Allowed origins for CORS requests
   * Includes frontend URL, mobile app schemes, and development URLs
   */
  getAllowedOrigins: () => {
    const baseOrigins = [
      env("FRONTEND_URL", "https://example.com"),
      "http://localhost:4000", // Local development
      "http://localhost:3000", // Local development
      "http://localhost:3001", // Local development (alternative port)
      "http://localhost:3002", // Local development (alternative port)
      "http://localhost:3006", // Local development (alternative port)
      "http://127.0.0.1:3000", // Local development (IP)
      "http://127.0.0.1:3001", // Local development (IP alternative port)
      "capacitor://localhost", // Capacitor mobile apps
      "ionic://localhost", // Ionic mobile apps
      "tauri://localhost", // Tauri desktop apps
      "https://tauri.localhost", // Tauri desktop apps (secure)
      "http://localhost", // Mobile development
      "https://localhost", // Mobile development with HTTPS
    ];

    // Add custom mobile app origins from environment if provided
    const customOrigins = env("MOBILE_APP_ORIGINS", "") as string;
    if (customOrigins) {
      baseOrigins.push(
        ...customOrigins.split(",").map((origin: string) => origin.trim())
      );
    }

    return baseOrigins;
  },

  /**
   * Allowed HTTP methods for CORS requests
   */
  allowedMethods: ["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],

  /**
   * Allowed headers for CORS requests
   * Includes common headers needed for mobile apps and web frontends
   */

  allowedHeaders: [
    "Content-Type",
    "Authorization",
    "X-Requested-With",
    "Accept",
    "Origin",
    "Cache-Control",
    "X-File-Name",
    "X-Device-Type",
    "X-App-Version",
  ],

  /**
   * Headers to expose to the client
   */
  exposedHeaders: ["Content-Length", "X-Kuma-Revision"],

  /**
   * Whether to include credentials in CORS requests
   */
  credentials: true,

  /**
   * Cache duration for preflight requests (in seconds)
   */
  maxAge: 600, // 10 minutes

  /**
   * Check if an origin is allowed
   * @param origin - The origin to check
   * @returns boolean - Whether the origin is allowed
   */
  isOriginAllowed: (origin: string | undefined): boolean => {
    if (!origin) return true; // Allow requests with no origin (mobile apps, Postman, etc.)

    // In development, allow all origins
    if (process.env.NODE_ENV === "development") {
      return true;
    }

    const allowedOrigins = corsConfig.getAllowedOrigins();

    // Check exact match
    if (allowedOrigins.includes(origin)) {
      return true;
    }

    // Check for mobile app schemes
    if (origin.startsWith("capacitor://") || origin.startsWith("ionic://")) {
      return true;
    }

    // Check for Tauri app schemes
    if (origin.startsWith("tauri://") || origin.startsWith("https://tauri.")) {
      return true;
    }

    // Check for file:// scheme (mobile apps)
    if (origin.startsWith("file://")) {
      return true;
    }

    return false;
  },
};
