import { LocationController } from "./controllers/LocationController";
import { errorHandler } from "./middlewares/error-handlers";
// global env variable
import "dotenv/config";
import "./config/env";

import { Hono } from "hono";
import { logger } from "hono/logger";
import { prettyJSON } from "hono/pretty-json";
import { PORT } from "./config/constants";
import userRoutes from "./routes/user-routes";
import authRoutes from "./routes/auth-routes"; // <-- 1. Import authRoutes
import { ApiResponse } from "./utils/ApiResponse";
import hotelRoutes from "./routes/hotel-routes";
import { redis } from "./config/redis";

const app = new Hono().basePath("/api");

// use Global errors handlers
app.use(errorHandler);
app.use(prettyJSON());
app.use(logger());

app.get("/hello", (c) => {
  return c.json({ message: "Welcome to 9jaHotel.com!" }, 200);
});

// location and hotels endpoint
app.route("/", hotelRoutes);

app.get("/clear-caches", (c) => {
  const done = redis.flushall();
  return c.json({ done }, 200);
});

// user endpoint
app.route("/users", userRoutes);

// auth endpoint
app.route("/", authRoutes);

// Not found errorHandler
app.all("*", (c) => {
  return ApiResponse.failure("Resource Not Found", 404);
});

export default {
  fetch: app.fetch,
  PORT,
};

console.log(`Server running on port ${PORT}`);
