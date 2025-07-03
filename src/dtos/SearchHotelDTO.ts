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

    // Sorting parameters
    sortBy: z
      .enum(["room_lowest_price", "star_rating", "distance", "name"])
      .optional(),
    sortOrder: z.enum(["asc", "desc"]).default("asc"),

    // Price filtering parameters
    minPrice: z.coerce.number().min(0).optional(),
    maxPrice: z.coerce.number().min(0).optional(),

    // Coordinates for distance sorting
    latitude: z.coerce.number().min(-90).max(90).optional(),
    longitude: z.coerce.number().min(-180).max(180).optional(),
  })
  .refine((data) => data.checkOut > data.checkIn, {
    message: "Check-out date must be after check-in date",
    path: ["checkOut"],
  })
  .refine(
    (data) => {
      if (data.minPrice !== undefined && data.maxPrice !== undefined) {
        return data.minPrice <= data.maxPrice;
      }
      return true;
    },
    {
      message: "Minimum price cannot be greater than maximum price",
      path: ["maxPrice"],
    }
  )
  .refine(
    (data) => {
      if (data.sortBy === "distance") {
        return data.latitude !== undefined && data.longitude !== undefined;
      }
      return true;
    },
    {
      message: "Latitude and longitude are required when sorting by distance",
      path: ["sortBy"],
    }
  );

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
    perpage: z.coerce.number().min(1).max(100).default(24), // Updated default to match city search
    search: z.string().optional().default(""),

    // Sorting parameters
    sortBy: z
      .enum(["room_lowest_price", "star_rating", "distance", "name"])
      .optional(),
    sortOrder: z.enum(["asc", "desc"]).default("asc"),

    // Price filtering parameters
    minPrice: z.coerce.number().min(0).optional(),
    maxPrice: z.coerce.number().min(0).optional(),

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
  })
  .refine(
    (data) => {
      if (data.minPrice !== undefined && data.maxPrice !== undefined) {
        return data.minPrice <= data.maxPrice;
      }
      return true;
    },
    {
      message: "Minimum price cannot be greater than maximum price",
      path: ["maxPrice"],
    }
  );
