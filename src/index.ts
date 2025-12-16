import { LocationController } from "./controllers/LocationController";
import { errorHandler } from "./middlewares/error-handlers";
// global env variable
import "dotenv/config";
import "./config/env";

import { Hono } from "hono";
import { cors } from "hono/cors";
import { logger } from "hono/logger";
import { prettyJSON } from "hono/pretty-json";
import { PORT } from "./config/constants";
import userRoutes from "./routes/user-routes";
import authRoutes from "./routes/auth-routes";
import { ApiResponse } from "./utils/ApiResponse";
import hotelRoutes from "./routes/hotel-routes";
import { redis } from "./config/redis";
import { hostname } from "os";
import bookingRoutes from "./routes/booking-routes";
import { customerRoutes } from "./routes/customer-routes";
import { EmailService } from "./services/EmailService";
import { corsConfig } from "./config/cors";
import { db } from "./config/database";
import { sql } from "drizzle-orm";

// Import email worker to start it
import "./jobs/email-processor";

import paymentRoutes from "./routes/payment-routes";

const app = new Hono().basePath("/api");

// CORS configuration for mobile app support
app.use(
  "*",
  cors({
    origin: (origin, c) => {
      // For development, allow all origins
      if (process.env.NODE_ENV === "development") {
        return origin || "*";
      }

      // Use the CORS configuration helper
      if (corsConfig.isOriginAllowed(origin)) {
        return origin || "*";
      }

      // Default to allowing the request for mobile apps
      return origin || "*";
    },
    allowMethods: corsConfig.allowedMethods,
    allowHeaders: corsConfig.allowedHeaders,
    exposeHeaders: corsConfig.exposedHeaders,
    credentials: corsConfig.credentials,
    maxAge: corsConfig.maxAge,
  }),
);

// use Global errors handlers
app.use(errorHandler);
app.use(prettyJSON());
app.use(logger());

app.get("/", (c) => {
  return c.json(
    {
      version: "v0",
      message:
        "Welcome to 9jahotel.com! The apis is working this is a test version to test the apis changed",
      description:
        "The latest version for production server will be v1.wakalor.com, therefore pls keep it in mind",
    },
    200,
  );
});

// location and hotels endpoint
app.route("/bookings", bookingRoutes);

app.route("/", hotelRoutes);

// customer endpoint (public, not guarded by auth)
app.route("/customers", customerRoutes);

app.get("/clear-caches", (c) => {
  const done = redis.flushall();
  return ApiResponse.success(null, "All saved cache cleared successfully");
});

// user endpoint
app.route("/users", userRoutes);

// auth endpoint
app.route("/", authRoutes);

// heath check
app.get("/health", async (c) => {
  try {
    // Test database connection
    await db.execute(sql`SELECT 1`);
    return c.text("OK", 200); // Respond with status 200 for health checks
  } catch (error) {
    console.error("Database connection failed:", error);
    return c.text("Database connection failed", 500);
  }
});

const emailService = new EmailService();

app.route("/", paymentRoutes);
app.get("/test-email", async (c) => {
  try {
    await emailService.sendTemplateEmail({
      to: "fedumsoftware@gmail.com",
      subject: "Test Email from 9jaHotel API",
      templateName: "password-reset", // Using existing template
      context: {
        resetUrl:
          env("FRONTEND_URL", "https://example.com") + "/reset-password",
        expiryHours: 1,
      },
    });
    return ApiResponse.success(
      null,
      "Test email sent to fedumsoftware@gmail.com",
    );
  } catch (error: any) {
    return ApiResponse.failure(error.message, 500);
  }
});

// Not found errorHandler
app.notFound((c) => {
  return ApiResponse.failure("Resource/Endpoint Not Found", 404);
});

// sockets events
import "./routes/socket-event";
import { engine, websocket } from "./routes/socket-event";
export default {
  PORT,
  idleTimeout: 30, // must be greater than ping interval
  hostname: "0.0.0.0",
  fetch(req: any, server: any) {
    const url = new URL(req.url);
    if (url.pathname === "/socket.io/") {
      return engine.handleRequest(req, server);
    }
    return app.fetch(req, server);
  },

  websocket,
};

console.log(`Server running on port ${PORT}`);
