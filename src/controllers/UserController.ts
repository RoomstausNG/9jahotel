import { inject, injectable } from "inversify";
import { Context } from "hono";
import { UserService } from "../services/UserService";
import { ApiResponse } from "../utils/ApiResponse";
import { generateCloudinarySignature } from "../utils/cloudinaryUtils";
import { env } from "../config/env";

@injectable()
export class UserController {
  constructor(@inject(UserService) private userService: UserService) {}

  async index(c: Context) {
    try {
      const page = Number(c.req.query("page") || 1);
      const perPage = Number(c.req.query("perPage") || 24);

      // all search terms
      const searchParams = c.req.query("search") || "";

      const users = await this.userService.getAllUsers(
        page,
        perPage,
        searchParams
      );
      return ApiResponse.success(users, "User fetch successfully");
    } catch (error: any) {
      return ApiResponse.failure(error.message, 500);
    }
  }

  async show(c: Context) {
    try {
      const idParam = c.req.param("id");
      const id = Number(idParam);

      // Check if id is a valid number
      if (isNaN(id) || id <= 0) {
        return ApiResponse.failure(
          "Invalid user ID, the id must be a number",
          400
        );
      }

      const user = await this.userService.getUserById(id);
      return ApiResponse.success(user);
    } catch (error: any) {
      if (error instanceof Response) {
        return error;
      }
      return ApiResponse.failure(
        "Internal Server Error while fetching user",
        500
      );
    }
  }

  async store(c: Context) {
    try {
      const body = await c.req.json();
      const user = await this.userService.createUser(body);
      return ApiResponse.success(user, "User created successfully", 201);
    } catch (error: any) {
      return ApiResponse.failure(error.message, 400);
    }
  }

  async generateSignature(c: Context) {
    try {
      const user = c.get("user");
      if (!user || !user.id) {
        return ApiResponse.failure("User not authenticated", 401);
      }

      const params = {
        folder: `avatars/${user.id}`,
        public_id: user.referralCode ?? `${user.id}_avatar`,
        overwrite: true,
      };

      const { signature, timestamp } = generateCloudinarySignature(params);

      return ApiResponse.success(
        {
          signature,
          timestamp,
          api_key: env("CLOUDINARY_API_KEY"),
          cloud_name: env("CLOUDINARY_CLOUD_NAME"),
          ...params,
        },
        "Signature generated successfully"
      );
    } catch (error: any) {
      return ApiResponse.failure(
        error.message || "Failed to generate signature",
        500
      );
    }
  }

  async updateAvatarUrl(c: Context) {
    try {
      const user = c.get("user");
      if (!user || !user.id) {
        return ApiResponse.failure("User not authenticated", 401);
      }

      const body = await c.req.json();
      const { url } = body;

      if (!url) {
        return ApiResponse.failure("URL is required", 400);
      }

      // Ensure user.id is a number
      const userId = Number(user.id);
      if (isNaN(userId)) {
        return ApiResponse.failure("Invalid user ID", 400);
      }

      const updatedUser = await this.userService.updateAvatarUrl(userId, url);
      return ApiResponse.success(
        updatedUser,
        "Avatar URL updated successfully"
      );
    } catch (error: any) {
      console.error("Avatar update error:", error);
      return ApiResponse.failure(
        error.message || "Failed to update avatar URL",
        500
      );
    }
  }
}
