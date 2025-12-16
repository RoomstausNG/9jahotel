/**
 * Socket.IO Events Documentation
 *
 * This file handles real-time communication events for the chat application and TV events.
 *
 * Chat Events:
 * - handshake: Authenticates user and joins their personal room (user:{userId})
 * - joinConversation: Joins a conversation room (conversation:{conversationId})
 * - leaveConversation: Leaves a conversation room
 * - sendMessage: Sends a message to a conversation
 *   - Emits: "message" to conversation room with full message data
 *   - Emits: "newMessage" to each recipient's user room with {userId, conversationId}
 *   - Emits: "unreadConversationsCount" to each recipient's user room
 * - typingIndicator: Emits typing status to conversation room
 * - editMessage: Edits a message and emits to conversation room
 * - deleteMessage: Deletes a message and emits to conversation room
 * - markMessageAsRead: Marks message as read and emits "messageRead" to conversation room
 * - disconnect: Handles user disconnection and updates status
 *
 * TV Events:
 * - tv-registration: Registers a TV with hotel and room, joins rooms (smart-tv:macAddress, hotel:hotelcode)
 * - check-in: Processes guest check-in and broadcasts welcome message to TV
 * - reminder: Sends reminder notifications to specific TV (checkout_2h, checkout_1h)
 * - checkout: Processes guest checkout and broadcasts thank you message to TV
 * - broadcast-all: Broadcasts custom message to all TVs in a hotel
 *
 * New Events Added:
 * - newMessage: Emitted to recipient's user room when a new message is sent
 *   - Data: { userId: string, conversationId: string }
 *   - Purpose: Notify users of new messages per user, useful for push notifications
 *
 * Notification Events (handled in NotificationService):
 * - notification: Emitted to user's room when a new notification is created
 *   - Data: Full notification object {id, userId, title, body, type, isRead, createdAt, updatedAt}
 * - requestNotification: Emitted additionally when notification type is "request"
 *   - Data: Same as notification
 *   - Purpose: Special handling for connection requests
 */

import { Server } from "socket.io";
import { Server as Engine } from "@socket.io/bun-engine";
import { eq } from "drizzle-orm";
import { dbPool } from "../config/database";
import { redis } from "../config/redis";
import { withTimeout } from "../config/redis";
import { container } from "../container";
import { EventController } from "../controllers/EventController";

const io = new Server({
  cors: {
    origin: ["*"],
    methods: ["GET", "POST", "PUT", "PATCH", "DELETE"],
    allowedHeaders: ["Authorization", "Content-Type"],
    credentials: true,
  },
});

const engine = new Engine({ path: "/socket.io" });
io.bind(engine);

const { websocket } = engine.handler();

io.on("connection", (socket) => {
  console.log("New TV connected", socket.id);

  socket.emit("welcome", { message: "Welcome to the chat!" });

  socket.on("handshake", async (userId: string) => {});

  socket.on("leaveConversation", (conversationId) => {});

  // TV Event Handlers
  const eventController = container.get(EventController);

  socket.on("tv-registration", async (data) => {
    try {
      // For Socket.IO, we'll simulate a context-like object
      const mockContext = {
        req: {
          json: async () => Promise.resolve(data),
        },
      };
      const response = await eventController.registerTV(mockContext as any);
      
      let result;
      if (response && typeof (response as any).json === 'function') {
         result = await (response as any).json();
      } else if (response instanceof Response) {
        result = await response.json();
      } else {
        result = response;
      }

      if (result.success && result.data) {
        // Join rooms
        const { tvRoom, hotelRoom } = result.data;
        if (tvRoom) socket.join(tvRoom);
        if (hotelRoom) socket.join(hotelRoom);
        console.log(`Socket ${socket.id} joined rooms: ${tvRoom}, ${hotelRoom}`);
        
        // Emit connected event specifically to this socket
        socket.emit("tv-notification", {
          type: "connected",
          data: {
            tvid: result.data.tvId,
            status: "active",
            message: "TV is now connected and active",
          }
        });

        // If new, also emit registration event to this socket
        if (result.data.isNew) {
          socket.emit("tv-notification", {
            type: "registration",
            data: {
              tvid: result.data.tvId,
              macaddress: result.data.sanitizedMac,
              status: "active",
              timestamp: new Date().toISOString(),
              hotelcode: data.hotelcode,
              roomnumber: data.roomnumber,
              ipaddress: data.ipaddress
            }
          });
        }
      }
      
      socket.emit("tv-registration-success", result);
    } catch (error: any) {
      socket.emit("tv-registration-error", { error: error.message });
    }
  });

  socket.on("check-in", async (data) => {
    try {
      const mockContext = {
        req: {
          json: async () => Promise.resolve(data),
        },
      };
      const response = await eventController.checkIn(mockContext as any);
      let result;
      if (response && typeof (response as any).json === 'function') {
         result = await (response as any).json();
      } else if (response instanceof Response) {
        result = await response.json();
      } else {
        result = response;
      }
      socket.emit("check-in-success", result);
    } catch (error: any) {
      socket.emit("check-in-error", { error: error.message });
    }
  });

  socket.on("reminder", async (data) => {
    try {
      const mockContext = {
        req: {
          json: async () => Promise.resolve(data),
        },
      };
      const response = await eventController.sendReminder(mockContext as any);
      let result;
      if (response && typeof (response as any).json === 'function') {
         result = await (response as any).json();
      } else if (response instanceof Response) {
        result = await response.json();
      } else {
        result = response;
      }
      socket.emit("reminder-success", result);
    } catch (error: any) {
      socket.emit("reminder-error", { error: error.message });
    }
  });

  socket.on("checkout", async (data) => {
    try {
      const mockContext = {
        req: {
          json: async () => Promise.resolve(data),
        },
      };
      const response = await eventController.checkOut(mockContext as any);
      let result;
      if (response && typeof (response as any).json === 'function') {
         result = await (response as any).json();
      } else if (response instanceof Response) {
        result = await response.json();
      } else {
        result = response;
      }
      socket.emit("checkout-success", result);
    } catch (error: any) {
      socket.emit("checkout-error", { error: error.message });
    }
  });

  socket.on("broadcast-all", async (data) => {
    try {
      const mockContext = {
        req: {
          json: async () => Promise.resolve(data),
        },
      };
      const response = await eventController.broadcastAll(mockContext as any);
      let result;
      if (response && typeof (response as any).json === 'function') {
         result = await (response as any).json();
      } else if (response instanceof Response) {
        result = await response.json();
      } else {
        result = response;
      }
      socket.emit("broadcast-all-success", result);
    } catch (error: any) {
      socket.emit("broadcast-all-error", { error: error.message });
    }
  });

  // Handle disconnect event to mark user offline
  socket.on("disconnect", async () => {
    let userId: string | undefined;
    const queryUserId = socket.handshake.query.userId;
    if (typeof queryUserId === "string") {
      userId = queryUserId.trim();
    } else if (Array.isArray(queryUserId) && queryUserId.length > 0) {
      userId = queryUserId[0].trim();
    }
    if (!userId) {
      console.warn(`Disconnect event: No userId found for socket ${socket.id}`);
      return;
    }

    try {
      const redisKey = `user:${userId}:online`;
      await redis.del(redisKey);
      console.log(`User ${userId} marked offline on disconnect`);

      // Notify relevant rooms of offline status
      const rooms = socket.rooms;
      rooms.forEach((room) => {
        if (room.startsWith("conversation:")) {
          io.to(room).emit("userStatus", { userId, status: "offline" });
        }
      });
    } catch (error: any) {
      console.error(`Redis error marking ${userId} offline: ${error.message}`);
    }
  });
});

export { io, websocket, engine };
