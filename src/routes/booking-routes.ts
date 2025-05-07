import { Hono } from "hono";
import { container } from "../container";
import { BookingController } from "../controllers/BookingController";
import { AuthService, PaymentService } from "../services/AuthService";

import { zValidator } from "../validators/validator-wrapper";

import { authMiddleware } from "../middlewares/auth-middleware";

import { CreateBookingDto } from "../dtos/BookingDTO";

const bookingRoutes = new Hono();
const bookingController = container.get(BookingController);

// create a new booking
bookingRoutes.post("/", zValidator("json", CreateBookingDto), (c) =>
  bookingController.createBooking(c)
);

// POST /verify-payment
bookingRoutes.post("/verify-payment", async (c) => {
  try {
    const { transactionId, invoiceReference, userEmail } = await c.req.json();
    // Fetch the booking invoice to get the expected amount
    const [invoice] = await db
      .select()
      .from(booking_invoices)
      .where(eq(booking_invoices.reference, invoiceReference))
      .limit(1);
    if (!invoice) {
      return c.json(
        { success: false, message: "Booking invoice not found." },
        404
      );
    }
    const expectedAmount = invoice.amount;
    // Call the payment verification service
    const result = await PaymentService.verifyFlutterwavePayment({
      transactionId,
      expectedAmount,
      invoiceReference,
      userEmail,
    });
    if (result.success) {
      return c.json({ success: true, message: result.message });
    } else {
      return c.json({ success: false, message: result.message }, 400);
    }
  } catch (error) {
    return c.json({ success: false, message: "Internal server error." }, 500);
  }
});

export default bookingRoutes;
