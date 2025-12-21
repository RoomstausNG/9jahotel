import { redis } from "../config/redis";

// Interface for TV notification jobs
export interface TVNotificationJob {
  macaddress: string;
  eventType: string;
  data: Record<string, any>;
  timestamp: number; // When the job was created
}

/**
 * Professional Redis-Direct Buffer Implementation
 * 
 * We use a Redis List for each TV (keyed by MAC address).
 * This is 100% transparent and bypasses BullMQ persistence issues.
 */

// Buffer a notification for a TV in a Redis List
export const queueTVNotification = async (data: TVNotificationJob) => {
  const key = `tv:buffer:${data.macaddress}`;
  const payload = JSON.stringify({
    eventType: data.eventType,
    data: data.data,
    timestamp: data.timestamp
  });

  // Push to end of list
  await redis.rpush(key, payload);
  // Set 24 hour expiry on the buffer
  await redis.expire(key, 86400);

  const count = await redis.llen(key);
  console.log(
    `📋 Notification buffered (Redis List): MAC=${data.macaddress}, Type=${data.eventType}, BufferCount=${count}`
  );
  
  return true;
};

// Retrieve all buffered notifications for a TV
export const getPendingNotifications = async (macaddress: string) => {
  const key = `tv:buffer:${macaddress}`;
  
  // Get all items from the list
  const items = await redis.lrange(key, 0, -1);
  
  console.log(
    `🔍 Searching Redis Buffer for MAC=${macaddress}, Found ${items.length} messages.`
  );

  const decodedJobs = items.map((item, index) => {
    try {
      const parsed = JSON.parse(item);
      return {
        id: `msg:${index}`, // Synthetic ID for logging
        data: parsed
      };
    } catch (e) {
      console.error(`❌ Error parsing buffered message for ${macaddress}`, e);
      return null;
    }
  }).filter(Boolean);

  return decodedJobs;
};

// Clear the entire buffer for a TV
export const markNotificationProcessed = async (macaddress: string) => {
  if (!macaddress) return;
  const key = `tv:buffer:${macaddress}`;
  await redis.del(key);
  console.log(`✅ Redis Buffer cleared for MAC=${macaddress}`);
};

/**
 * Redundant functions maintained for compatibility with EventController,
 * but functionally no-ops or simplified in the list approach.
 */
export const markNotificationDelivered = async (macaddress: string, id: string) => {
  // In the list approach, we clear the whole list at once.
  // Individual tracking is less necessary, but we'll leave it empty.
};

export const isNotificationDelivered = async (macaddress: string, id: string) => {
  return false; // List items are either there or cleared.
};

