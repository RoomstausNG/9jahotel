import { z } from "zod";

export const InitPaystackPaymentSchema = z.object({
  amount: z.number().min(100), // minimum amount in Naira
  email: z.string().email(),
  reference: z.string().min(6).optional(),
  callback_url: z.string().url().optional(),
});
