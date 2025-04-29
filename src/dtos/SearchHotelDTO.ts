import { z } from "zod";
import { db } from "../config/database"; // Import db instance
import { cities } from "../database/migrations/schema"; // Import cities schema
import { eq } from "drizzle-orm"; // Import eq operator

// Helper function to get the start of today
const startOfToday = () => {
  const today = new Date();
  today.setHours(0, 0, 0, 0); // Set time to midnight
  return today;
};

export const SearchHotelByCityDTO = z
  .object({
    checkIn: z.coerce
      .date({
        errorMap: (issue, ctx) => ({
          message: "Invalid check-in date format. Please provide a valid date.",
        }),
      })
      // Add min validation to ensure check-in date is not in the past
      .min(startOfToday(), { message: "Check-in date cannot be in the past." }),

    checkOut: z.coerce.date({
      errorMap: (issue, ctx) => ({
        message: "Invalid check-out date format. Please provide a valid date.",
      }),
    }),
    page: z.coerce.number().min(1).default(1),
    perpage: z.coerce.number().min(1).max(100).default(24),
    search: z.string().optional().default(""),
  })
  .refine((data) => data.checkOut > data.checkIn, {
    message: "Check-out date must be after check-in date",
    path: ["checkOut"],
  });

export const SearchHotelTypeDTO = z
  .object({
    checkIn: z.coerce
      .date({
        errorMap: (issue, ctx) => ({
          message: "Invalid check-in date format. Please provide a valid date.",
        }),
      })
      .min(startOfToday(), { message: "Check-in date cannot be in the past." }),

    checkOut: z.coerce.date({
      errorMap: (issue, ctx) => ({
        message: "Invalid check-out date format. Please provide a valid date.",
      }),
    }),
  })
  .refine((data) => data.checkOut > data.checkIn, {
    message: "Check-out date must be after check-in date",
    path: ["checkOut"],
  });

export const CityDTO = z.object({
  cityId: z.string().transform((val) => {
    const parsed = parseInt(val, 10);
    if (isNaN(parsed)) throw new Error("Invalid city ID");
    return parsed;
  }),
});

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
      // Also add min validation here for consistency if needed
      .min(startOfToday(), { message: "Check-in date cannot be in the past." }),
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
