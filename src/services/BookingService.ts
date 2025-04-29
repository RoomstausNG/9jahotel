import { injectable, inject } from "inversify";
import { db } from "../config/database";
import { CreateBookingDtoType } from "../dtos/BookingDTO";
import { HotelService } from "../services/HotelService";
import { getDaysBetweenDates } from "../utils/DateAmountUtils";

@injectable()
export class BookingService {
  constructor() {}

  async createBooking(bookingData: CreateBookingDtoType, userId?: number) {
    try {
      const result = await db.transaction(async (tx: typeof db) => {
        // Create booking invoice first
        const invoiceId = await generateBookingReference();
        const bookingInvoice = await tx
          .insert("booking_invoices")
          .values({
            id: invoiceId,
            customer_id: userId || null,
            customer_name: `${bookingData.firstname} ${bookingData.lastname}`,
            customer_email: bookingData.email,
            customer_phone: bookingData.phone,
            total_amount: bookingData.total,
            status: "unpaid", // Default status
            check_in: bookingData.checkIn,
            check_out: bookingData.checkOut,
            created_at: new Date(),
            updated_at: new Date(),
          })
          .returning();

        // Create individual bookings for each room
        const bookings = await Promise.all(
          bookingData.roomIds.map(async (roomId) => {
            return await tx
              .insert("bookings")
              .values({
                invoice_id: invoiceId,
                room_id: roomId,
                type: bookingData.type,
                check_in: bookingData.checkIn,
                check_out: bookingData.checkOut,
                status: "unpaid", // Default status
                created_at: new Date(),
                updated_at: new Date(),
              })
              .returning();
          })
        );

        // Return combined result
        return {
          invoice: bookingInvoice[0],
          bookings: bookings.map((booking) => booking[0]),
        };
      });

      return result;
    } catch (error: any) {
      console.error("Error creating booking:", error);
      throw new Error(error.message || "Failed to create booking");
    }
  }

  async getBookingsByUser(
    userId: number,
    page: number = 1,
    perPage: number = 10
  ) {
    try {
      const offset = (page - 1) * perPage;

      // Get booking invoices for the user
      const invoices = await db.query.booking_invoices.findMany({
        where: (invoice, { eq }) => eq(invoice.customer_id, userId),
        orderBy: (invoice, { desc }) => [desc(invoice.created_at)],
        limit: perPage,
        offset,
        with: {
          bookings: {
            with: {
              room: true,
            },
          },
        },
      });

      // Count total invoices for pagination
      const totalCount = await db
        .select({ count: count() })
        .from(booking_invoices)
        .where(eq(booking_invoices.customer_id, userId));

      return {
        data: invoices,
        pagination: {
          total: totalCount[0].count,
          page,
          perPage,
          totalPages: Math.ceil(totalCount[0].count / perPage),
        },
      };
    } catch (error: any) {
      console.error("Error fetching bookings:", error);
      throw new Error(error.message || "Failed to fetch bookings");
    }
  }

  async getBookingDetails(invoiceId: string) {
    try {
      const invoice = await db.query.booking_invoices.findFirst({
        where: (invoice, { eq }) => eq(invoice.id, invoiceId),
        with: {
          bookings: {
            with: {
              room: true,
            },
          },
        },
      });

      if (!invoice) {
        throw new Error("Booking not found");
      }

      return invoice;
    } catch (error: any) {
      console.error("Error fetching booking details:", error);
      throw new Error(error.message || "Failed to fetch booking details");
    }
  }
}
