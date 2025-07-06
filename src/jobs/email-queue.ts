import { Queue } from "bullmq";
import { redisConnection } from "../config/redis";

export const emailQueue = new Queue("emailQueue", {
  connection: redisConnection,
  defaultJobOptions: {
    removeOnComplete: 100, // Keep last 100 completed jobs
    removeOnFail: 50, // Keep last 50 failed jobs
    attempts: 3, // Retry failed jobs up to 3 times
    backoff: {
      type: "exponential",
      delay: 5000, // Start with 5 second delay
    },
  },
});

// Interface for template email jobs
export interface TemplateEmailJob {
  to: string;
  subject: string;
  templateName: string;
  context: Record<string, any>;
}

// Interface for simple email jobs
export interface SimpleEmailJob {
  to: string;
  subject: string;
  body: string;
}

// Add template email to queue
export const queueTemplateEmail = async (data: TemplateEmailJob) => {
  await emailQueue.add("sendTemplateEmail", data, {
    priority: 10, // Higher priority for template emails
  });
};

// Add simple email to queue
export const queueSimpleEmail = async (data: SimpleEmailJob) => {
  await emailQueue.add("sendSimpleEmail", data, {
    priority: 5, // Lower priority for simple emails
  });
};

// Add booking confirmation emails (high priority)
export const queueBookingEmails = async (
  guestEmail: TemplateEmailJob,
  hotelEmail?: TemplateEmailJob
) => {
  // Guest email has highest priority
  await emailQueue.add("sendTemplateEmail", guestEmail, {
    priority: 20,
  });

  // Hotel email has high priority
  if (hotelEmail) {
    await emailQueue.add("sendTemplateEmail", hotelEmail, {
      priority: 15,
    });
  }
};
