import { Context } from "hono";
import { inject, injectable } from "inversify";
import { BookingService } from "../services/BookingService";
import { HotelService } from "../services/HotelService";
import { ApiResponse } from "../utils/ApiResponse";
import { CreateBookingDto } from "../dtos/BookingDTO";
import {
  getDaysBetweenDates,
  calculateDiscountedAmount,
} from "../utils/DateAmountUtils";
import { AuthService } from "../services/AuthService";
import { CreateCustomerDtoType } from "../types/customer";
import { db } from "../config/database";
import { customers, booking_invoices } from "../database/migrations/schema";
import { eq, or } from "drizzle-orm";

@injectable()
export class BookingController {
  constructor(
    @inject(BookingService) private bookingService: BookingService,
    @inject(AuthService) private authService: AuthService
  ) {}

  async createBooking(c: Context) {
    try {
      const body = await c.req.json();

      // Get details for ALL rooms, not just the first one
      const roomDetails = await Promise.all(
        body.roomIds.map(async (roomId: number) => {
          return await HotelService.getRoomDetailsById(roomId);
        })
      );

      // Ensure all rooms belong to the same hotel
      const hotelIds = [...new Set(roomDetails.map((room) => room.hotel.id))];
      if (hotelIds.length > 1) {
        return ApiResponse.failure(
          "All rooms must belong to the same hotel",
          400
        );
      }

      const hotel = roomDetails[0].hotel; // Since all rooms are from the same hotel
      const days = getDaysBetweenDates(body.checkIn, body.checkOut);

      // Get the discount percentage based on booking type
      const percent = Number(
        body.type === "NonRefundable"
          ? hotel.nonRefundablePercentage
          : hotel.onlinePercentage
      );

      // Calculate total base amount for ALL rooms
      let totalBaseAmount = 0;
      const roomCalculations = roomDetails.map((room) => {
        const roomBaseAmount = room.price * days;
        totalBaseAmount += roomBaseAmount;

        return {
          roomId: room.id,
          price: room.price,
          baseAmount: roomBaseAmount,
          roomDetail: room,
        };
      });

      // Apply discount to the total amount
      const compute = calculateDiscountedAmount(totalBaseAmount, 1, percent); // Using 1 day since we already calculated total for all days

      // Validate the provided total against calculated amount
      if (Math.abs(compute.remainingAmount - body.total) > 0.01) {
        // Allow for small rounding differences
        return ApiResponse.failure(
          `Invalid total amount. Expected: ${compute.remainingAmount} for ${body.roomIds.length} room(s) for ${days} day(s), but you provided: ${body.total}`,
          400,
          {
            expectedTotal: compute.remainingAmount,
            providedTotal: body.total,
            baseAmount: compute.originalAmount,
            discountAmount: compute.discountedAmount,
            discountPercent: percent,
            numberOfRooms: body.roomIds.length,
            numberOfDays: days,
            roomBreakdown: roomCalculations,
          }
        );
      }

      // Get user ID if authenticated
      let user = c.get("user");
      let userId = user?.id;

      // If not authenticated, check if customer exists or create a new one
      if (!userId) {
        // First, try to find existing customer by email
        const existingCustomers = await db
          .select()
          .from(customers)
          .where(
            or(eq(customers.email, body.email), eq(customers.phone, body.phone))
          )
          .limit(1);

        if (existingCustomers.length > 0) {
          // Customer exists, use their ID
          user = existingCustomers[0];
          userId = user.id;
          console.log(`Found existing customer with ID: ${userId}`);
        } else {
          // Customer doesn't exist, create a new one
          const customerData = {
            email: body.email,
            firstname: body.firstname,
            lastname: body.lastname,
            phone: body.phone,
            password: body.password || "password123", // Default password for guest bookings
          };

          user = await this.authService.register(customerData);
          userId = user?.id;
          console.log(`Created new customer with ID: ${userId}`);
        }
      } else {
        // If authenticated, check idType and idNumber
        if (!user.idType || !user.idNumber) {
          return ApiResponse.failure(
            "Documents must be updated before booking can be made.",
            400,
            {
              idType: "ID type is required",
              idNumber: "ID number is required",
            }
          );
        }
      }

      // Ensure we have a valid userId
      if (!userId) {
        return ApiResponse.failure(
          "Failed to identify or create customer",
          500
        );
      }

      const validatedData = {
        ...body,
        total: compute.remainingAmount, // Use the calculated amount to ensure consistency
        baseAmount: compute.originalAmount,
        discountAmount: compute.discountedAmount,
        days,
        percent,
        numberOfRooms: body.roomIds.length,
      };

      // Create booking
      const booking = await this.bookingService.createBooking(
        validatedData,
        c,
        userId
      );

      return ApiResponse.success(booking, "Booking created successfully", 201);
    } catch (error: any) {
      // Handle validation errors
      if (error.errors) {
        return ApiResponse.failure("Validation error", 422, error.errors);
      }

      return ApiResponse.failure(
        error.message || "Failed to create booking",
        error.status || 500
      );
    }
  }

  async getUserBookings(c: Context) {
    try {
      const userId = c.get("user").id;

      if (!userId) {
        return ApiResponse.failure("User not authenticated", 401);
      }

      const page = Number(c.req.query("page") || 1);
      const perPage = Number(c.req.query("perPage") || 10);

      // Use getBookingsByStatus with "all" status or create a new method
      const bookings = await this.bookingService.getBookingsByStatus(
        "upcoming", // or implement an "all" status in the service
        page,
        perPage,
        c
      );

      return ApiResponse.success(bookings, "Bookings fetched successfully");
    } catch (error: any) {
      return ApiResponse.failure(
        error.message || "Failed to fetch bookings",
        error.status || 500
      );
    }
  }

  async getBookingByIdOrReference(c: Context) {
    try {
      const { reference: idOrReference } = c.req.param();
      const booking = await this.bookingService.getBookingByIdOrReference(
        idOrReference,
        c
      );

      if (!booking) {
        return ApiResponse.failure("Booking not found", 404);
      }

      // Optionally, check user ownership here if needed

      return ApiResponse.success(booking, "Booking fetched successfully");
    } catch (error: any) {
      return ApiResponse.failure(
        error.message || "Failed to fetch booking",
        error.status || 500
      );
    }
  }

  async verifyPayment(c: Context) {
    try {
      const { transactionId, invoiceReference, userEmail } = await c.req.json();
      // Fetch the booking invoice to get the expected amount
      const [invoice] = await db
        .select()
        .from(booking_invoices)
        .where(eq(booking_invoices.reference, invoiceReference))
        .limit(1);
      if (!invoice) {
        return ApiResponse.failure("Booking invoice not found.", 404);
      }
      const expectedAmount = invoice.amount;
      // Call the payment verification service
      // const result = await PaymentService.verifyFlutterwavePayment({
      //   transactionId,
      //   expectedAmount,
      //   invoiceReference,
      //   userEmail,
      // });
      // if (result.success) {
      //   return ApiResponse.success(null, result.message);
      // } else {
      //   return ApiResponse.failure(result.message, 400);
      // }
      return ApiResponse.failure("Payment verification not implemented", 501);
    } catch (error) {
      return ApiResponse.failure("Internal server error.", 500);
    }
  }

  async getBookingsByStatus(c: Context) {
    try {
      const { status } = c.req.query();
      const userId = c.get("user").id; // Adjust this if you get userId differently

      const page = Number(c.req.query("page") || 1);
      const perPage = Number(c.req.query("perPage") || 24);

      const bookings = await this.bookingService.getBookingsByStatus(
        status,
        page,
        perPage,
        c
      );

      return ApiResponse.success(bookings, "Bookings fetched successfully");
    } catch (error: any) {
      return ApiResponse.failure(
        error.message || "Failed to fetch bookings",
        error.status || 500
      );
    }
  }

  async createBookingForAuthUser(c: Context) {
    try {
      const bookingData = await c.req.json();
      const user = c.get("user");
      if (!user) {
        return ApiResponse.failure("User not authenticated", 401);
      }
      // Reuse the BookingService method, passing the authenticated user's ID
      const booking = await this.bookingService.createBooking(
        bookingData,
        c,
        user.id
      );
      return ApiResponse.success(booking, "Booking created successfully");
    } catch (error: any) {
      return ApiResponse.failure(
        error.message || "Failed to create booking",
        500
      );
    }
  }
}
