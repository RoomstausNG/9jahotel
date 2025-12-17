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
    let validatedData: any;
    let sanitizedMac: string | undefined;
    let tvRoom: string | undefined;
    let hotelRoom: string | undefined;

    try {
      const data = await c.req.json();

      // Validate input
      validatedData = TVRegistrationDTO.parse(data);
      
      // Register TV
      const result = await this.tvService.registerTV(validatedData);
      
      // Store for error handling
      sanitizedMac = result.sanitizedMac;

      // Join Socket.IO rooms
      tvRoom = `smart-tv:${result.sanitizedMac}`;
      hotelRoom = `hotel:${result.hotel.code}`;

      // Note: In a real implementation, you'd get the socket from the TV connection
      // For now, we'll just log the rooms that should be joined

      // Log successful registration with details
      if (result.isNew) {
        console.log(`✓ TV registration successful (NEW): ID=${result.tv.id}, MAC=${result.sanitizedMac}, Room=${validatedData.roomnumber}, Hotel=${validatedData.hotelcode}, Rooms=[${tvRoom}, ${hotelRoom}]`);
      } else if (result.roomUpdated) {
        console.log(`✓ TV registration successful (ROOM UPDATED): ID=${result.tv.id}, MAC=${result.sanitizedMac}, Room=${validatedData.roomnumber}, Hotel=${validatedData.hotelcode}, Rooms=[${tvRoom}, ${hotelRoom}]`);
      } else {
        console.log(`✓ TV registration successful (RECONNECTION): ID=${result.tv.id}, MAC=${result.sanitizedMac}, Room=${validatedData.roomnumber}, Hotel=${validatedData.hotelcode}, Rooms=[${tvRoom}, ${hotelRoom}]`);
      }

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
        console.log(`✓ Registration event emitted to room: ${tvRoom}`);
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
      console.log(`✓ Connected event emitted to room: ${tvRoom}`);

      // Emit to hotel room about new TV registration
      io.to(hotelRoom).emit("tv-connected", {
        hotelcode: validatedData.hotelcode,
        roomnumber: validatedData.roomnumber,
        macaddress: result.sanitizedMac,
        tvid: result.tv.id,
        status: "active",
        timestamp: new Date().toISOString(),
      });
      console.log(`✓ TV connection event emitted to hotel room: ${hotelRoom}`);

      return ApiResponse.success(
        {
          tvId: result.tv.id,
          sanitizedMac: result.sanitizedMac,
          tvRoom,
          hotelRoom,
          isNew: result.isNew,
          roomUpdated: result.roomUpdated,
        },
        "TV registered successfully",
        201,
      );
    } catch (error: any) {
      console.error("✗ TV Registration Error:", error.message || error);
      
      // Emit failure event to both TV room and hotel room if we have the necessary data
      if (sanitizedMac && validatedData) {
        tvRoom = tvRoom || `smart-tv:${sanitizedMac}`;
        hotelRoom = hotelRoom || `hotel:${validatedData.hotelcode}`;
        
        const failureData = {
          error: error.message || "TV registration failed",
          macaddress: sanitizedMac,
          roomnumber: validatedData.roomnumber,
          hotelcode: validatedData.hotelcode,
          ipaddress: validatedData.ipaddress,
          timestamp: new Date().toISOString(),
        };

        try {
          // Emit to TV room
          io.to(tvRoom).emit("tv-registration-failure", failureData);
          console.log(`✓ Failure event emitted to TV room: ${tvRoom}`, failureData);
        } catch (emitError: any) {
          console.error(`✗ Failed to emit failure event to TV room: ${tvRoom}`, emitError);
        }

        try {
          // Emit to hotel room
          io.to(hotelRoom).emit("tv-registration-failure", failureData);
          console.log(`✓ Failure event emitted to hotel room: ${hotelRoom}`, failureData);
        } catch (emitError: any) {
          console.error(`✗ Failed to emit failure event to hotel room: ${hotelRoom}`, emitError);
        }
      } else {
        console.error("✗ Cannot emit failure event - insufficient data (MAC or validated data missing)");
      }

      if (error.name === "ZodError") {
        console.error("✗ Validation error details:", error.errors);
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      
      console.error("✗ Returning error response to client:", error.message || "Internal server error");
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
      console.log(`✓ Check-in data validation successful for room: ${validatedData.roomnumber}`);

      // Update TV last seen (no other database operations)
      const result = await this.tvService.checkIn(validatedData);

      // Craft welcome message using request data
      const welcomeMessage = `Welcome, ${validatedData.fullname}! Your check-in at ${validatedData.checkindatetime} is confirmed. Enjoy your stay until ${validatedData.checkoutdatetime}.${validatedData.message ? ` ${validatedData.message}` : ""}`;

      // Broadcast to TV room
      tvRoom = `smart-tv:${result.sanitizedMac}`;
      try {
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
      } catch (broadcastError: any) {
        console.error(`✗ Check-in broadcast failed to room: ${tvRoom}`, broadcastError);
        throw new Error(`Broadcast failed: ${broadcastError.message}`);
      }

      return ApiResponse.success(
        {
          tvId: result.tvId,
          message: welcomeMessage,
        },
        "Check-in processed successfully",
      );
    } catch (error: any) {
      console.error("✗ Check-in endpoint failed:", error);
      
      // Emit error to TV room if we have the room identifier
      if (tvRoom) {
        try {
          io.to(tvRoom).emit("tv-notification", {
            type: "error",
            data: {
              event: "check-in",
              message: error.message || "Check-in failed",
              timestamp: new Date().toISOString(),
            }
          });
          console.log(`✓ Check-in error event broadcast to room: ${tvRoom}`);
        } catch (errorBroadcastError: any) {
          console.error(`✗ Failed to broadcast check-in error to room: ${tvRoom}`, errorBroadcastError);
        }
      }
      
      if (error.name === "ZodError") {
        console.error("✗ Check-in validation failed:", error.errors);
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      console.error("✗ Check-in returning error response:", error.message);
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
      console.log(`✓ Reminder data validation successful for room: ${validatedData.roomnumber}`);

      // Update TV last seen (no other database operations)
      const result = await this.tvService.getTVForBroadcast(validatedData);

      // Craft reminder message using request data
      const reminderMessage = `Reminder: You have ${validatedData.reminderType === "checkout_2h" ? "2 hours" : "1 hour"} until checkout at ${validatedData.checkoutdatetime}.${validatedData.message ? ` ${validatedData.message}` : ""}`;

      // Broadcast to TV room
      tvRoom = `smart-tv:${result.sanitizedMac}`;
      try {
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
      } catch (broadcastError: any) {
        console.error(`✗ Reminder broadcast failed to room: ${tvRoom}`, broadcastError);
        throw new Error(`Broadcast failed: ${broadcastError.message}`);
      }

      return ApiResponse.success(
        {
          tvId: result.tvId,
          message: reminderMessage,
        },
        "Reminder sent successfully",
      );
    } catch (error: any) {
      console.error("✗ Reminder endpoint failed:", error);
      
      // Emit error to TV room if we have the room identifier
      if (tvRoom) {
        try {
          io.to(tvRoom).emit("tv-notification", {
            type: "error",
            data: {
              event: "reminder",
              message: error.message || "Reminder failed",
              timestamp: new Date().toISOString(),
            }
          });
          console.log(`✓ Reminder error event broadcast to room: ${tvRoom}`);
        } catch (errorBroadcastError: any) {
          console.error(`✗ Failed to broadcast reminder error to room: ${tvRoom}`, errorBroadcastError);
        }
      }
      
      if (error.name === "ZodError") {
        console.error("✗ Reminder validation failed:", error.errors);
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      console.error("✗ Reminder returning error response:", error.message);
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
      console.log(`✓ Checkout data validation successful for room: ${validatedData.roomnumber}`);

      // Update TV last seen (no other database operations)
      const result = await this.tvService.checkOut(validatedData);

      // Craft checkout message using request data
      const checkoutMessage = `Thank you for your stay, ${validatedData.fullname}! Checkout completed at ${validatedData.checkoutdatetime}.${validatedData.message ? ` ${validatedData.message}` : ""}`;

      // Broadcast to TV room
      tvRoom = `smart-tv:${result.sanitizedMac}`;
      try {
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
      } catch (broadcastError: any) {
        console.error(`✗ Checkout broadcast failed to room: ${tvRoom}`, broadcastError);
        throw new Error(`Broadcast failed: ${broadcastError.message}`);
      }

      return ApiResponse.success(
        {
          tvId: result.tvId,
          message: checkoutMessage,
        },
        "Checkout processed successfully",
      );
    } catch (error: any) {
      console.error("✗ Checkout endpoint failed:", error);
      
      // Emit error to TV room if we have the room identifier
      if (tvRoom) {
        try {
          io.to(tvRoom).emit("tv-notification", {
            type: "error",
            data: {
              event: "checkout",
              message: error.message || "Checkout failed",
              timestamp: new Date().toISOString(),
            }
          });
          console.log(`✓ Checkout error event broadcast to room: ${tvRoom}`);
        } catch (errorBroadcastError: any) {
          console.error(`✗ Failed to broadcast checkout error to room: ${tvRoom}`, errorBroadcastError);
        }
      }
      
      if (error.name === "ZodError") {
        console.error("✗ Checkout validation failed:", error.errors);
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      console.error("✗ Checkout returning error response:", error.message);
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
