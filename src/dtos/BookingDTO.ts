import { uniqueEmail } from "./../validators/unique-email";
import { z } from "zod";
import { createInsertSchema, createSelectSchema } from "drizzle-zod";
import { customers as users } from "../database/migrations/schema";
import { uniquePhone } from "../validators/unique-phone";
import { existEmail } from "../validators/exists-email";

// Create DTO for user creation
export const CreateUserDto = createInsertSchema(users)
  .pick({
    firstname: true,
    lastname: true,
    email: true,
    password: true,
    phone: true,
    referral_code: true,
  })
  .extend({
    firstname: z
      .string()
      .min(3, "First name must be at least 3 characters long"),
    lastname: z.string().min(3, "Last name must be at least 3 characters long"),
    phone: uniquePhone,
    email: uniqueEmail,
    password: z.string().min(6, "Password must be at least 6 characters long"),
  });

// Create DTO for user updates
export const UpdateUserDto = createInsertSchema(users)
  .pick({
    firstname: true,
    lastname: true,
    email: true,
    phone: true,
    avatar: true,
  })
  .partial() // Make all fields optional for updates
  .extend({
    firstname: z
      .string()
      .min(3, "First name must be at least 3 characters long")
      .optional(),
    lastname: z
      .string()
      .min(3, "Last name must be at least 3 characters long")
      .optional(),
    email: z.string().email("Invalid email format").optional(),
    phone: uniquePhone.optional(),
    username: z
      .string()
      .min(3, "Username must be at least 3 characters long")
      .optional(),
  });

// Create DTO for user responses
export const UserResponseDto = createSelectSchema(users).pick({
  id: true,
  firstname: true,
  lastname: true,
  email: true,
  phone: true,
  avatar: true,
  created_at: true,
  updated_at: true,
});

// Login DTO
export const LoginDto = z.object({
  email: existEmail,
  password: z.string().min(1, "Password is required"),
});

export const PasswordUpdateDto = z
  .object({
    currentPassword: z.string().min(1, "Current password is required"),
    newPassword: z
      .string()
      .min(6, "New password must be at least 6 characters"),
    confirmPassword: z.string().min(1, "Please confirm your new password"),
  })
  .refine((data) => data.newPassword === data.confirmPassword, {
    message: "Passwords do not match",
    path: ["confirmPassword"],
  });

export const LocationUpdateDto = z.object({
  latitude: z
    .number()
    .min(-90, "Latitude must be greater than or equal to -90")
    .max(90, "Latitude must be less than or equal to 90"),
  longitude: z
    .number()
    .min(-180, "Longitude must be greater than or equal to -180")
    .max(180, "Longitude must be less than or equal to 180"),
});

export const ProfileUpdateDto = z.object({
  firstname: z.string().optional(),
  lastname: z.string().optional(),
  gender: z.string().optional(), // Assuming gender is stored as a string ('male', 'female', 'other')
  dob: z
    .date({
      // Ensure input is parsed as a Date object
      coerce: true, // Attempt to coerce string/number inputs into Dates
      errorMap: (issue, ctx) => {
        if (issue.code === z.ZodIssueCode.invalid_date) {
          return { message: "Invalid date format." };
        }
        return { message: ctx.defaultError };
      },
    })
    .optional()
    .refine(
      (dob) => {
        if (!dob) return true; // Optional field, valid if not provided
        const today = new Date();
        const eighteenYearsAgo = new Date(
          today.getFullYear() - 18,
          today.getMonth(),
          today.getDate()
        );
        return dob <= eighteenYearsAgo;
      },
      {
        message: "You must be at least 18 years old.",
      }
    ),
  address: z.string().optional(),
  cityId: z.number().int().positive().optional(), // Renamed from city_id for consistency
  idType: z
    .enum(["Passport", "Driver License", "National ID", "Voter ID"], {
      errorMap: (issue, ctx) => {
        // Use a single static error message for invalid enum values
        if (issue.code === z.ZodIssueCode.invalid_enum_value) {
          return {
            message:
              "Invalid ID type selected. Please choose a valid option from Passport, Driver License, National ID, Voter ID.",
          };
        }
        // Fallback to default error for other issues
        return { message: ctx.defaultError };
      },
    })
    .optional(),
  idNumber: z.string().optional(), // Corrected typo from idDumber, renamed from id_number
  latitude: z
    .number()
    .min(-90, "Latitude must be greater than or equal to -90")
    .max(90, "Latitude must be less than or equal to 90")
    .optional(),
  longitude: z
    .number()
    .min(-180, "Longitude must be greater than or equal to -180")
    .max(180, "Longitude must be less than or equal to 180")
    .optional(),
});

export const SocialLoginDto = z.object({
  token: z.string().min(1, "Token is required"),
  provider: z.enum(["google", "facebook", "apple"], {
    errorMap: () => ({
      message: "Type must be one of: google, facebook, apple",
    }),
  }),
});

export const PasswordResetDto = z
  .object({
    email: existEmail,
    newPassword: z
      .string()
      .min(6, "New password must be at least 6 characters"),
    confirmPassword: z.string().min(1, "Please confirm your new password"),
  })
  .refine((data) => data.newPassword === data.confirmPassword, {
    message: "Passwords do not match",
    path: ["confirmPassword"],
  });

export const ForgetPasswordDto = z.object({
  email: existEmail,
});

// Type inference
export type CreateUserDtoType = z.infer<typeof CreateUserDto>;
export type UpdateUserDtoType = z.infer<typeof UpdateUserDto>;
export type UserResponseDtoType = z.infer<typeof UserResponseDto>;
export type LoginDtoType = z.infer<typeof LoginDto>;
export type PasswordUpdateDtoType = z.infer<typeof PasswordUpdateDto>;
export type PasswordResetDtoType = z.infer<typeof PasswordResetDto>;
export type ForgetPasswordDtoType = z.infer<typeof ForgetPasswordDto>;
export type LocationUpdateDtoType = z.infer<typeof LocationUpdateDto>;
export type ProfileUpdateDtoType = z.infer<typeof ProfileUpdateDto>;
export type SocialLoginDtoType = z.infer<typeof SocialLoginDto>;
