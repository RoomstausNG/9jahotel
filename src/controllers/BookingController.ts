import { Context } from "hono";
import { inject, injectable } from "inversify";
import { BookingService } from "../services/BookingService";
import { HotelService } from "../services/HotelService";
import { ApiResponse } from "../utils/ApiResponse";
import { CreateBookingDto } from "../dtos/BookingDTO";
import { getDaysBetweenDates, calculateDiscountedAmount } from "../utils/DateAmountUtils";

@injectable()
export class BookingController {
  constructor(@inject(BookingService) private bookingService: BookingService) {}

  async createBooking(c: Context) {
    try {
      const body = await c.req.json();

      const singleId = body.roomIds[0];

      let singleRoomPrice = body.total / body.roomIds.length;

      const room = HotelService.getRoomDetailsById(singleId);
      console.log(room);

      const days = getDaysBetweenDates(body.checkIn, body.checkOut);
      
      const compute = calculateDiscountedAmount(room.price,days,percent);

      const percent =
        body.type == "NonRefundable"
          ? room.nonRefundablePercentage
          : room.onlinePercentage;

          if(compute.remainingAmount != body.total){
            ApiResponse.success(compute, `Invalid input the total amount for this booking type is ${compute.remainingAmount}`, ,400)
          }

      // Get user ID if authenticated
      const user = c.get("user");
      const userId = user?.id;

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
