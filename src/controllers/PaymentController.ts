import { InitPaystackPaymentSchema } from "../validators/init-paystack-payment";
import { PaymentService } from "../services/PaymentService";
import { inject, injectable } from "inversify";
import { ApiResponse } from "../utils/ApiResponse";

@injectable()
export class PaymentController {
  constructor(@inject(PaymentService) private paymentService: PaymentService) {}

  async initializePaystackPayment(c: any) {
    try {
      const body = await c.req.json();
      const parse = InitPaystackPaymentSchema.safeParse(body);
      if (!parse.success) {
        return ApiResponse.failure("Invalid request", 400, parse.error.errors);
      }
      const result = await this.paymentService.initializePaystackPayment(
        parse.data
      );
      if (result.success) {
        return ApiResponse.success(
          result.data,
          "Payment initialized successfully"
        );
      } else {
        return ApiResponse.failure(
          result.message || "Payment initialization failed",
          400
        );
      }
    } catch (error: any) {
      return ApiResponse.failure(error.message || "Internal server error", 500);
    }
  }

  async verifyPaystackPayment(c: any) {
    try {
      const reference = c.req.param("reference");
      if (!reference) {
        return ApiResponse.failure("Reference is required", 400);
      }
      const result = await this.paymentService.verifyPaystackPayment(reference);
      if (result.success) {
        return ApiResponse.success(
          result.data,
          "Payment verified successfully"
        );
      } else {
        return ApiResponse.failure(
          result.message || "Payment verification failed",
          400
        );
      }
    } catch (error: any) {
      return ApiResponse.failure(error.message || "Internal server error", 500);
    }
  }
}
