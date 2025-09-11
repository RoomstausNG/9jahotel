import { Context } from "hono";
import { AuthService } from "../services/AuthService";
import { ApiResponse } from "../utils/ApiResponse";
import { inject, injectable } from "inversify";
import { customerSchema } from "../validators/customer-schema";
import {
  generateCloudinarySignature,
  bufferToStream,
} from "../utils/cloudinaryUtils";
import cloudinary from "../config/cloudinary";
import { UploadApiResponse, UploadApiErrorResponse } from "cloudinary";
import { container } from "../container";
import { UserService } from "../services/UserService";

@injectable()
export class AuthController {
  async deleteAccount(c: Context) {
    try {
      const user = c.get("user");
      if (!user || !user.id) {
        return ApiResponse.failure("User not authenticated", 401);
      }

      // Call service to delete user
      const result = await this.authService.deleteAccount(user.id);
      if (result.success) {
        return ApiResponse.success(null, "Account deleted successfully");
      } else {
        return ApiResponse.failure(
          result.message || "Failed to delete account",
          500
        );
      }
    } catch (error: any) {
      return ApiResponse.failure(
        error.message || "Failed to delete account",
        500
      );
    }
  }
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

      // Check file size (max 1MB) - Middleware might handle this better
      const maxSize = 1024 * 1024; // 1MB
      if ((file as File).size > maxSize) {
        return ApiResponse.failure("File size exceeds the 1MB limit.", 400, {
          avatar: "File size too large. Maximum allowed size is 1MB.",
        });
      }

      // Convert file to buffer
      const fileBuffer = Buffer.from(await (file as File).arrayBuffer());

      // Generate signature for signed upload
      const user = c.get("user");
      const params = {
        folder: `avatars/${user.id}`,
        public_id: user.referralCode ?? `${user.id}_avatar`,
        overwrite: true,
      };

      const { signature, timestamp } = generateCloudinarySignature(params);

      // Add signature to upload options
      const uploadOptions = {
        ...params,
        signature,
        timestamp,
        api_key: env("CLOUDINARY_API_KEY"),
        transformation: [
          { gravity: "face", height: 300, width: 300, crop: "thumb" },
          { radius: 1000 },
          { overlay: { resource_type: "image", public_id: "logo_kbhbqo" } },
          {
            flags: "layer_apply",
            gravity: "south_east",
            x: 10,
            y: 10,
            width: 50,
          },
          { opacity: 100 },
          { quality: "auto" },
          { fetch_format: "webp" },
        ],
      };

      // Upload with signed params
      const result = await new Promise<UploadApiResponse>((resolve, reject) => {
        const uploadStream = cloudinary.uploader.upload_stream(
          uploadOptions,
          (error, result) => {
            if (error) reject(error);
            else resolve(result as UploadApiResponse);
          }
        );
        bufferToStream(fileBuffer).pipe(uploadStream);
      });

      // Update database using UserService
      const userService = container.get(UserService);
      const updatedUser = await userService.updateAvatarUrl(
        user.id,
        result.secure_url
      );

      return ApiResponse.success(updatedUser, "Avatar updated successfully");
    } catch (error: any) {
      console.error("Avatar Upload Error:", error);
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

      const result = await this.authService.sendOtp(body.email);
      return ApiResponse.success(null, result.message);
    } catch (error: any) {
      if (error.errors) {
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      console.error("Send OTP error:", error);
      return ApiResponse.failure(error.message || "Failed to send OTP", 500);
    }
  }

  async verifyOtp(c: Context) {
    try {
      const body = await c.req.json();

      const result = await this.authService.verifyOtp(body.email, body.otp);

      if (!result.success) {
        return ApiResponse.failure(result.message, 400);
      }

      return ApiResponse.success(null, result.message);
    } catch (error: any) {
      if (error.errors) {
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      console.error("Verify OTP error:", error);
      return ApiResponse.failure(error.message || "Failed to verify OTP", 500);
    }
  }

  async resetPassword(c: Context) {
    try {
      const body = await c.req.json();

      const result = await this.authService.resetPassword(body);
      return ApiResponse.success(null, result.message, 201);
    } catch (error: any) {
      if (error.errors) {
        return ApiResponse.failure("Validation error", 422, error.errors);
      }
      console.error("Reset Password error:", error);
      return ApiResponse.failure(
        error.message || "Failed to reset password",
        500
      );
    }
  }
}
