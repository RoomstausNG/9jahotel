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
    return ["*"];
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
    return true; // Allow all origins
  },
};
