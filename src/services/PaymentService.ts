import { booking_invoices, bookings } from "../database/index";
import axios from "axios";
import { redis } from "../config/redis"; // Import Redis client
import { EmailService } from "./EmailService"; // Import EmailService
import { db, dbPool } from "../config/database";
import { eq } from "drizzle-orm";
import { generateBookingReference } from "../utils/referenceGenerator";

import type { InitPaystackPaymentDTO } from "../dtos/InitPaystackPaymentDTO";
import { injectable } from "inversify";

@injectable()
export class PaymentService {
  /**
   * Verifies a Flutterwave payment and updates booking invoice and bookings.
   * @param transactionId - The Flutterwave transaction ID
   * @param expectedAmount - The expected amount to be paid
   * @param invoiceReference - The booking invoice reference
   * @param userEmail - The customer's email
   * @returns {Promise<{ success: boolean; message: string }>} Result
   */
  async verifyFlutterwavePayment({
    transactionId,
    expectedAmount,
    invoiceReference,
    userEmail,
  }: {
    transactionId: string;
    expectedAmount: number;
    invoiceReference: string;
    userEmail: string;
  }): Promise<{ success: boolean; message: string }> {
    try {
      // Call Flutterwave verify endpoint
      const FLW_SECRET_KEY = process.env.FLW_SECRET_KEY;
      const url = `https://api.flutterwave.com/v3/transactions/${transactionId}/verify`;
      const response = await axios.get(url, {
        headers: {
          Authorization: `Bearer ${FLW_SECRET_KEY}`,
        },
      });
      const data = response.data.data;
      // Check status, amount, and reference
      if (
        response.data.status !== "success" ||
        data.status !== "successful" ||
        Number(data.amount) !== Number(expectedAmount) ||
        data.tx_ref !== invoiceReference
      ) {
        return {
          success: false,
          message: "Payment verification failed or details mismatch.",
        };
      }
      // Update booking_invoice status and amount_paid
      await dbPool
        .update(booking_invoices)
        .set({ status: "paid" })
        .where(eq(booking_invoices.reference, invoiceReference));
      // Update all associated bookings
      await dbPool
        .update(bookings)
        .set({ status: "paid", amount_paid: expectedAmount })
        .where(eq(bookings.booking_invoice_id, data.id));
      // Send confirmation email
      await EmailService.sendTemplateEmail({
        to: userEmail,
        subject: "Booking Payment Confirmation",
        templateName: "payment-confirmation", // You need to create this template
        context: {
          amount: expectedAmount,
          reference: invoiceReference,
        },
      });
      return {
        success: true,
        message: "Payment verified and booking updated.",
      };
    } catch (error) {
      return { success: false, message: "Payment verification failed." };
    }
  }
  /**
   * Initializes a Paystack payment transaction.
   * @param params - InitPaystackPaymentDTO
   * @returns {Promise<{ success: boolean; data?: any; message?: string }>}
   */
  async initializePaystackPayment(
    params: InitPaystackPaymentDTO
  ): Promise<{ success: boolean; data?: any; message?: string }> {
    try {
      console.log("[PaymentService] Initializing Paystack payment", params);
      const PAYSTACK_SECRET_KEY = process.env.PAYSTACK_SECRET_KEY;
      const url = "https://api.paystack.co/transaction/initialize";

      const reference = params.reference || (await generateBookingReference());
      const response = await axios.post(
        url,
        {
          email: params.email,
          amount: params.amount * 100,
          reference,
          callback_url: params.callback_url,
        },
        {
          headers: {
            Authorization: `Bearer ${PAYSTACK_SECRET_KEY}`,
            "Content-Type": "application/json",
          },
        }
      );
      console.log("[PaymentService] Paystack init response", response.data);
      if (response.data.status) {
        return { success: true, data: response.data.data };
      } else {
        return { success: false, message: response.data.message };
      }
    } catch (error: any) {
      console.error(
        "[PaymentService] Paystack init error",
        error?.response?.data || error
      );
      return {
        success: false,
        message:
          error?.response?.data?.message || "Paystack initialization failed.",
      };
    }
  }

  /**
   * Verifies a Paystack payment transaction.
   * @param reference - The transaction reference
   * @returns {Promise<{ success: boolean; data?: any; message?: string }>}
   */
  async verifyPaystackPayment(
    reference: string
  ): Promise<{ success: boolean; data?: any; message?: string }> {
    try {
      console.log(
        `[PaymentService] Verifying Paystack payment: reference=${reference}`
      );
      const PAYSTACK_SECRET_KEY = process.env.PAYSTACK_SECRET_KEY;
      const url = `https://api.paystack.co/transaction/verify/${reference}`;
      const response = await axios.get(url, {
        headers: {
          Authorization: `Bearer ${PAYSTACK_SECRET_KEY}`,
        },
      });
      console.log(
        "[PaymentService] Paystack verify response",
        JSON.stringify(response.data, null, 2)
      );
      const paystackData = response.data.data;
      const gatewayMessage =
        paystackData?.gateway_response || response.data.message;
      const status = paystackData?.status;
      if (response.data.status && status === "success") {
        console.log(
          "[PaymentService] Paystack verification SUCCESS",
          paystackData
        );
        return {
          success: true,
          data: paystackData,
          message: gatewayMessage,
        };
      } else {
        console.warn(
          "[PaymentService] Paystack verification FAILURE",
          response.data
        );
        return {
          success: false,
          message: gatewayMessage,
          data: paystackData,
        };
      }
    } catch (error: any) {
      console.error(
        "[PaymentService] Paystack verify error",
        error?.response?.data || error
      );
      return {
        success: false,
        message:
          error?.response?.data?.message || "Paystack verification failed.",
      };
    }
  }
}
