import { Worker } from "bullmq";
import { redis } from "../config/redis";

new Worker(
  "emailQueue",
  async (job) => {
    console.log(`Sending email to ${job.data.email}`);
    // Simulate email sending
  },
  { connection: redis }
);

// to add new job
// await sendEmailJob({
//   email: "user@example.com",
//   subject: "Welcome!",
//   body: "Thanks for signing up!",
// });
