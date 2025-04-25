import { z } from "zod";

// DTO for requesting a password reset OTP
export const SendOtpDto = z.object({
  email: z.string().email("Invalid email address"),
});

// DTO for resetting the password using an OTP
export const ResetPasswordDto = z
  .object({
    email: z.string().email("Invalid email address"),
    otp: z.string().length(6, "OTP must be 6 digits"), // Assuming 6-digit OTP
    password: z.string().min(6, "Password must be at least 6 characters"),
    confirmPassword: z
      .string()
      .min(6, "Confirm password must be at least 6 characters"),
  })
  .refine((data) => data.password === data.confirmPassword, {
    message: "Passwords do not match",
    path: ["confirmPassword"], // path of error
  });

// Define types from schemas
export type SendOtpDtoType = z.infer<typeof SendOtpDto>;
export type ResetPasswordDtoType = z.infer<typeof ResetPasswordDto>;
import { uniqueEmail } from "../validators/unique-email";

export const authDto = z.object({
  name: z.string().min(3, "Name must be at least 3 characters"),
  email: uniqueEmail,
  password: z.string().min(6, "Password must be at least 6 characters"),
});

export interface SocialLoginDtoType {
  provider: "google" | "facebook" | "apple";
  token: string;
}
