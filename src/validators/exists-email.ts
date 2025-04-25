import { z } from "zod";
import { db } from "../config/database";
import { customers } from "../database/migrations/schema";
import { eq } from "drizzle-orm";

export const existEmail = z
  .string()
  .email()
  .refine(isEmailExist, "No user with this Email address");

export async function isEmailExist(email: string): Promise<boolean> {
  const result = await db
    .select()
    .from(customers)
    .where(eq(customers.email, email))
    .limit(1);
  return result.length === 1;
}
