# TV Notification Queue Guide (Simplified) 📺

This guide explains how your TV notification system handles messages when a TV is offline.

## 1. The Problem We Fixed
Previously, the system used a complex queue called "BullMQ". While BullMQ is great for big tasks like emails, it was too complex for TVs. It was "ghosting"—it said messages were saved, but they were actually getting lost in background processes before the TV could see them.

## 2. The New Professional Solution: Redis Lists 🛡️
We switched to a **Direct Redis List** approach. Think of it like a physical "Delivery Box" for each TV.

### Step 1: Saving the Message (When TV is Offline)
When the system tries to send a notification (like a Check-in) but the TV is not connected:
- It takes that message and drops it into a **Redis List** named after the TV's MAC address (e.g., `tv:buffer:ACBECADFEA`).
- The message "sits" there safely.
- We set a **24-hour timer** on it. If the TV doesn't come online for a whole day, the message expires to keep things clean.

### Step 2: Picking Up the Message (When TV Reconnects)
The moment the TV comes back online and registers:
- The system checks its "Delivery Box" (the Redis List) using its MAC address.
- It pulls out **all** saved messages.
- It sends each one to the TV instantly via the Socket.

### Step 3: Emptying the Box
Once all messages are successfully sent to the TV:
- The system **deletes** the list for that TV.
- This ensures the TV doesn't get the same "Welcome" message twice if it reconnects again later.

## 3. Why This Works Perfectly Now (Bulletproof) ✅
- **Invisible to Workers**: There are no background "workers" that can accidentally delete or move your messages.
- **Direct & Fast**: We talk directly to Redis. If the code says it's saved, it's 100% in the database.
- **Easy to See**: You can check if a TV has messages waiting by running this command in your terminal:
  ```bash
  redis-cli LLEN tv:buffer:YOUR_MAC_ADDRESS
  ```
  If it returns `1` or more, you know there are messages waiting to be delivered!

## Summary of Code Files
- **`src/jobs/tv-notification-queue.ts`**: This is where the "Saving" and "Fetching" logic lives.
- **`src/controllers/EventController.ts`**: This is where the system decides *when* to check the buffer (right when the TV connects).

Everything is now reliable, transparent, and built to handle reconnections perfectly.
