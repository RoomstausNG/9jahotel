import { Hono } from "hono";
import { container } from "../container";
import { BookingController } from "../controllers/BookingController";

import { zValidator } from "../validators/validator-wrapper";

import { authMiddleware } from "../middlewares/auth-middleware";

import { CreateBookingDto } from "../dtos/BookingDTO";

const bookingRoutes = new Hono();
const bookingController = container.get(BookingController);

// create a new booking
bookingRoutes.post("/", zValidator("json", CreateBookingDto), (c) =>
  bookingController.createBooking(c)
);

export default bookingRoutes;
