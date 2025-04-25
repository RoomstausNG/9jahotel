import { z } from "zod";
import { db } from "../config/database";
import { customers } from "../database/migrations/schema";
import { eq } from "drizzle-orm";

export const referralExist = z
  .string()
  // Add refinement step
  .refine(
    async (val) => {
      // Only run the async database check if the value is a non-empty string
      if (val && val.trim().length > 0) {
        // <-- Checks if val is truthy and not empty
        return await isReferralExist(val);
      }
      // If the value is an empty string, null, or undefined (handled by .nullish()),
      // it should pass this specific refinement check because the field is optional/nullable.
      return true; // <-- Allows empty/null/undefined to pass the refinement
    },
    {
      message: "Invalid or non-existent referral code!",
    }
  )
  // Make the schema accept null or undefined in addition to a valid string
  .nullish();

export async function isReferralExist(referral_code: string): Promise<boolean> {
  const result = await db
    .select()
    .from(customers)
    .where(eq(customers.referral_code, referral_code))
    .limit(1);
  return result.length === 1;
}
