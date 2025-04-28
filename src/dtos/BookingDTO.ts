import { z } from "zod";
import { existsRoomsValidator } from "../validators/exists-room";

// DTO for booking creation
export const CreateBookingDto = z
  .object({
    checkIn: z.coerce.date({
      required_error: "Check-in date is required",
      invalid_type_error: "Check-in must be a valid date",
    }),
    checkOut: z.coerce.date({
      required_error: "Check-out date is required",
      invalid_type_error: "Check-out must be a valid date",
    }),
    firstname: z.string().min(3, "First name must be at least 3 characters"),
    lastname: z.string().min(3, "Last name must be at least 3 characters"),
    email: z.string().email("Invalid email format"),
    phone: z.string().min(10, "Phone number must be at least 10 characters"),
    roomIds: existsRoomsValidator,
    type: z.enum(["standard", "premium", "deluxe"], {
      errorMap: () => ({ message: "Invalid room type selected" }),
    }),
    total: z.number().positive("Total must be a positive number"),
  })
  .refine(
    (data) => {
      // Ensure check-out date is after check-in date
      return data.checkOut > data.checkIn;
    },
    {
      message: "Check-out date must be after check-in date",
      path: ["checkOut"],
    }
  );

// Type inference
export type CreateBookingDtoType = z.infer<typeof CreateBookingDto>;
