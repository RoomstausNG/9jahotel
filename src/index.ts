// import { LocationController } from "./controllers/LocationController";
// import { errorHandler } from "./middlewares/error-handlers";
// // global env variable
// import "dotenv/config";
// import "./config/env";

// import { Hono } from "hono";
// import { logger } from "hono/logger";
// import { prettyJSON } from "hono/pretty-json";
// import { PORT } from "./config/constants";
// import userRoutes from "./routes/user-routes";
// import authRoutes from "./routes/auth-routes"; // <-- 1. Import authRoutes
// import { ApiResponse } from "./utils/ApiResponse";
// import hotelRoutes from "./routes/hotel-routes";
// import { redis } from "./config/redis";

// const app = new Hono().basePath("/api");

// // use Global errors handlers
// app.use(errorHandler);
// app.use(prettyJSON());
// app.use(logger());

// app.get("/hello", (c) => {
//   return c.json({ message: "Welcome to 9jaHotel.com!" }, 200);
// });

// // location and hotels endpoint
// app.route("/", hotelRoutes);

// app.get("/clear-caches", (c) => {
//   const done = redis.flushall();
//   return c.json({ done }, 200);
// });

// // user endpoint
// app.route("/users", userRoutes);

// // auth endpoint
// app.route("/", authRoutes);

// // Not found errorHandler
// app.all("*", (c) => {
//   return ApiResponse.failure("Resource Not Found", 404);
// });

// export default {
//   fetch: app.fetch,
//   PORT,
// };

// console.log(`Server running on port ${PORT}`);

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
import authRoutes from "./routes/auth-routes";
import { ApiResponse } from "./utils/ApiResponse";
import hotelRoutes from "./routes/hotel-routes";
import { getRedisConnection, redis } from "./config/redis";
import { string } from "zod";

// Declare a global Redis ready state
declare global {
  var redisReady: boolean;
}
global.redisReady = false;

// Create app instance
const app = new Hono().basePath("/api");

// use Global errors handlers
app.use(errorHandler);
app.use(prettyJSON());
app.use(logger());

// Add a health check endpoint
app.get("/health", async (c) => {
  try {
    // Check if Redis is ready
    if (!global.redisReady) {
      return c.json(
        { status: "initializing", message: "Redis connection not ready" },
        503
      );
    }

    // Verify Redis with a ping
    const pingResult = await redis.ping();
    if (pingResult !== "PONG") {
      return c.json(
        { status: "error", message: "Redis connection unhealthy" },
        503
      );
    }

    return c.json({ status: "healthy", message: "System is operational" }, 200);
  } catch (error) {
    return c.json(
      {
        status: "error",
        message: "Health check failed",
        error: error instanceof Error ? error.message : String(error),
      },
      503
    );
  }
});

app.get("/hello", (c) => {
  return c.json({ message: "Welcome to 9jaHotel.com!" }, 200);
});

// location and hotels endpoint
app.route("/", hotelRoutes);

app.get("/clear-caches", async (c) => {
  try {
    const done = await redis.flushall();
    return c.json({ done }, 200);
  } catch (error) {
    return c.json(
      {
        error: error instanceof Error ? error.message : String(error),
      },
      500
    );
  }
});

// user endpoint
app.route("/users", userRoutes);

// auth endpoint
app.route("/", authRoutes);

// Not found errorHandler
app.all("*", (c) => {
  return ApiResponse.failure("Resource Not Found", 404);
});

// Define server interface
interface Server {
  fetch: typeof app.fetch;
  PORT: number;
}

// Initialize the server asynchronously
const initServer = async (): Promise<Server> => {
  try {
    console.log("Initializing server...");
    console.log("Waiting for Redis connection...");

    // Wait for Redis to connect
    const redisClient = await getRedisConnection();
    (exports as any).redis = redisClient;
    global.redisReady = true;

    console.log(
      `Redis connected and ready! Server can now handle requests on port ${PORT}`
    );

    return {
      fetch: app.fetch,
      PORT as Number,
    };
  } catch (error) {
    console.error(
      "Failed to start server:",
      error instanceof Error ? error.message : String(error)
    );
    process.exit(1);
  }
};

// Export the server initialization function
export default await initServer();

console.log(`Server running on port ${PORT}`);