import { Redis } from "ioredis";
import { env } from "./env";

// Configure Redis client with environment variables and defaults
export const redis = new Redis({
  host: env("REDIS_HOST", "127.0.0.1"), // Use "redis" as the host name for Docker
  port: Number(env("REDIS_PORT", "6379")), // Default to 6379 if not specified
  password: env("REDIS_PASSWORD", ""), // Use the Redis password if required, otherwise leave it empty
  db: Number(env("REDIS_DB", "0")), // Default to DB 0
  reconnectOnError: (err) => {
    // This will help reconnect if there are transient errors
    if (err.message.includes("READONLY")) {
      console.log("Redis is in read-only mode, attempting to reconnect.");
      return true; // Automatically reconnect
    }
    return false;
  },
  retryStrategy: (times) => {
    const delay = Math.min(times * 50, 2000);
    console.log(
      `Retrying Redis connection in ${delay}ms... (attempt ${times})`
    );
    return delay;
  },
  maxRetriesPerRequest: 5,
});

// Handle Redis connection errors
redis.on("error", (err) => {
  console.error("Redis connection error:", err);
});

// Handle successful connection
redis.on("connect", () => {
  console.log("Connected to Redis server!");
});
