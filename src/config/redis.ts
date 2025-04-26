// import { Redis } from "ioredis";
// import { env } from "./env";

// // Configure Redis client with environment variables and defaults
// export const redis = new Redis({
//   host: env("REDIS_HOST", "redis"), // Use "redis" as the host name for Docker
//   port: Number(env("REDIS_PORT", "6379")), // Default to 6379 if not specified
//   password: env("REDIS_PASSWORD", ""), // Use the Redis password if required, otherwise leave it empty
//   db: Number(env("REDIS_DB", "0")), // Default to DB 0
//   retryStrategy(times) {
//     const delay = Math.min(times * 100, 2000); // retry delay
//     return delay;
//   },
//   reconnectOnError: (err) => {
//     // This will help reconnect if there are transient errors
//     if (err.message.includes("READONLY")) {
//       console.log("Redis is in read-only mode, attempting to reconnect.");
//       return true; // Automatically reconnect
//     }
//     return false;
//   },
// });

// // Handle Redis connection errors
// redis.on("error", (err) => {
//   console.error("Redis connection error:", err);
// });

// // Handle successful connection
// redis.on("connect", () => {
//   console.log("Connected to Redis server!");
// });

import { Redis } from "ioredis";
import { env } from "./env";

let redisClient: Redis | null = null;

export const getRedisConnection = (): Promise<Redis> => {
  console.log("Establishing Redis connection...");

  return new Promise((resolve, reject) => {
    const redis = new Redis({
      host: env("REDIS_HOST", "redis"),
      port: Number(env("REDIS_PORT", "6379")),
      password: env("REDIS_PASSWORD", ""),
      db: Number(env("REDIS_DB", "0")),
      retryStrategy(times: number) {
        const maxRetryTime = 30000; // Max 30 seconds between retries
        const delay = Math.min(times * 1000, maxRetryTime);
        console.log(
          `Redis connection attempt ${times} failed. Retrying in ${delay}ms...`
        );
        return delay;
      },
      reconnectOnError: (err: Error) => {
        if (err.message.includes("READONLY")) {
          console.log("Redis is in read-only mode, attempting to reconnect.");
          return true;
        }
        return false;
      },
      connectTimeout: 60000, // 60 second connection timeout
    });

    const connectionTimeout = setTimeout(() => {
      reject(new Error("Redis connection timed out after 60 seconds"));
      redis.disconnect();
    }, 60000);

    redis.on("error", (err: Error) => {
      console.error("Redis connection error:", err);
    });

    redis.on("connect", () => {
      console.log("Redis connected!");
    });

    redis.on("ready", () => {
      clearTimeout(connectionTimeout);
      console.log("Redis is ready to accept commands!");
      redisClient = redis;
      resolve(redis);
    });
  });
};

// Export a placeholder for modules that need to import redis before it's ready
export let redis: Redis;

// Initialize with a getter that throws if accessed before initialization
Object.defineProperty(exports, "redis", {
  get: function () {
    if (!redisClient) {
      throw new Error("Redis client accessed before initialization");
    }
    return redisClient;
  },
  set: function (client: Redis) {
    redisClient = client;
  },
});
