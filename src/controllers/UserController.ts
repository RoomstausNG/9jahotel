import { inject, injectable } from "inversify";
import { Context } from "hono";
import { UserService } from "../services/UserService";
import { ApiResponse } from "../utils/ApiResponse";

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
}
