import { Context } from "hono";
import { inject, injectable } from "inversify";
import { HotelService } from "../services/HotelService";
import { AuthService } from "../services/AuthService";
import { ApiResponse } from "../utils/ApiResponse";
import {
  SearchHotelByCityDTO,
  SearchHotelTypeDTO,
} from "../dtos/SearchHotelDTO"; // Import the DTO
import { z } from "zod";

@injectable()
export class HotelController {
  constructor(@inject(HotelService) private hotelService: HotelService) {}

  async listByCity(c: Context) {
    try {
      const { cityId } = c.req.valid("param");

      const data = c.req.query();
      // Destructure the validated data
      const { checkIn, checkOut, page, perpage, search } =
        await SearchHotelByCityDTO.parseAsync(data);

      const hotels = await this.hotelService.listHotelsByCity(
        cityId,
        checkIn.toISOString(),
        checkOut.toISOString(),
        page,
        perpage,
        search
      );

      return ApiResponse.success(
        hotels,
        "Hotels for city fetched successfully"
      );
    } catch (error: any) {
      // Handle potential service errors
      return ApiResponse.failure(
        error.message || "Failed to fetch hotels",
        500
      );
    }
  }

  async searchNearbyHotels(c: Context) {
    try {
      const lat = parseFloat(c.req.query("lat") || "0");
      const long = parseFloat(c.req.query("long") || "0");
      const page = parseInt(c.req.query("page") || "1");
      const perPage = parseInt(c.req.query("perpage") || "10");
      const search = c.req.query("search") || "";
      const checkIn = c.req.query("checkIn");
      const checkOut = c.req.query("checkOut");
      const customerId = c.get("user")?.id || null;

      if (!lat || !long) {
        return ApiResponse.failure("Location coordinates are required", 400);
      }

      // Validate dates if provided
      if ((checkIn && !checkOut) || (!checkIn && checkOut)) {
        return ApiResponse.failure(
          "Both check-in and check-out dates must be provided",
          400
        );
      }

      const hotels = await this.hotelService.searchHotelsNearby(
        customerId,
        lat,
        long,
        page,
        perPage,
        search,
        checkIn,
        checkOut
      );

      return ApiResponse.success(hotels, "Hotels fetched successfully");
    } catch (error: any) {
      return ApiResponse.failure(error.message, 500);
    }
  }

  async listFavourites(c: Context) {
    try {
      const userId = c.get("user").id;

      const page = Number(c.req.query("page") || 1);
      const perPage = Number(c.req.query("perPage") || 24);
      const hotels = await this.hotelService.listFavouriteHotels(
        userId,
        page,
        perPage,
        c
      );
      return ApiResponse.success(
        hotels,
        "Favorite hotels fetched successfully"
      );
    } catch (error: any) {
      return ApiResponse.failure(error.message, 500);
    }
  }
  async showHotelDetails(c: Context) {
    try {
      const userId = c.get("user")?.id;
      const { code } = c.req.valid("param");

      const page = Number(c.req.query("page") || 1);
      const perPage = Number(c.req.query("perPage") || 24);
      const hotels = await this.hotelService.getHotelDetails(code, userId);
      return ApiResponse.success(hotels, "Hotel details fetched successfully");
    } catch (error: any) {
      return ApiResponse.failure(error.message, 500);
    }
  }

  async toggleFavourite(c: Context) {
    try {
      const { hotelId } = await c.req.json();
      const userId = c.get("user").id;
      if (!userId) {
        return ApiResponse.failure("User not authenticated", 401);
      }
      if (!hotelId || isNaN(Number(hotelId))) {
        return ApiResponse.failure("Invalid hotel ID", 400);
      }
      const result = await this.hotelService.toggleHotelFavourite(
        userId,
        Number(hotelId)
      );
      return ApiResponse.success(
        result,
        "Hotel favorite status toggled successfully"
      );
    } catch (error: any) {
      return ApiResponse.failure(error.message, 500);
    }
  }

  async getHotelTypes(c: Context) {
    try {
      const { code } = c.req.valid("param");
      // Use the correct DTO for query validation here
      const { checkIn, checkOut } = c.req.valid("query"); // Already validated by middleware

      const types = await this.hotelService.getHotelTypesByCode(
        code,
        checkIn.toISOString(),
        checkOut.toISOString()
      );

      return ApiResponse.success(
        types,
        "Hotel room categories fetched successfully"
      );
    } catch (error: any) {
      // Handle specific errors like "Hotel not found" potentially with 404
      if (error.message === "Hotel not found") {
        return ApiResponse.failure(error.message, 404);
      }
      return ApiResponse.failure(
        error.message || "Failed to fetch hotel types",
        500
      );
    }
  }

  async listAvailableRoomsByType(c: Context) {
    try {
      // Validate path parameter and query parameters using data validated by middleware
      const { typeId } = c.req.valid("param");
      const { checkIn, checkOut } = c.req.valid("query");

      const rooms = await this.hotelService.getAvailableRoomsByType(
        typeId,
        checkIn.toISOString(),
        checkOut.toISOString()
      );

      // The service method throws if type not found, caught below
      return ApiResponse.success(
        rooms,
        "Available rooms for the type fetched successfully"
      );
    } catch (error: any) {
      // Handle specific errors like "Room type not found" with 404
      if (error.message === "Room type not found") {
        return ApiResponse.failure(error.message, 404);
      }
      // Handle Zod validation errors (though middleware should catch them first)
      if (error instanceof z.ZodError) {
        return ApiResponse.failure(
          "Validation failed",
          400,
          error.flatten().fieldErrors
        );
      }
      return ApiResponse.failure(
        error.message || "Failed to fetch available rooms",
        500
      );
    }
  }

  async authRoomBooking(c: Context) {
    try {
      const body = await c.req.json();

      // Validate request
      const parsed = await customerSchema.parseAsync(body);

      const customer = c.get("user") || AuthService.getAuthUser(c);

      const booking = await this.hotelService.bookARoom(parsed, customer);
      return ApiResponse.success(
        booking,
        `Room booked successfully with booking code ${booking.code}`,
        201
      );
    } catch (error: Error) {
      if (error.message) {
        return ApiResponse.failure("Validation error", 400, error.message);
      }
      return ApiResponse.failure("Booking failed", 500);
    }
  }
}
