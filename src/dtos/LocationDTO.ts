import { eq } from "drizzle-orm";
import { z } from "zod";
import { hotels } from "../database/migrations/schema";
import { db } from "../config/database";

// Helper function to get the start of today
const startOfToday = () => {
  const today = new Date();
  today.setHours(0, 0, 0, 0); // Set time to midnight
  return today;
};

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
    checkIn: z.coerce
      .date({
        errorMap: (issue, ctx) => ({
          message: "Invalid check-in date format. Please provide a valid date.",
        }),
      })
      .min(startOfToday(), { message: "Check-in date cannot be in the past." })
      .optional(),
    checkOut: z.coerce
      .date({
        errorMap: (issue, ctx) => ({
          message:
            "Invalid check-out date format. Please provide a valid date.",
        }),
      })
      .optional(),
  })
  .refine(
    (data) => {
      // If both dates are provided, checkOut must be after checkIn
      if (data.checkIn && data.checkOut) {
        return data.checkOut > data.checkIn;
      }
      // If only one date is provided, that's invalid
      if (data.checkIn && !data.checkOut) {
        return false;
      }
      if (!data.checkIn && data.checkOut) {
        return false;
      }
      // If neither date is provided, that's valid
      return true;
    },
    {
      message:
        "Either provide both check-in and check-out dates (with check-out after check-in), or provide neither",
      path: ["checkOut"],
    }
  );

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
