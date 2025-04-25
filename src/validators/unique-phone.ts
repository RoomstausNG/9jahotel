import { z } from "zod";
import { db } from "../config/database";
import { customers } from "../database/migrations/schema";
import { eq } from "drizzle-orm";

export const uniquePhone = z
  .string()
  .min(10, "Phone number must be atleast 10 digits")
  .max(11, "Phone number must be atleast 11 digits")
  .refine(isPhoneUnique, "Phone number already exists");

export async function isPhoneUnique(phone: string): Promise<boolean> {
  const result = await db
    .select()
    .from(customers)
    .where(eq(customers.phone, phone))
    .limit(1);
  return result.length === 0;
}
