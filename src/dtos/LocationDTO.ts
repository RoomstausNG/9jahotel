import { eq } from "drizzle-orm";
import { z } from "zod";
import { hotels } from "../database/migrations/schema";
import { db } from "../config/database";

export const NearbyHotelDTO = z
  .object({
    lat: z.coerce
      .number()
      .min(-90)
      .max(90, "Latitude must be between -90 and 90"),
    long: z.coerce
      .number()
      .min(-180)
      .max(180, "Longitude must be between -180 and 180"),
    distance: z.coerce
      .number()
      .min(1, "Distance must be at least 1 km")
      .default(10),
    page: z.coerce.number().min(1).default(1),
    perpage: z.coerce.number().min(1).max(100).default(10),
    search: z.string().optional().default(""),
    checkIn: z.coerce.date({
      errorMap: (issue, ctx) => ({
        message: "Invalid check-in date format. Please provide a valid date.",
      }),
    }),
    checkOut: z.coerce.date({
      errorMap: (issue, ctx) => ({
        message: "Invalid check-out date format. Please provide a valid date.",
      }),
    }),
  })
  .refine((data) => data.checkOut > data.checkIn, {
    message: "Check-out date must be after check-in date",
    path: ["checkOut"], // Attach the error specifically to the checkOut field
  });

export const HotelCodeDTO = z.object({
  code: z.string().refine(isHotelExist, "No hotel with the privided code"),
});

export async function isHotelExist(code: string): Promise<boolean> {
  const result = await db
    .select()
    .from(hotels)
    .where(eq(hotels.code, code))
    .limit(1);
  return result.length === 1;
}
