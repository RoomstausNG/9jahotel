import { Redis } from "ioredis";
import { env } from "./env";

// Configure Redis client
export const redis = new Redis({
  host: env("REDIS_HOST", "redis"), // Use "redis" for Docker Compose
  port: Number(env("REDIS_PORT", "6379")),
  password: env("REDIS_PASSWORD", ""), // Leave empty if no auth
  db: Number(env("REDIS_DB", "0")),

  // Connection behaviors
  connectTimeout: 10000, // 10 seconds timeout
  maxRetriesPerRequest: 5, // Fail fast if Redis becomes unresponsive
  enableReadyCheck: true, // Ensure connection is actually ready
  autoResubscribe: true, // Resubscribe after reconnect (default true)
  autoResendUnfulfilledCommands: true, // Resend commands after reconnect
  enableOfflineQueue: false, // Fail commands immediately when offline (optional, safer)

  // Retry strategy for reconnections
  retryStrategy: (times) => {
    const delay = Math.min(times * 100, 3000); // Exponential backoff, up to 3s
    console.warn(`Retrying Redis connection in ${delay}ms (attempt ${times})`);
    return delay;
  },

  // When encountering certain errors
  reconnectOnError: (err) => {
    const targetErrors = ["READONLY", "ECONNRESET", "ETIMEDOUT"];
    if (targetErrors.some((e) => err.message.includes(e))) {
      console.warn(`Redis reconnecting after error: ${err.message}`);
      return true;
    }
    return false;
  },
});

// Redis events
redis.on("connect", () => {
  console.log("✅ Connected to Redis server!");
});

redis.on("ready", () => {
  console.log("🚀 Redis connection is fully ready!");
});

redis.on("error", (err) => {
  console.error("🔴 Redis connection error:", err.message);
});

redis.on("close", () => {
  console.warn("⚠️ Redis connection closed.");
});

redis.on("reconnecting", () => {
  console.warn("♻️  Attempting to reconnect to Redis...");
});
