import { z } from "zod";
import { existsRoomsValidator } from "../validators/exists-room";

const startOfToday = () => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return today;
};

export const CouponCheckDTO = z.object({
  code: z.string().min(1, "Coupon code is required"),
  amount: z.number().positive("Amount must be greater than zero"),
});

// DTO for booking creation
export const CreateBookingDto = z
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

    firstname: z.string().min(3, "First name must be at least 3 characters"),

    lastname: z.string().min(3, "Last name must be at least 3 characters"),

    email: z.string().email("Please provide a valid email address"),
    phone: z.string().min(10, "Phone number must be at least 10 characters"),
    roomIds: existsRoomsValidator,
    reference: z.string().min(8).optional(),

    type: z.enum(["PayAtHotel", "PayNow", "NonRefundable"], {
      errorMap: (issue, ctx) => {
        // Kept errorMap for specific enum message
        if (issue.code === z.ZodIssueCode.invalid_enum_value) {
          return {
            message:
              "Invalid payment type. Please choose PayAtHotel, PayNow, or NonRefundable.",
          };
        }
        return { message: ctx.defaultError };
      },
    }),
    total: z
      .number() // Removed required_error and invalid_type_error
      .positive("Total must be a positive number"),
    couponId: z.string().optional(),
  })
  .refine((data) => data.checkOut > data.checkIn, {
    message: "Check-out date must be after check-in date",
    path: ["checkOut"],
  });

export const CreateAuthBookingDto = z
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

    roomIds: existsRoomsValidator,

    type: z.enum(["PayAtHotel", "PayNow", "NonRefundable"], {
      errorMap: (issue, ctx) => {
        // Kept errorMap for specific enum message
        if (issue.code === z.ZodIssueCode.invalid_enum_value) {
          return {
            message:
              "Invalid payment type. Please choose PayATHotel, PayNow, or NonRefundable.",
          };
        }
        return { message: ctx.defaultError };
      },
    }),
    total: z
      .number() // Removed required_error and invalid_type_error
      .positive("Total must be a positive number"),
    couponId: z.number().optional(),
  })
  .refine((data) => data.checkOut > data.checkIn, {
    message: "Check-out date must be after check-in date",
    path: ["checkOut"],
  });

// Type inference
export type CreateBookingDtoType = z.infer<typeof CreateBookingDto>;
export type CouponCheckDTOType = z.infer<typeof CouponCheckDTO>;

export const GetBookingByIdOrReferenceDTO = z.object({
  reference: z.string().min(1, "Booking ID or reference is required"),
});

export const GetBookingsByStatusDTO = z.object({
  status: z.enum(["completed", "cancelled", "upcoming"], {
    errorMap: (issue, ctx) => ({
      message: "Status must be one of: completed, cancelled, upcoming",
    }),
  }),
  page: z.coerce.number().min(1).default(1),
  perPage: z.coerce.number().min(1).max(100).default(10),
});
