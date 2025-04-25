import { z } from "zod";

// Schema for path parameter validation (e.g., /hotels/types/:typeId/rooms)
export const RoomTypeParamDTO = z.object({
  typeId: z.coerce.number().int().positive("Type ID must be a positive integer"),
});

// Schema for query parameter validation for fetching available rooms
export const AvailableRoomsQueryDTO = z.object({
  checkIn: z.coerce.date({
    errorMap: (issue, { defaultError }) => ({
      message:
        issue.code === "invalid_date"
          ? "Invalid check-in date format (YYYY-MM-DD expected)"
          : defaultError,
    }),
  }),
  checkOut: z.coerce.date({
    errorMap: (issue, { defaultError }) => ({
      message:
        issue.code === "invalid_date"
          ? "Invalid check-out date format (YYYY-MM-DD expected)"
          : defaultError,
    }),
  }),
}).refine((data) => data.checkOut > data.checkIn, {
  message: "Check-out date must be after check-in date",
  path: ["checkOut"], // Attach error to the checkOut field
});

// Export inferred types for use in controllers/services
export type RoomTypeParamType = z.infer<typeof RoomTypeParamDTO>;
export type AvailableRoomsQueryType = z.infer<typeof AvailableRoomsQueryDTO>;