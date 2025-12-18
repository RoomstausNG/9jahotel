import { Context } from "hono";
import { inject, injectable } from "inversify";
import { TVService } from "../services/TVService";
import { ApiResponse } from "../utils/ApiResponse";
import { formatDateToHumanReadable } from "../utils/DateAmountUtils";
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
    let rawData: any = {};
    let sanitizedMac: string | undefined;
    let tvRoom: string | undefined;
    let hotelRoom: string | undefined;

    try {
      rawData = await c.req.json();

      // Attempt to extract critical data for error reporting even before validation
      if (rawData.macaddress) {
        sanitizedMac = rawData.macaddress
          .replace(/[^a-zA-Z0-9]/g, "")
          .toUpperCase();
        tvRoom = `smart-tv:${sanitizedMac}`;
      }

      if (rawData.hotelcode) {
        hotelRoom = `hotel:${rawData.hotelcode}`;
      }

      // Validate input
      const validatedData = TVRegistrationDTO.parse(rawData);

      // Update sanitizedMac and rooms with validated data
      sanitizedMac = validatedData.macaddress
        .replace(/[^a-zA-Z0-9]/g, "")
        .toUpperCase();
      tvRoom = `smart-tv:${sanitizedMac}`;
      hotelRoom = `hotel:${validatedData.hotelcode}`;

      // Register TV
      const result = await this.tvService.registerTV(validatedData);

      // Log successful registration with details
      if (result.isNew) {
        console.log(
          `✓ TV registration successful (NEW): ID=${result.tv.id}, MAC=${result.sanitizedMac}, Room=${validatedData.roomnumber}, Hotel=${validatedData.hotelcode}, Rooms=[${tvRoom}, ${hotelRoom}]`
        );
      } else if (result.roomUpdated) {
        console.log(
          `✓ TV registration successful (ROOM UPDATED): ID=${result.tv.id}, MAC=${result.sanitizedMac}, Room=${validatedData.roomnumber}, Hotel=${validatedData.hotelcode}, Rooms=[${tvRoom}, ${hotelRoom}]`
        );
      } else {
        console.log(
          `✓ TV registration successful (RECONNECTION): ID=${result.tv.id}, MAC=${result.sanitizedMac}, Room=${validatedData.roomnumber}, Hotel=${validatedData.hotelcode}, Rooms=[${tvRoom}, ${hotelRoom}]`
        );
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
            timestamp: formatDateToHumanReadable(new Date().toISOString()),
          },
        });
        console.log(`✓ Registration event emitted to room: ${tvRoom}`);

        // Wait 5 seconds before sending connected event for new TVs
        // Capture tvRoom and tvId in closure to avoid undefined issues
        const roomForCallback = tvRoom;
        const tvIdForCallback = result.tv.id;
        // Capture data for callback
        const connectedData = {
          tvid: tvIdForCallback,
          macaddress: result.sanitizedMac,
          roomnumber: validatedData.roomnumber,
          hotelcode: validatedData.hotelcode,
          status: "active",
          message: "TV is now connected and active",
        };

        setTimeout(() => {
          io.to(roomForCallback).emit("tv-notification", {
            type: "connected",
            data: connectedData,
          });
          console.log(
            `✓ Connected event emitted to room: ${roomForCallback} (after 5-second delay)`
          );
        }, 5000);
      } else {
        // For reconnections and room updates, send only connected event immediately
        io.to(tvRoom).emit("tv-notification", {
          type: "connected",
          data: {
            tvid: result.tv.id,
            macaddress: result.sanitizedMac,
            roomnumber: validatedData.roomnumber,
            hotelcode: validatedData.hotelcode,
            status: "active",
            message: "TV is now connected and active",
          },
        });
        console.log(
          `✓ Connected event emitted to room: ${tvRoom} (immediate - reconnection/room update)`
        );
      }

      // Emit to hotel room about new TV registration
      io.to(hotelRoom).emit("tv-connected", {
        hotelcode: validatedData.hotelcode,
        roomnumber: validatedData.roomnumber,
        macaddress: result.sanitizedMac,
        tvid: result.tv.id,
        status: "active",
        timestamp: formatDateToHumanReadable(new Date().toISOString()),
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
        201
      );
    } catch (error: any) {
      let errorMessage = error.message || "TV registration failed";
      let validationErrors = undefined;

      if (error.name === "ZodError") {
        errorMessage = "Validation error";
        validationErrors = error.errors;
        // Format validation errors for the event message
        if (error.errors && error.errors.length > 0) {
          const details = error.errors
            .map((e: any) => `${e.path.join(".")}: ${e.message}`)
            .join(", ");
          errorMessage = `Validation error: ${details}`;
        }
      }

      console.error(`✗ TV Registration Error: ${errorMessage}`);

      // Emit failure event to both TV room and hotel room if we have ANY room info
      // Use whatever data we managed to extract (raw or validated)
      const failureData = {
        context: "registration",
        message: errorMessage,
        error: errorMessage, // Keep error for backward compatibility or clarity
        macaddress: sanitizedMac || rawData.macaddress || "unknown",
        roomnumber: rawData.roomnumber || "unknown",
        hotelcode: rawData.hotelcode || "unknown",
        ipaddress: rawData.ipaddress || "unknown",
        timestamp: formatDateToHumanReadable(new Date().toISOString()),
        validationErrors: validationErrors,
      };

      // Try to emit to TV room if we can determine it
      if (tvRoom) {
        try {
          io.to(tvRoom).emit("failure", failureData);
          console.log(
            `✓ Failure event emitted to TV room: ${tvRoom}`,
            failureData
          );
        } catch (emitError: any) {
          console.error(
            `✗ Failed to emit failure event to TV room: ${tvRoom}`,
            emitError
          );
        }
      }

      // Try to emit to hotel room if we can determine it
      if (hotelRoom) {
        try {
          io.to(hotelRoom).emit("failure", failureData);
          console.log(
            `✓ Failure event emitted to hotel room: ${hotelRoom}`,
            failureData
          );
        } catch (emitError: any) {
          console.error(
            `✗ Failed to emit failure event to hotel room: ${hotelRoom}`,
            emitError
          );
        }
      }

      if (!tvRoom && !hotelRoom) {
        console.error(
          "✗ Cannot emit failure event - insufficient data to determine target rooms"
        );
      }

      if (error.name === "ZodError") {
        return ApiResponse.failure("Validation error", 422, error.errors);
      }

      return ApiResponse.failure(errorMessage, 500);
    }
  }

  /**
   * Delete a registered TV
   */
  async deleteTV(c: Context) {
    try {
      const data = await c.req.json();
      const { macaddress, hotelcode } = data;

      if (!macaddress || !hotelcode) {
        return ApiResponse.failure(
          "MAC address and Hotel Code are required",
          400
        );
      }

      await this.tvService.deleteTV(macaddress, hotelcode);

      const sanitizedMac = macaddress
        .replace(/[^a-zA-Z0-9]/g, "")
        .toUpperCase();
      const tvRoom = `smart-tv:${sanitizedMac}`;

      // Emit deleted event
      io.to(tvRoom).emit("tv-notification", {
        type: "deleted",
        data: {
          message: "TV has been deleted",
          macaddress: sanitizedMac,
          hotelcode,
        },
      });

      return ApiResponse.success(
        { deleted: true, mac: sanitizedMac },
        "TV deleted successfully"
      );
    } catch (error: any) {
      console.error("Delete TV Error:", error.message);
      return ApiResponse.failure(error.message, 500);
    }
  }

  /**
   * Update TV details (Change TV)
   * This basically re-registers/updates the TV with new details
   */
  async updateTV(c: Context) {
    // Re-use registerTV logic as it handles updates gracefully
    return this.registerTV(c);
  }

  /**
   * Handle disconnected TV explicitly
   */
  async disconnectTV(c: Context) {
    try {
      const data = await c.req.json();
      const { macaddress } = data;

      if (macaddress) {
        const sanitizedMac = macaddress
          .replace(/[^a-zA-Z0-9]/g, "")
          .toUpperCase();
        console.log(`✓ TV disconnected explicitly: ${sanitizedMac}`);
        // Can add logic here to update status to 'offline' in DB if needed
      }

      return ApiResponse.success({ disconnected: true }, "TV disconnected");
    } catch (error: any) {
      return ApiResponse.failure(error.message, 500);
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
      console.log(
        `✓ Check-in data validation successful for room: ${validatedData.roomnumber}`
      );

      // Update TV last seen (no other database operations)
      const result = await this.tvService.checkIn(validatedData);

      // Craft welcome message using request data
      const welcomeMessage = `Welcome, ${
        validatedData.fullname
      }! Your check-in at ${formatDateToHumanReadable(
        validatedData.checkindatetime
      )} is confirmed. Enjoy your stay until ${formatDateToHumanReadable(
        validatedData.checkoutdatetime
      )}.${validatedData.message ? ` ${validatedData.message}` : ""}`;

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
            checkintime: formatDateToHumanReadable(
              validatedData.checkindatetime
            ),
            checkouttime: formatDateToHumanReadable(
              validatedData.checkoutdatetime
            ),
            message: welcomeMessage,
            tvid: validatedData.tvid,
          },
        });
        console.log(
          `✓ Check-in event broadcast successfully to room: ${tvRoom}`
        );
      } catch (broadcastError: any) {
        console.error(
          `✗ Check-in broadcast failed to room: ${tvRoom}`,
          broadcastError
        );
        throw new Error(`Broadcast failed: ${broadcastError.message}`);
      }

      return ApiResponse.success(
        {
          tvId: result.tvId,
          message: welcomeMessage,
        },
        "Check-in processed successfully"
      );
    } catch (error: any) {
      console.error("✗ Check-in endpoint failed:", error);

      // Emit error to TV room if we have the room identifier
      if (tvRoom) {
        try {
          io.to(tvRoom).emit("failure", {
            context: "check-in",
            message: error.message || "Check-in failed",
            timestamp: formatDateToHumanReadable(new Date().toISOString()),
          });
          console.log(`✓ Check-in failure event broadcast to room: ${tvRoom}`);
        } catch (errorBroadcastError: any) {
          console.error(
            `✗ Failed to broadcast check-in failure to room: ${tvRoom}`,
            errorBroadcastError
          );
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
      console.log(
        `✓ Reminder data validation successful for room: ${validatedData.roomnumber}`
      );

      // Update TV last seen (no other database operations)
      const result = await this.tvService.getTVForBroadcast(validatedData);

      // Craft reminder message using request data
      const reminderMessage = `Reminder: You have ${
        validatedData.reminderType === "checkout_2h" ? "2 hours" : "1 hour"
      } until checkout at ${formatDateToHumanReadable(
        validatedData.checkoutdatetime
      )}.${validatedData.message ? ` ${validatedData.message}` : ""}`;

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
            checkouttime: formatDateToHumanReadable(
              validatedData.checkoutdatetime
            ),
            message: reminderMessage,
            tvid: result.tvId, // Now available from service
          },
        });
        console.log(
          `✓ Reminder event broadcast successfully to room: ${tvRoom}`
        );
      } catch (broadcastError: any) {
        console.error(
          `✗ Reminder broadcast failed to room: ${tvRoom}`,
          broadcastError
        );
        throw new Error(`Broadcast failed: ${broadcastError.message}`);
      }

      return ApiResponse.success(
        {
          tvId: result.tvId,
          message: reminderMessage,
        },
        "Reminder sent successfully"
      );
    } catch (error: any) {
      console.error("✗ Reminder endpoint failed:", error);

      // Emit error to TV room if we have the room identifier
      if (tvRoom) {
        try {
          io.to(tvRoom).emit("failure", {
            context: "reminder",
            message: error.message || "Reminder failed",
            timestamp: formatDateToHumanReadable(new Date().toISOString()),
          });
          console.log(`✓ Reminder failure event broadcast to room: ${tvRoom}`);
        } catch (errorBroadcastError: any) {
          console.error(
            `✗ Failed to broadcast reminder failure to room: ${tvRoom}`,
            errorBroadcastError
          );
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
      console.log(
        `✓ Checkout data validation successful for room: ${validatedData.roomnumber}`
      );

      // Update TV last seen (no other database operations)
      const result = await this.tvService.checkOut(validatedData);

      // Craft checkout message using request data
      const checkoutMessage = `Thank you for your stay, ${
        validatedData.fullname
      }! Checkout completed at ${formatDateToHumanReadable(
        validatedData.checkoutdatetime
      )}.${validatedData.message ? ` ${validatedData.message}` : ""}`;

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
            checkouttime: formatDateToHumanReadable(
              validatedData.checkoutdatetime
            ),
            message: checkoutMessage,
            tvid: result.tvId, // Now available from service
          },
        });
        console.log(
          `✓ Checkout event broadcast successfully to room: ${tvRoom}`
        );
      } catch (broadcastError: any) {
        console.error(
          `✗ Checkout broadcast failed to room: ${tvRoom}`,
          broadcastError
        );
        throw new Error(`Broadcast failed: ${broadcastError.message}`);
      }

      return ApiResponse.success(
        {
          tvId: result.tvId,
          message: checkoutMessage,
        },
        "Checkout processed successfully"
      );
    } catch (error: any) {
      console.error("✗ Checkout endpoint failed:", error);

      // Emit error to TV room if we have the room identifier
      if (tvRoom) {
        try {
          io.to(tvRoom).emit("failure", {
            context: "checkout",
            message: error.message || "Checkout failed",
            timestamp: formatDateToHumanReadable(new Date().toISOString()),
          });
          console.log(`✓ Checkout failure event broadcast to room: ${tvRoom}`);
        } catch (errorBroadcastError: any) {
          console.error(
            `✗ Failed to broadcast checkout failure to room: ${tvRoom}`,
            errorBroadcastError
          );
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
        },
      });

      console.log(`Broadcast sent to hotel room: ${hotelRoom}`);

      return ApiResponse.success(
        {
          hotelCode: validatedData.hotelcode,
          message: validatedData.message,
        },
        "Broadcast sent successfully"
      );
    } catch (error: any) {
      console.error("Broadcast Error:", error);
      if (error.name === "ZodError") {
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      return ApiResponse.failure(
        error.message || "Internal server error:",
        500
      );
    }
  }
}
