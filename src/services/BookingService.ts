import {
  booking_invoices,
  bookings,
  customers,
  halls,
  hotels,
  rooms,
} from "./../database/migrations/schema";
import { injectable, inject } from "inversify";
import { db, dbPool } from "../config/database";
import { generateBookingReference } from "../utils/referenceGenerator";
import { HotelService } from "./HotelService";
import { ourCommission } from "../utils/DateAmountUtils";
import { count, eq } from "drizzle-orm";
import { EmailService } from "./EmailService";
import { BookingResource } from "../resources/BookingResource";
import { redis } from "../config/redis";
import { or, gt, and, lte } from "drizzle-orm";
import { Context } from "hono";
import { AuthService } from "./AuthService";
import { getToken } from "../utils/getToken";

@injectable()
export class BookingService {
  constructor(@inject(EmailService) private emailService: EmailService) {}

  async createBooking(bookingData: any, c: Context, userId?: number) {
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
      const result = await dbPool.transaction(async (tx: typeof db) => {
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
      });

      // Send confirmation email after successful booking
      try {
        const customerName =
          bookingData.firstname && bookingData.lastname
            ? `${bookingData.firstname} ${bookingData.lastname}`
            : bookingData.customerName || "Guest";
        const bookingAmount = bookingData.total;
        const numberOfRooms = bookingData.roomIds.length;
        const roomCategory = room.roomCategory.name.toUpperCase();
        const bookingReference = reference;
        const checkIn = bookingData.checkIn;
        const checkOut = bookingData.checkOut;
        const guestEmail = bookingData.email;

        // Send email to guest
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

        // Send email to hotel
        if (hotel.email) {
          await this.emailService.sendTemplateEmail({
            to: hotel.email,
            subject: "New Reservation Received",
            templateName: "hotelBooking",
            context: {
              hotelName: hotel.name,
              customerName,
              bookingAmount,
              numberOfRooms,
              roomCategory, // now uppercase
              bookingReference,
              checkIn,
              checkOut,
              bookedBy: customerName,
              totalRooms: numberOfRooms,
              guestEmail,
            },
          });
        }
      } catch (emailError) {
        console.error("Failed to send booking confirmation email:", emailError);
      }

      // Clear bookings status caches for this user
      try {
        const user = userId || c.get("user")?.id;
        if (user) {
          // Assuming redis supports the 'keys' and 'del' commands
          const pattern = `bookings:status:${user}:*`;
          const keys = await redis.keys(pattern);
          if (keys.length > 0) {
            await redis.del(...keys);
            console.log(`Cleared bookings cache for user ${user}`);
          }
        }
      } catch (cacheError) {
        console.error("Failed to clear bookings cache:", cacheError);
      }

      // Return the booking invoice resource
      return await this.getBookingByIdOrReference(reference, c);
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

  async getBookingByIdOrReference(idOrReference: string | number, c?: Context) {
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

      let invoices;
      const isNumeric =
        !isNaN(Number(idOrReference)) &&
        idOrReference !== "" &&
        idOrReference !== null;
      if (isNumeric) {
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
      } else {
        invoices = await db
          .select()
          .from(booking_invoices)
          .where(eq(booking_invoices.reference, String(idOrReference)))
          .limit(1);
      }

      console.log(invoices);

      const invoice = invoices[0];

      if (!invoice) {
        throw new Error("Booking not found");
      }

      // Fetch related bookings
      const relatedBookings = await db
        .select()
        .from(bookings)
        .where(eq(bookings.booking_invoice_id, invoice.id));

      // Fetch customer details
      const customerResult = await db
        .select()
        .from(customers)
        .where(eq(customers.id, invoice.customer_id))
        .limit(1);

      // Fix: Assign the first item from the array instead of the whole array
      invoice.customer = customerResult[0] || null;

      // Fetch hotel details
      if (invoice.hotel_id) {
        const hotelResult = await db
          .select()
          .from(hotels)
          .where(eq(hotels.id, invoice.hotel_id))
          .limit(1);
        invoice.hotel = hotelResult[0] || null;
      }

      // For each booking, fetch room/hall and category
      for (const booking of relatedBookings) {
        if (booking.room_id) {
          booking.room = await db
            .select()
            .from(rooms)
            .where(eq(rooms.id, booking.room_id))
            .limit(1);
          // Optionally fetch category if not included in room
          // booking.room.category = ...
        }
        if (booking.hall_id) {
          booking.hall = await db
            .select()
            .from(halls)
            .where(eq(halls.id, booking.hall_id))
            .limit(1);
          // Optionally fetch category if not included in hall
          // booking.hall.category = ...
        }
      }
      invoice.bookings = relatedBookings;

      // Cache the result before returning
      const resourceResult = BookingResource.toResource(invoice);
      await redis.set(
        cacheKey,
        JSON.stringify(resourceResult),
        "EX",
        CACHE_EXPIRATION_SECONDS
      );

      return resourceResult;
    } catch (error: any) {
      console.error("Error fetching booking by ID or reference:", error);
      throw new Error(error.message || "Failed to fetch booking");
    }
  }

  async getBookingsByStatus(
    status: string,
    page: number = 1,
    perPage: number = 24,
    c: Context
  ) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const token = getToken(c);

    const user =
      c.get("user") ?? (await AuthService.getAuthUser(token as string));

    if (!user) {
      throw new Error("User not authenticated");
    }

    const userId = user.id;

    // Generate a unique cache key for this user, status, and pagination
    const CACHE_EXPIRATION_SECONDS = 3600;
    const cacheKey = `bookings:status:${userId}:${status}:page:${page}:perPage:${perPage}`;

    // Try to get from cache first
    try {
      const cachedData = await redis.get(cacheKey);
      if (cachedData) {
        console.log(`Cache hit for key: ${cacheKey}`);
        return JSON.parse(cachedData);
      }
      console.log(`Cache miss for key: ${cacheKey}`);
    } catch (error) {
      console.error("Redis error getting cache:", error);
    }

    // Calculate offset for pagination
    const offset = (page - 1) * perPage;

    let whereClause;

    if (status === "completed") {
      whereClause = and(
        eq(booking_invoices.customer_id, userId),
        gt(booking_invoices.checked_in, today)
      );
    } else if (status === "cancelled") {
      whereClause = and(
        eq(booking_invoices.customer_id, userId),
        eq(booking_invoices.status, "cancelled")
      );
    } else if (status === "upcoming") {
      whereClause = and(
        eq(booking_invoices.customer_id, userId),
        lte(booking_invoices.checked_in, today)
      );
    } else {
      throw new Error("Invalid status");
    }

    // Get total count for pagination - modified to handle potential undefined result
    const countResult = await db
      .select({ count: count() })
      .from(booking_invoices)
      .where(whereClause);

    const total = countResult[0]?.count ?? 0;

    // Get paginated invoices
    const invoices = await db
      .select()
      .from(booking_invoices)
      .where(whereClause)
      .orderBy(booking_invoices.created_at, "desc")
      .limit(perPage)
      .offset(offset);

    // Fetch and attach related data for each invoice
    for (const invoice of invoices) {
      // Fetch related bookings
      const relatedBookings = await db
        .select()
        .from(bookings)
        .where(eq(bookings.booking_invoice_id, invoice.id));

      // Fetch customer details
      const customerResult = await db
        .select()
        .from(customers)
        .where(eq(customers.id, invoice.customer_id))
        .limit(1);
      invoice.customer = customerResult[0] || null;

      // Fetch hotel details
      if (invoice.hotel_id) {
        const hotelResult = await db
          .select()
          .from(hotels)
          .where(eq(hotels.id, invoice.hotel_id))
          .limit(1);
        invoice.hotel = hotelResult[0] || null;
      }

      // For each booking, fetch room/hall and category
      for (const booking of relatedBookings) {
        if (booking.room_id) {
          booking.room = await db
            .select()
            .from(rooms)
            .where(eq(rooms.id, booking.room_id))
            .limit(1);
        }
        if (booking.hall_id) {
          booking.hall = await db
            .select()
            .from(halls)
            .where(eq(halls.id, booking.hall_id))
            .limit(1);
        }
      }
      invoice.bookings = relatedBookings;
    }

    // Return with pagination metadata using BookingResource
    const result = BookingResource.toCollection(invoices, total, page, perPage);

    // Cache the result before returning
    try {
      await redis.set(
        cacheKey,
        JSON.stringify(result),
        "EX",
        CACHE_EXPIRATION_SECONDS
      );
      console.log(`Cached data for key: ${cacheKey}`);
    } catch (error) {
      console.error("Redis error setting cache:", error);
    }

    return result;
  }
}
