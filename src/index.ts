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
import { redis } from "./config/redis";
import { hostname } from "os";
import bookingRoutes from "./routes/booking-routes";
import { EmailService } from "./services/EmailService";

const app = new Hono().basePath("/api");

// use Global errors handlers
app.use(errorHandler);
app.use(prettyJSON());
app.use(logger());

app.get("/", (c) => {
  return c.json(
    {
      version: "v0",
      message:
        "Welcome to 9jaHotel.com! The apis is working this is a test version to test the apis changed",
      description:
        "The latest version for production server will be v1.wakalor.com, therefore pls keep it in mind",
    },
    200
  );
});

// location and hotels endpoint
app.route("/bookings", bookingRoutes);

app.route("/", hotelRoutes);

app.get("/clear-caches", (c) => {
  const done = redis.flushall();
  return ApiResponse.success(null, "All saved cache cleared successfully");
});

// user endpoint
app.route("/users", userRoutes);

// auth endpoint
app.route("/", authRoutes);

// heath check
app.get("/health", (c) => {
  return c.text("OK", 200); // Respond with status 200 for health checks
});

const emailService = new EmailService();

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
      "Test email sent to fedumsoftware@gmail.com"
    );
  } catch (error: any) {
    return ApiResponse.failure(error.message, 500);
  }
});

// Not found errorHandler
app.all("*", (c) => {
  return ApiResponse.failure("Resource Not Found", 404);
});

export default {
  fetch: app.fetch,
  PORT,
  hostname: "0.0.0.0",
};

console.log(`Server running on port ${PORT}`);
