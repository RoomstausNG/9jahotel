import { Context } from "hono";
import { AuthService } from "../services/AuthService";
import { ApiResponse } from "../utils/ApiResponse";
import { SocialLoginDtoType } from "../dtos/UserDTO";
import { inject, injectable } from "inversify";
import { customerSchema } from "../validators/customer-schema";
import { ResetPasswordDto, SendOtpDto } from "../dtos/auth-dto";

@injectable()
export class AuthController {
  constructor(@inject(AuthService) private authService: AuthService) {}

  async register(c: Context) {
    try {
      const body = await c.req.json();

      // Validate request
      const parsed = await customerSchema.parseAsync(body);

      const customer = await this.authService.register(parsed);
      return ApiResponse.success(
        customer,
        "Customer registered successfully",
        201
      );
    } catch (error: any) {
      if (error.errors) {
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      return ApiResponse.failure("Registration failed", 500);
    }
  }

  async login(c: Context) {
    try {
      const body = await c.req.json();

      const { user, token } = await this.authService.login(
        body.email,
        body.password,
        c
      );
      return ApiResponse.success({ token, user }, "Login successful");
    } catch (error: any) {
      if (error.message) {
        return ApiResponse.failure("Validation error", 422, {
          password: error.message, // Provide a specific error message for the password error
        });
      }
      return ApiResponse.failure("Invalid credentials", 401);
    }
  }
  async updateAvatar(c: Context) {
    try {
      const body = await c.req.parseBody();

      if (!body.avatar) {
        return ApiResponse.failure("Empty file uploaded", 422, {
          avatar: "No image file uploaded",
        });
      }

      const file = body.avatar;

      console.log(typeof file, typeof body);

      const allowedTypes = [
        "image/jpeg",
        "image/png",
        "image/jpg",
        "image/webp",
      ];
      if (!allowedTypes.includes((file as File).type)) {
        return ApiResponse.failure(
          "Invalid file type. Only JPG, webp and PNG are allowed.",
          400,
          {
            avatar: "Inavlid file format",
          }
        );
      }

      // Check file size (example: max 5MB) - Middleware might handle this better
      const maxSize = 5 * 1024 * 1024; // 5MB
      if (file.size > maxSize) {
        // Adjust property based on middleware
        return ApiResponse.failure("File size exceeds the 5MB limit.", 400);
      }

      const result = await this.authService.updateAvatar(file, c);

      return ApiResponse.success(result, "Avatar updated successfully");
    } catch (error: any) {
      console.error("Avatar Upload Error:", error); // Log the actual error
      // Provide a more generic error to the client
      return ApiResponse.failure(
        error.message || "Failed to update avatar",
        500
      );
    }
  }

  async socialLogin(c: Context) {
    const body = await c.req.json();
    try {
      const data = await this.authService.socialLogin(body);
      return ApiResponse.success(
        data,
        `Login successfuly via ${body.provider}`
      );
    } catch (error: any) {
      return ApiResponse.failure(error.message, 400);
    }
  }

  async logout(c: Context) {
    try {
      this.authService.logout(c);
      return ApiResponse.success(null, "Logged out successfully");
    } catch (error) {
      console.error("Logout error:", error);
      return ApiResponse.failure("Logout failed", 500);
    }
  }

  async updatePassword(c: Context) {
    try {
      const body = await c.req.json();

      // Call the service method to update the password
      const result = await this.authService.updatePassword(c, body);
      return ApiResponse.success("Password updated successfully");
    } catch (error: any) {
      if (error.message) {
        return ApiResponse.failure("Validation error", 422, {
          currentPassword: error.message, // Provide a specific error message for the currentPassword erro
        });
      }
      return ApiResponse.failure("Validation error", 422, {
        currentPassword: "Invalid current password",
      });
    }
  }

  async updateLocation(c: Context) {
    try {
      const location = await c.req.json();

      // Call the service method to update the password
      const result = await this.authService.updateLocation(location, c);
      return ApiResponse.success(result, "Location updated successfully");
    } catch (error: any) {
      if (error.message) {
        return ApiResponse.failure("Validation error", 422, {
          currentPassword: error.message, // Provide a specific error message for the currentPassword erro
        });
      }
      return ApiResponse.failure("Validation error", 422, {
        currentPassword: "Invalid current password",
      });
    }
  }
  async updateProfile(c: Context) {
    try {
      const data = await c.req.json();

      // Call the service method to update the profile
      const result = await this.authService.updateProfile(data, c);
      return ApiResponse.success(result, "Profile updated successfully");
    } catch (error: any) {
      if (error.message) {
        return ApiResponse.failure("Bad request", 400, {
          currentPassword: error.message,
        });
      }
    }
  }

  async profile(c: Context) {
    const customer = c.get("user");
    return ApiResponse.success(customer, "Customer profile details");
  }

  async sendOtp(c: Context) {
    try {
      const body = await c.req.json();
      const parsed = await SendOtpDto.parseAsync(body);

      const result = await this.authService.sendOtp(parsed.email);
      return ApiResponse.success(result.message);
    } catch (error: any) {
      if (error.errors) {
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      console.error("Send OTP error:", error);
      return ApiResponse.failure(error.message || "Failed to send OTP", 500);
    }
  }

  async resetPassword(c: Context) {
    try {
      const body = await c.req.json();
      const parsed = await ResetPasswordDto.parseAsync(body); // Validate using ResetPasswordDto

      const result = await this.authService.resetPassword(parsed);
      return ApiResponse.success(result.message);
    } catch (error: any) {
      if (error.errors) {
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      console.error("Reset Password error:", error);
      // Provide specific feedback for common errors
      if (
        error.message === "OTP expired or invalid." ||
        error.message === "Incorrect OTP."
      ) {
        return ApiResponse.failure(error.message, 400);
      }
      return ApiResponse.failure(
        error.message || "Failed to reset password",
        500
      );
    }
  }
}
