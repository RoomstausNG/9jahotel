import { Hono } from "hono";
import { container } from "../container";
import { PaymentController } from "../controllers/PaymentController";
import { zValidator } from "../validators/validator-wrapper";
import { InitPaystackPaymentSchema } from "../validators/init-paystack-payment";

const paymentRoutes = new Hono();

const paymentController = container.get(PaymentController);

// POST /api/payment/paystack/init
paymentRoutes.post(
  "/payment/paystack/init",
  zValidator("json", InitPaystackPaymentSchema),
  (c) => paymentController.initializePaystackPayment(c)
);

// GET /api/payment/paystack/verify/:reference
paymentRoutes.get("/payment/paystack/verify/:reference", (c) =>
  paymentController.verifyPaystackPayment(c)
);

export default paymentRoutes;
