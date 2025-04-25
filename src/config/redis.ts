import { Redis } from "ioredis";
import { env } from "./env";

export const redis = new Redis({
  host: env("REDIS_HOST", "127.0.0.1"),
  port: Number(env("REDIS_PORT", "6379")),
  password: env("REDIS_PASSWORD", ""),
});
