import { injectable, inject } from "inversify";
import { db } from "../config/database";
import { generateBookingReference } from "../utils/referenceGenerator";
import { HotelService } from "./HotelService";
import { ourCommission } from "../utils/DateAmountUtils";
import { booking_invoices } from "../database/index";

@injectable()
export class BookingService {
  constructor() {}

  async createBooking(bookingData: any, userId?: number) {
    let hotel: any;
    try {
      // Fetch hotel name from the first roomId
      let hotelName = undefined;

      const room = await HotelService.getRoomDetailsById(
        bookingData.roomIds[0]
      );
      hotel = room.hotel;
      const settlement = ourCommission(
        bookingData.total,
        hotel.commsionPercent
      );

      const reference = await generateBookingReference(hotel.name);
      const result = await db.transaction(async (tx: typeof db) => {
        // Create booking invoice first
        const bookingInvoiceResult = await tx.insert(booking_invoices).values({
          reference,
          amount: bookingData.total,
          commission_amount: settlement.commissionAmount,
          hotel_payable: settlement.hotelPayable,
          status: "unpaid",
          customer_id: userId,
          hotel_id: hotel.id,
          checked_in: bookingData.checkIn,
          checked_out: bookingData.checkOut,
          type: bookingData.type,
          created_at: new Date(),
          updated_at: new Date(),
        });

        const invoiceId = bookingInvoiceResult[0].insertId;

        // Create individual bookings for each room
        const bookings = await Promise.all(
          bookingData.roomIds.map(async (roomId: number) => {
            return await tx.insert("bookings").values({
              code: reference + "-" + roomId,
              room_id: roomId,
              type: "room",
              booking_invoice_id: invoiceId,
              hotel_id: hotel.id,
              customer_id: userId,
              bookable_id: roomId,
              due: bookingData.total / bookingData.roomIds.length,
              discount: this.getBookingCommissionPercent(bookingData, hotel),
              amount_paid: 0,
              via: "9jahotel",
              bookable_type: "App\\Models\\Room",
              check_in: bookingData.checkIn,
              check_out: bookingData.checkOut,
              status: "unpaid",
              // created_at: new Date(),
              // updated_at: new Date(),
            });
          })
        );

        // Return combined result
        return {
          invoice: bookingInvoiceResult[0],
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

  /**
   * Get the commission percent based on booking type.
   * @param bookingData - The booking data object (must have type)
   * @param hotel - The hotel object (must have nonRefundablePercentage and onlinePercentage)
   * @returns The commission percent (number)
   */
  getBookingCommissionPercent(
    bookingData: { type: string },
    hotel: any
  ): number {
    if (bookingData.type === "Nonrefundable") {
      return hotel.nonRefundablePercentage;
    }
    return hotel.onlinePercentage;
  }
}
