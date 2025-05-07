import { booking_invoices, bookings } from "./../database/migrations/schema";
import { injectable, inject } from "inversify";
import { db, dbPool } from "../config/database";
import { generateBookingReference } from "../utils/referenceGenerator";
import { HotelService } from "./HotelService";
import { ourCommission } from "../utils/DateAmountUtils";
import { count, eq } from "drizzle-orm";
import { EmailService } from "./EmailService";
import { BookingResource } from "../resources/BookingResource";
import { redis } from "../config/redis";
import { or } from "drizzle-orm";

@injectable()
export class BookingService {
  constructor(@inject(EmailService) private emailService: EmailService) {}

  async createBooking(bookingData: any, userId?: number) {
    let hotel: any;
    try {
      // Fetch hotel name from the first roomId
      const room = await HotelService.getRoomDetailsById(
        bookingData.roomIds[0]
      );
      hotel = room.hotel;
      // Ensure commissionPercent and total are valid numbers
      const commissionPercent = Number(hotel.commissionPercent) || 0;
      const bookingTotal = Number(bookingData.total) || 0;
      const settlement = ourCommission(bookingTotal, commissionPercent);

      // Fallback to 0 if commission calculation fails
      const commissionAmount = isNaN(settlement.commissionAmount)
        ? 0
        : settlement.commissionAmount;
      const hotelPayable = isNaN(settlement.hotelPayable)
        ? 0
        : settlement.hotelPayable;

      const reference = await generateBookingReference(hotel.name);
      const result = await db.transaction(async (tx: typeof db) => {
        // Create booking invoice first
        const bookingInvoiceResult = await tx.insert(booking_invoices).values({
          reference,
          amount: bookingTotal,
          commission_amount: commissionAmount,
          hotel_payable: hotelPayable,
          status: "unpaid",
          customer_id: userId,
          hotel_id: hotel.id,
          checked_in: bookingData.checkIn,
          checked_out: bookingData.checkOut,
          type: bookingData.type,
          created_at: new Date(),
          updated_at: new Date(),
        });
        
        const invoiceId =
          bookingInvoiceResult[0]?.insertId || bookingInvoiceResult[0]?.id;
        if (!invoiceId) {
          throw new Error("Failed to retrieve booking invoice ID");
        }

        // Create individual bookings for each room
        await Promise.all(
          bookingData.roomIds.map(async (roomId: number) => {
            await tx.insert(bookings).values({
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
              checked_in: bookingData.checkIn,
              checked_out: bookingData.checkOut,
              status: "unpaid",
              created_at: new Date(),
              updated_at: new Date(),
            });
          })
        );

        // Return the booking invoice resource
        return await this.getBookingByIdOrReference(invoiceId);
      });

      // Send confirmation email after successful booking
      try {
        const customerName =
          bookingData.firstname && bookingData.lastname
            ? `${bookingData.firstname} ${bookingData.lastname}`
            : bookingData.customerName || "Guest";
        const bookingAmount = bookingData.total;
        const numberOfRooms = bookingData.roomIds.length;
        const roomCategory = room.category || room.type || "Room";
        const bookingReference = reference;
        const checkIn = bookingData.checkIn;
        const checkOut = bookingData.checkOut;
        const guestEmail = bookingData.email;

        await this.emailService.sendTemplateEmail({
          to: guestEmail,
          subject: "Your Reservation is Confirmed",
          templateName: "guestBooking",
          context: {
            hotelName: hotel.name,
            customerName,
            bookingAmount,
            numberOfRooms,
            roomCategory,
            bookingReference,
            checkIn,
            checkOut,
            bookedBy: customerName,
            totalRooms: numberOfRooms,
          },
        });
      } catch (emailError) {
        console.error("Failed to send booking confirmation email:", emailError);
      }

      return result;
    } catch (error: any) {
      console.error("Error creating booking:", error);
      throw new Error(error.message || "Failed to create booking");
    }
  }

  getBookingCommissionPercent(bookingData: any, hotel: any) {
    if (bookingData.type === "NonRefundable") {
      return hotel.nonRefundablePercentage;
    }
    return hotel.onlinePercentage;
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
        where: (booking_invoices, { eq }) =>
          eq(booking_invoices.customer_id, userId),
        orderBy: (booking_invoices, { desc }) => [
          desc(booking_invoices.created_at),
        ],
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
        data: BookingResource.toCollection(invoices),
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
      // Use Drizzle ORM query builder instead of db.query
      const invoices = await db
        .select()
        .from(booking_invoices)
        .where(eq(booking_invoices.id, Number(invoiceId)))
        .limit(1);

      const invoice = invoices[0];

      if (!invoice) {
        throw new Error("Booking not found");
      }

      // Fetch related bookings and rooms manually if needed
      const relatedBookings = await db
        .select()
        .from(bookings)
        .where(eq(bookings.booking_invoice_id, Number(invoiceId)));

      // Optionally, fetch rooms for each booking if needed
      // (Assuming you have a rooms table and schema)

      invoice.bookings = relatedBookings;

      return BookingResource.toResource(invoice);
    } catch (error: any) {
      console.error("Error fetching booking details:", error);
      throw new Error(error.message || "Failed to fetch booking details");
    }
  }

  async getBookingByIdOrReference(idOrReference: string | number) {
    const CACHE_EXPIRATION_SECONDS = 3600;
    const cacheKey = `booking:invoice:${idOrReference}`;

    try {
      // Try to get from cache first
      const cachedData = await redis.get(cacheKey);
      if (cachedData) {
        console.log(`Cache hit for key: ${cacheKey}`);
        return JSON.parse(cachedData);
      }
      console.log(`Cache miss for key: ${cacheKey}`);

      // Determine if input is numeric (ID) or string (reference)
      const isNumeric = !isNaN(Number(idOrReference));
      let invoices;
      if (isNumeric) {
        invoices = await db
          .select()
          .from(booking_invoices)
          .where(eq(booking_invoices.id, Number(idOrReference)))
          .limit(1);
      } else {
        invoices = await db
          .select()
          .from(booking_invoices)
          .where(
            or(
              eq(booking_invoices.id, Number(idOrReference)),
              eq(booking_invoices.reference, String(idOrReference))
            )
          )
          .limit(1);
      }
      console.log(invoices);

      const invoice = invoices[0];

      if (!invoice) {
        throw new Error("Booking not found");
      }

      // Fetch related bookings and rooms manually if needed
      const relatedBookings = await db
        .select()
        .from(bookings)
        .where(eq(bookings.booking_invoice_id, invoice.id));

      invoice.bookings = relatedBookings;

      // Return the booking resource (not an array)
      const result = BookingResource.toResource(invoice);

      // Cache the result
      await redis.set(
        cacheKey,
        JSON.stringify(result),
        "EX",
        CACHE_EXPIRATION_SECONDS
      );

      return result;
    } catch (error: any) {
      console.error("Error fetching booking by ID or reference:", error);
      throw new Error(error.message || "Failed to fetch booking");
    }
  }

  async getBookingsByStatus(
    userId: number,
    status: "completed" | "cancelled" | "current"
  ) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let whereClause;
    if (status === "completed") {
      whereClause = (booking_invoices: any, { eq, gt }: any) =>
        eq(booking_invoices.customer_id, userId) &&
        gt(booking_invoices.checked_in, today);
    } else if (status === "cancelled") {
      whereClause = (booking_invoices: any, { eq }: any) =>
        eq(booking_invoices.customer_id, userId) &&
        eq(booking_invoices.status, "cancelled");
    } else if (status === "current") {
      whereClause = (booking_invoices: any, { eq, lte }: any) =>
        eq(booking_invoices.customer_id, userId) &&
        lte(booking_invoices.checked_in, today);
    } else {
      throw new Error("Invalid status");
    }

    // If your ORM doesn't support this where clause function, use query builder logic instead.

    // Replace this:
    // const invoices = await db.query.booking_invoices.findMany({
    //   where: whereClause,
    //   orderBy: (booking_invoices, { desc }) => [
    //     desc(booking_invoices.created_at),
    //   ],
    //   with: {
    //     bookings: {
    //       with: {
    //         room: true,
    //       },
    //     },
    //   },
    // });
    // With this:
    const invoices = await db
      .select()
      .from(booking_invoices)
      .where(whereClause)
      .orderBy(booking_invoices.created_at, "desc");
    // Note: If you need to include related bookings and rooms, you will need to fetch them separately and attach them to each invoice.

    return BookingResource.toCollection(invoices);
  }
}
