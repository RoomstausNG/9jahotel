import { redis } from "../config/redis";

const REDIS_COUNTER_KEY = "booking_reference_counter";
const REFERENCE_PREFIX = "RE9A";
const PADDING_LENGTH = 8;

/**
 * Generates a unique booking reference number (e.g., RE9A00000001).
 * Uses Redis to atomically increment a counter for the numeric part.
 * @returns {Promise<string>} The generated booking reference.
 * @throws {Error} If Redis operation fails.
 */
export async function generateBookingReference(): Promise<string> {
  try {
    // Atomically increment the counter in Redis.
    // If the key doesn't exist, Redis initializes it to 0 before incrementing.
    const currentCount = await redis.incr(REDIS_COUNTER_KEY);

    // Pad the number with leading zeros to match the desired length.
    const paddedCount = String(currentCount).padStart(PADDING_LENGTH, "0");

    // Combine the prefix and the padded count.
    const reference = `${REFERENCE_PREFIX}${paddedCount}`;

    console.log(`Generated booking reference: ${reference}`); // Optional logging
    return reference;
  } catch (error) {
    console.error("Redis error generating booking reference:", error);
    throw new Error(
      "Failed to generate booking reference due to a Redis issue."
    );
  }
}

/**
 * Gets the current value of the booking reference counter from Redis without incrementing it.
 * @returns {Promise<number>} The current counter value, or 0 if not set.
 */
export async function getCurrentBookingReferenceCounter(): Promise<number> {
  try {
    const value = await redis.get(REDIS_COUNTER_KEY);
    return value ? parseInt(value, 10) : 0;
  } catch (error) {
    console.error("Redis error getting current counter:", error);
    throw new Error("Failed to get booking reference counter.");
  }
}

/**
 * Resets the booking reference counter in Redis back to 0.
 * Use this function with caution, as it will restart the numbering sequence.
 * @returns {Promise<void>}
 */
export async function resetBookingReferenceCounter(): Promise<void> {
  try {
    await redis.set(REDIS_COUNTER_KEY, "0");
    console.log("Booking reference counter reset to 0.");
  } catch (error) {
    console.error("Redis error resetting counter:", error);
    throw new Error("Failed to reset booking reference counter.");
  }
}
