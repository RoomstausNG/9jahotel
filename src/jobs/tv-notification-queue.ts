import { Queue } from "bullmq";
import { redisConnection } from "../config/redis";

export const tvNotificationQueue = new Queue("tvNotificationQueue", {
  connection: redisConnection,
  defaultJobOptions: {
    removeOnComplete: 50, // Keep last 50 completed jobs
    removeOnFail: 20, // Keep last 20 failed jobs
    attempts: 3, // Retry failed jobs up to 3 times
    backoff: {
      type: "exponential",
      delay: 2000, // Start with 2 second delay
    },
  },
});

// Interface for TV notification jobs
export interface TVNotificationJob {
  macaddress: string;
  eventType: string;
  data: Record<string, any>;
}

// Add TV notification to queue
export const queueTVNotification = async (data: TVNotificationJob) => {
  await tvNotificationQueue.add("sendTVNotification", data, {
    priority: 10, // High priority for real-time notifications
  });
};

// Get pending notifications for a TV
export const getPendingNotifications = async (macaddress: string) => {
  const jobs = await tvNotificationQueue.getJobs(
    ["waiting", "delayed"],
    0,
    100
  );
  return jobs.filter((job) => job.data.macaddress === macaddress);
};

// Remove processed notifications for a TV
export const removeProcessedNotifications = async (macaddress: string) => {
  const jobs = await tvNotificationQueue.getJobs(["completed"], 0, 100);
  const toRemove = jobs.filter((job) => job.data.macaddress === macaddress);
  await Promise.all(toRemove.map((job) => job.remove()));
};

// Mark notification as processed (move to completed)
export const markNotificationProcessed = async (jobId: string) => {
  const job = await tvNotificationQueue.getJob(jobId);
  if (job) {
    await job.moveToCompleted("processed", job.token || "");
  }
};
