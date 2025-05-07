import { booking_invoices, bookings } from "../database/index";
import axios from "axios";
import { redis } from "../config/redis"; // Import Redis client
import { EmailService } from "./EmailService"; // Import EmailService
import { db, dbPool } from "../config/database";
import { eq } from "drizzle-orm";

export class PaymentService {
  /**
   * Verifies a Flutterwave payment and updates booking invoice and bookings.
   * @param transactionId - The Flutterwave transaction ID
   * @param expectedAmount - The expected amount to be paid
   * @param invoiceReference - The booking invoice reference
   * @param userEmail - The customer's email
   * @returns {Promise<{ success: boolean; message: string }>} Result
   */
  static async verifyFlutterwavePayment({
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
}
