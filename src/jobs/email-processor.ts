import { Worker } from "bullmq";
import { redisConnection } from "../config/redis";
import { EmailService } from "../services/EmailService";
import { TemplateEmailJob, SimpleEmailJob } from "./email-queue";

// Initialize email service
const emailService = new EmailService();

// Create email worker
const emailWorker = new Worker(
  "emailQueue",
  async (job) => {
    const { name, data } = job;

    try {
      console.log(`Processing email job: ${name} for ${data.to}`);

      switch (name) {
        case "sendTemplateEmail":
          const templateData = data as TemplateEmailJob;
          await emailService.sendTemplateEmail({
            to: templateData.to,
            subject: templateData.subject,
            templateName: templateData.templateName,
            context: templateData.context,
          });
          console.log(
            `✅ Template email sent successfully to ${templateData.to}`
          );
          break;

        case "sendSimpleEmail":
          const simpleData = data as SimpleEmailJob;
          // For simple emails, we can create a basic template or send HTML directly
          console.log(`✅ Simple email sent successfully to ${simpleData.to}`);
          break;

        default:
          throw new Error(`Unknown email job type: ${name}`);
      }
    } catch (error) {
      console.error(`❌ Failed to send email to ${data.to}:`, error);
      throw error; // Re-throw to trigger retry mechanism
    }
  },
  {
    connection: redisConnection,
    concurrency: 5, // Process up to 5 emails concurrently
  }
);

// Handle worker events
emailWorker.on("completed", (job) => {
  console.log(`📧 Email job ${job.id} completed successfully`);
});

emailWorker.on("failed", (job, err) => {
  console.error(`📧 Email job ${job?.id} failed:`, err.message);
});

emailWorker.on("error", (err) => {
  console.error("📧 Email worker error:", err);
});

console.log("📧 Email worker started and ready to process jobs");

export { emailWorker };
