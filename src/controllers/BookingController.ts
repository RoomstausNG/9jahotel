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

@injectable()
export class BookingController {
  constructor(
    @inject(BookingService) private bookingService: BookingService,
    @inject(AuthService) private authService: AuthService
  ) {}

  async createBooking(c: Context) {
    try {
      const body = await c.req.json();

      const singleId = body.roomIds[0];

      let singleRoomPrice = body.total / body.roomIds.length;

      const room = await HotelService.getRoomDetailsById(singleId);

      const days = getDaysBetweenDates(body.checkIn, body.checkOut);

      const percent = Number(
        body.type == "NonRefundable"
          ? room.hotel.nonRefundablePercentage
          : room.hotel.onlinePercentage
      );

      const compute = calculateDiscountedAmount(room.price, days, percent);

      if (compute.remainingAmount !== body.total) {
        return ApiResponse.failure(
          `Invalid input the total amount for this booking type suppose to be ${compute.remainingAmount} but you provided ${body.total}`,
          400,
          compute
        );
      }

      // Get user ID if authenticated
      let user = c.get("user");
      let userId = user?.id;

      // If not authenticated, create a new customer and get the id
      if (!userId) {
        // Use AuthService.register to create a new customer
        const customerData = {
          email: body.email,
          firstname: body.firstname,
          lastname: body.lastname,
          phone: body.phone,
          password: body.password || "password",
        };

        user = await this.authService.register(customerData);
        userId = user?.id;
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

      const validatedData = {
        ...body,
        ...compute,
        days,
        percent,
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

      const bookings = await this.bookingService.getBookingsByUser(
        userId,
        page,
        perPage
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

  async verifyPayment(c) {
    try {
      const { transactionId, invoiceReference, userEmail } = await c.req.json();
      // Fetch the booking invoice to get the expected amount
      const [invoice] = await db
        .select()
        .from(booking_invoices)
        .where(eq(booking_invoices.reference, invoiceReference))
        .limit(1);
      if (!invoice) {
        return ApiResponse.failure(null, "Booking invoice not found.", 404);
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
        return ApiResponse.success(null, result.message);
      } else {
        return ApiResponse.failure(null, result.message, 400);
      }
    } catch (error) {
      return ApiResponse.failure(null, "Internal server error.", 500);
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
}
