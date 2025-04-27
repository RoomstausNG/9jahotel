import { Hono } from "hono";
import { container } from "../config/container";
import { BookingController } from "../controllers/BookingController";
import { authMiddleware } from "../middlewares/authMiddleware";
import { zValidator } from "@hono/zod-validator";
import { CreateBookingDto } from "../dtos/BookingDTO";

const bookingController = container.get(BookingController);
const bookingRoutes = new Hono();

// Create a new booking
bookingRoutes.post(
  "/",
  zValidator("json", CreateBookingDto),
  (c) => bookingController.createBooking(c)
);

// Get user's bookings (requires authentication)
bookingRoutes.get(
  "/my-bookings",
  authMiddleware,
  (c) => bookingController.getUserBookings(c)
);

// Get booking details by invoice ID
bookingRoutes.get(
  "/:invoiceId",
  authMiddleware,
  (c) => bookingController.getBookingDetails(c)
);

export default bookingRoutes;