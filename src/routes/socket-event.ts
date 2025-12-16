/**
 * Socket.IO Events Documentation
 *
 * This file handles real-time communication events for the chat application.
 *
 * Key Events:
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
