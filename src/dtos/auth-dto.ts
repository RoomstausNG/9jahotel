import { z } from "zod";
import { existEmail } from "../validators/exists-email"; // Adjust the path as needed

// DTO for requesting a password reset OTP
export const SendOtpDto = z.object({
  email: existEmail,
});

// DTO for verifying OTP
export const VerifyOtpDto = z.object({
  email: existEmail,
  otp: z.string().length(5, "OTP must be 5 digits"),
});

// DTO for resetting the password using an OTP
export const ResetPasswordDto = z
  .object({
    email: existEmail,
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
export type VerifyOtpDtoType = z.infer<typeof VerifyOtpDto>;
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
