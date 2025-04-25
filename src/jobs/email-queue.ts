import { Queue } from "bullmq";
import { redis } from "../config/redis";

export const emailQueue = new Queue("emailQueue", { connection: redis });

export const sendEmailJob = async (data: {
  email: string;
  subject: string;
  body: string;
}) => {
  await emailQueue.add("sendEmail", data);
};
