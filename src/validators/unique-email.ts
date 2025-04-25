import { z } from "zod";
import { db } from "../config/database";
import { customers, users } from "../database/migrations/schema";
import { eq } from "drizzle-orm";

export const uniqueEmail = z
  .string()
  .email()
  .refine(isEmailUnique, "Email address already exists");

export async function isEmailUnique(email: string): Promise<boolean> {
  const result = await db
    .select()
    .from(customers)
    .where(eq(customers.email, email))
    .limit(1);
  return result.length === 0;
}
