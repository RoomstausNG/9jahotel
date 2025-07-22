import { Hono } from "hono";
import { container } from "../container";
import { BookingController } from "../controllers/BookingController";
import { zValidator } from "../validators/validator-wrapper";
import { authMiddleware } from "../middlewares/auth-middleware";
import { CreateAuthBookingDto, CreateBookingDto } from "../dtos/BookingDTO";
import { GetBookingByIdOrReferenceDTO } from "../dtos/BookingDTO";
import { GetBookingsByStatusDTO } from "../dtos/BookingDTO";
import hotelRoutes from "./hotel-routes";

const bookingRoutes = new Hono();
const bookingController = container.get(BookingController);

// create a new booking
bookingRoutes.post("/", zValidator("json", CreateBookingDto), (c) =>
  bookingController.createBooking(c)
);

// POST /verify-payment
bookingRoutes.post("/verify-payment", (c) =>
  bookingController.verifyPayment(c)
);

// GET /:idOrReference - fetch booking by ID or reference
bookingRoutes.get(
  "/:reference",
  authMiddleware,
  zValidator("param", GetBookingByIdOrReferenceDTO),
  (c) => bookingController.getBookingByIdOrReference(c)
);

bookingRoutes.get(
  "/",
  authMiddleware,
  zValidator("query", GetBookingsByStatusDTO),
  (c) => bookingController.getBookingsByStatus(c)
);

bookingRoutes.post(
  "/auth",
  authMiddleware,
  zValidator("json", CreateAuthBookingDto),
  (c) => bookingController.createBookingForAuthUser(c)
);

hotelRoutes.post("/coupon/check", (c) => bookingController.checkCoupon(c));

export default bookingRoutes;
