import { Context } from "hono";
import { inject, injectable } from "inversify";
import { TVService } from "../services/TVService";
import { ApiResponse } from "../utils/ApiResponse";
import {
  TVRegistrationDTO,
  CheckInDTO,
  ReminderDTO,
  CheckoutDTO,
  BroadcastAllDTO,
} from "../dtos/EventDTO";
import { io } from "../routes/socket-event";

@injectable()
export class EventController {
  constructor(@inject(TVService) private tvService: TVService) {}

  /**
   * TV Registration Endpoint
   * POST /events/tv-registration
   */
  async registerTV(c: Context) {
    try {
      const data = await c.req.json();

      // Validate input
      const validatedData = TVRegistrationDTO.parse(data);

      // Register TV
      const result = await this.tvService.registerTV(validatedData);

      // Join Socket.IO rooms
      const tvRoom = `smart-tv:${result.sanitizedMac}`;
      const hotelRoom = `hotel:${result.hotel.code}`;

      // Note: In a real implementation, you'd get the socket from the TV connection
      // For now, we'll just log the rooms that should be joined

      console.log(
        `TV ${result.tv.id} registered in rooms: ${tvRoom}, ${hotelRoom}`,
      );

      // Emit registration event (only if new)
      if (result.isNew) {
        io.to(tvRoom).emit("tv-notification", {
          type: "registration",
          data: {
            hotelcode: validatedData.hotelcode,
            roomnumber: validatedData.roomnumber,
            macaddress: result.sanitizedMac,
            ipaddress: validatedData.ipaddress,
            tvid: result.tv.id,
            status: "active",
            timestamp: new Date().toISOString(),
          }
        });
      }

      // Also emit connected event to TV room as requested
      io.to(tvRoom).emit("tv-notification", {
        type: "connected",
        data: {
          tvid: result.tv.id,
          status: "active",
          message: "TV is now connected and active",
        }
      });

      // Emit to hotel room about new TV registration
      io.to(hotelRoom).emit("tv-connected", {
        hotelcode: validatedData.hotelcode,
        roomnumber: validatedData.roomnumber,
        macaddress: result.sanitizedMac,
        tvid: result.tv.id,
        status: "active",
        timestamp: new Date().toISOString(),
      });

      return ApiResponse.success(
        {
          tvId: result.tv.id,
          sanitizedMac: result.sanitizedMac,
          tvRoom,
          hotelRoom,
          isNew: result.isNew,
        },
        "TV registered successfully",
        201,
      );
    } catch (error: any) {
      console.error("TV Registration Error:", error);
      if (error.name === "ZodError") {
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      return ApiResponse.failure(error.message || "Internal server error", 500);
    }
  }

  /**
   * Check-in Event Endpoint
   * POST /events/check-in
   */
  async checkIn(c: Context) {
    let tvRoom: string | undefined;
    try {
      const data = await c.req.json();

      // Validate input
      const validatedData = CheckInDTO.parse(data);

      // Update TV last seen (no other database operations)
      const result = await this.tvService.checkIn(validatedData);

      // Craft welcome message using request data
      const welcomeMessage = `Welcome, ${validatedData.fullname}! Your check-in at ${validatedData.checkindatetime} is confirmed. Enjoy your stay until ${validatedData.checkoutdatetime}.${validatedData.message ? ` ${validatedData.message}` : ""}`;

      // Broadcast to TV room
      tvRoom = `smart-tv:${result.sanitizedMac}`;
      io.to(tvRoom).emit("tv-notification", {
        type: "check-in",
        data: {
          hotelcode: validatedData.hotelcode,
          roomnumber: validatedData.roomnumber,
          macaddress: result.sanitizedMac,
          fullname: validatedData.fullname,
          checkintime: validatedData.checkindatetime,
          checkouttime: validatedData.checkoutdatetime,
          message: welcomeMessage,
          tvid: validatedData.tvid,
        }
      });

      console.log(`✓ Check-in event broadcast successfully to room: ${tvRoom}`);

      return ApiResponse.success(
        {
          tvId: result.tvId,
          message: welcomeMessage,
        },
        "Check-in processed successfully",
      );
    } catch (error: any) {
      console.error("✗ Check-in Error:", error);
      
      // Emit error to TV room if we have the room identifier
      if (tvRoom) {
        io.to(tvRoom).emit("tv-notification", {
          type: "error",
          data: {
            event: "check-in",
            message: error.message || "Check-in failed",
            timestamp: new Date().toISOString(),
          }
        });
        console.log(`✗ Check-in error event broadcast to room: ${tvRoom}`);
      }
      
      if (error.name === "ZodError") {
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      return ApiResponse.failure(error.message || "Internal server error", 500);
    }
  }

  /**
   * Reminder Event Endpoint
   * POST /events/reminder
   */
  async sendReminder(c: Context) {
    let tvRoom: string | undefined;
    try {
      const data = await c.req.json();

      // Validate input
      const validatedData = ReminderDTO.parse(data);

      // Update TV last seen (no other database operations)
      const result = await this.tvService.getTVForBroadcast(validatedData);

      // Craft reminder message using request data
      const reminderMessage = `Reminder: You have ${validatedData.reminderType === "checkout_2h" ? "2 hours" : "1 hour"} until checkout at ${validatedData.checkoutdatetime}.${validatedData.message ? ` ${validatedData.message}` : ""}`;

      // Broadcast to TV room
      tvRoom = `smart-tv:${result.sanitizedMac}`;
      io.to(tvRoom).emit("tv-notification", {
        type: "reminder",
        data: {
          hotelcode: validatedData.hotelcode,
          roomnumber: validatedData.roomnumber,
          macaddress: result.sanitizedMac,
          fullname: null, // Not available in reminder
          checkintime: null, // Not available in reminder
          checkouttime: validatedData.checkoutdatetime,
          message: reminderMessage,
          tvid: result.tvId, // Now available from service
        }
      });

      console.log(`✓ Reminder event broadcast successfully to room: ${tvRoom}`);

      return ApiResponse.success(
        {
          tvId: result.tvId,
          message: reminderMessage,
        },
        "Reminder sent successfully",
      );
    } catch (error: any) {
      console.error("✗ Reminder Error:", error);
      
      // Emit error to TV room if we have the room identifier
      if (tvRoom) {
        io.to(tvRoom).emit("tv-notification", {
          type: "error",
          data: {
            event: "reminder",
            message: error.message || "Reminder failed",
            timestamp: new Date().toISOString(),
          }
        });
        console.log(`✗ Reminder error event broadcast to room: ${tvRoom}`);
      }
      
      if (error.name === "ZodError") {
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      return ApiResponse.failure(error.message || "Internal server error", 500);
    }
  }

  /**
   * Checkout Event Endpoint
   * POST /events/checkout
   */
  async checkOut(c: Context) {
    let tvRoom: string | undefined;
    try {
      const data = await c.req.json();

      // Validate input
      const validatedData = CheckoutDTO.parse(data);

      // Update TV last seen (no other database operations)
      const result = await this.tvService.checkOut(validatedData);

      // Craft checkout message using request data
      const checkoutMessage = `Thank you for your stay, ${validatedData.fullname}! Checkout completed at ${validatedData.checkoutdatetime}.${validatedData.message ? ` ${validatedData.message}` : ""}`;

      // Broadcast to TV room
      tvRoom = `smart-tv:${result.sanitizedMac}`;
      io.to(tvRoom).emit("tv-notification", {
        type: "checkout",
        data: {
          hotelcode: validatedData.hotelcode,
          roomnumber: validatedData.roomnumber,
          macaddress: result.sanitizedMac,
          fullname: validatedData.fullname,
          checkintime: null, // Not available in checkout
          checkouttime: validatedData.checkoutdatetime,
          message: checkoutMessage,
          tvid: result.tvId, // Now available from service
        }
      });

      console.log(`✓ Checkout event broadcast successfully to room: ${tvRoom}`);

      return ApiResponse.success(
        {
          tvId: result.tvId,
          message: checkoutMessage,
        },
        "Checkout processed successfully",
      );
    } catch (error: any) {
      console.error("✗ Checkout Error:", error);
      
      // Emit error to TV room if we have the room identifier
      if (tvRoom) {
        io.to(tvRoom).emit("tv-notification", {
          type: "error",
          data: {
            event: "checkout",
            message: error.message || "Checkout failed",
            timestamp: new Date().toISOString(),
          }
        });
        console.log(`✗ Checkout error event broadcast to room: ${tvRoom}`);
      }
      
      if (error.name === "ZodError") {
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      return ApiResponse.failure(error.message || "Internal server error", 500);
    }
  }

  /**
   * Broadcast All Event Endpoint
   * POST /events/broadcast-all
   */
  async broadcastAll(c: Context) {
    try {
      const data = await c.req.json();

      // Validate input
      const validatedData = BroadcastAllDTO.parse(data);

      // Broadcast to hotel room (no database operations)
      const hotelRoom = `hotel:${validatedData.hotelcode}`;
      io.to(hotelRoom).emit("tv-notification", {
        type: "broadcast",
        data: {
          hotelcode: validatedData.hotelcode,
          message: validatedData.message,
          ...(validatedData.title && { title: validatedData.title }),
        }
      });

      console.log(`Broadcast sent to hotel room: ${hotelRoom}`);

      return ApiResponse.success(
        {
          hotelCode: validatedData.hotelcode,
          message: validatedData.message,
        },
        "Broadcast sent successfully",
      );
    } catch (error: any) {
      console.error("Broadcast Error:", error);
      if (error.name === "ZodError") {
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      return ApiResponse.failure(error.message || "Internal server error:", 500);
    }
  }
}
