import { redis } from "../config/redis";

const REDIS_COUNTER_KEY = "booking_reference_counter";
const REFERENCE_PREFIX = "9JA";
const PADDING_LENGTH = 5;

/**
 * Generates a unique booking reference number (e.g., RE9A0501AH00000001).
 * Uses Redis to atomically increment a counter for the numeric part.
 * @param hotelName Optional hotel name to generate prefix (e.g., "ADele Hotel" -> "AH")
 * @returns {Promise<string>} The generated booking reference.
 * @throws {Error} If Redis operation fails.
 */
export async function generateBookingReference(
  hotelName?: string
): Promise<string> {
  try {
    // Get month as two digits
    const now = new Date();
    const month = String(now.getMonth() + 1).padStart(2, "0");

    // Generate hotel prefix (first letter of each word, max 3, uppercase)
    let hotelPrefix = "";
    if (hotelName) {
      hotelPrefix = hotelName
        .split(" ")
        .map((w) => w[0])
        .join("")
        .toUpperCase()
        .slice(0, 3);
    }

    // Use a dynamic Redis key per hotel prefix and month
    const dynamicCounterKey = `booking_reference_counter:${month}:${hotelPrefix}`;
    // Atomically increment the counter for this prefix/month
    const currentCount = await redis.incr(dynamicCounterKey);
    const paddedCount = String(currentCount).padStart(PADDING_LENGTH, "0");

    // Generate a short random alphanumeric string for uniqueness
    const randomStr = Math.random().toString(36).substring(2, 8).toUpperCase();

    // Combine the prefix, month, hotelPrefix, padded count, and random string.
    const reference = `${REFERENCE_PREFIX}${month}${hotelPrefix}${paddedCount}${randomStr}`;

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
