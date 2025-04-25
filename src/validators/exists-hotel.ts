import { z } from "zod";
import { db } from "../config/database";
import { hotels } from "../database/migrations/schema";
import { eq } from "drizzle-orm";

export const existHotel = z
  .number({ required_error: "hotelId is required" })
  .int()
  .positive({ message: "hotelId must be a positive integer" })
  .refine(isHotelExist, "Hotel doesn't exist");

export async function isHotelExist(id: number): Promise<boolean> {
  const result = await db
    .select()
    .from(hotels)
    .where(eq(hotels.id, id))
    .limit(1);
  return result.length === 1;
}
