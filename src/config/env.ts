import "dotenv/config";

/**
 * Environment variable helper
 * Provides type-safe access to environment variables with default values and validation
 */
export function env<T>(
  key: string,
  defaultValue?: T,
  options?: { required?: boolean }
): T {
  // Try to get value from process.env or import.meta.env (for Vite environments)
  const value = process?.env?.[key] as unknown as T;
  
  if (value === undefined || value === null || value === "") {
    if (options?.required) {
      throw new Error(`Required environment variable '${key}' is not defined.`);
    }
    return defaultValue as T;
  }
  
  return value;
}

// Attach `env` function to the global object
globalThis.env = env;
