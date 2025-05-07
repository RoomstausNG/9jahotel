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
      console.log({ room, body, percent, compute });
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

      // return ApiResponse.success(body, "Booking data validated successfully");

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

      return ApiResponse.success(
        validatedData,
        "Booking data validated successfully"
      );

      // Create booking
      const booking = await this.bookingService.createBooking(
        validatedData,
        userId
      );

      return ApiResponse.success(booking, "Booking created successfully", 201);
    } catch (error: any) {
      // Handle validation errors
      if (error.errors) {
        return ApiResponse.failure("Validation error", 400, error.errors);
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

  async getBookingDetails(c: Context) {
    try {
      const invoiceId = c.req.param("invoiceId");
      const userId = c.get("user")?.id;

      const booking = await this.bookingService.getBookingDetails(invoiceId);

      // Check if the booking belongs to the authenticated user
      if (userId && booking.customer_id !== userId) {
        return ApiResponse.failure("Unauthorized access to booking", 403);
      }

      return ApiResponse.success(
        booking,
        "Booking details fetched successfully"
      );
    } catch (error: any) {
      if (error.message === "Booking not found") {
        return ApiResponse.failure(error.message, 404);
      }

      return ApiResponse.failure(
        error.message || "Failed to fetch booking details",
        error.status || 500
      );
    }
  }
}
