import { inject, injectable } from "inversify";
import { Context } from "hono";
import { CustomerService } from "../services/CustomerService";
import { ApiResponse } from "../utils/ApiResponse";

@injectable()
export class CustomerController {
  constructor(
    @inject(CustomerService) private customerService: CustomerService
  ) {}

  /**
   * Check if a customer exists by email or phone
   * This endpoint is not guarded by auth middleware
   * @param c - Hono context
   * @returns Customer details if found, or not found response
   */
  async checkCustomerExists(c: Context) {
    try {
      const { identifier } = await c.req.json();

      // Validate input
      if (!identifier || typeof identifier !== "string") {
        return ApiResponse.failure(
          "Identifier (email or phone) is required",
          400
        );
      }

      // Clean and validate identifier
      const cleanIdentifier = identifier.trim();
      if (!cleanIdentifier) {
        return ApiResponse.failure("Invalid identifier provided", 400);
      }

      // Check if customer exists
      const customer = await this.customerService.findCustomerByEmailOrPhone(
        cleanIdentifier
      );

      if (!customer) {
        return ApiResponse.failure("Customer not found", 404);
      }

      return ApiResponse.success(customer, "Customer found successfully");
    } catch (error: any) {
      console.error("Error checking customer exists:", error);
      return ApiResponse.failure(
        "Internal server error while checking customer",
        500
      );
    }
  }

  /**
   * Get customer details by ID
   * @param c - Hono context
   * @returns Customer details
   */
  async show(c: Context) {
    try {
      const idParam = c.req.param("id");
      const id = Number(idParam);

      // Check if id is a valid number
      if (isNaN(id) || id <= 0) {
        return ApiResponse.failure(
          "Invalid customer ID, the id must be a number",
          400
        );
      }

      const customer = await this.customerService.getCustomerById(id);
      return ApiResponse.success(customer, "Customer retrieved successfully");
    } catch (error: any) {
      if (error instanceof Response) {
        return error;
      }
      console.error("Error fetching customer:", error);
      return ApiResponse.failure(
        "Internal Server Error while fetching customer",
        500
      );
    }
  }

  /**
   * Find customer by email (separate endpoint)
   * @param c - Hono context
   * @returns Customer details or not found
   */
  async findByEmail(c: Context) {
    try {
      const email = c.req.query("email");

      if (!email || typeof email !== "string") {
        return ApiResponse.failure("Email parameter is required", 400);
      }

      const customer = await this.customerService.findCustomerByEmail(email);

      if (!customer) {
        return ApiResponse.failure("Customer not found", 404);
      }

      return ApiResponse.success(customer, "Customer found successfully");
    } catch (error: any) {
      console.error("Error finding customer by email:", error);
      return ApiResponse.failure(
        "Internal server error while finding customer",
        500
      );
    }
  }

  /**
   * Find customer by phone (separate endpoint)
   * @param c - Hono context
   * @returns Customer details or not found
   */
  async findByPhone(c: Context) {
    try {
      const phone = c.req.query("phone");

      if (!phone || typeof phone !== "string") {
        return ApiResponse.failure("Phone parameter is required", 400);
      }

      const customer = await this.customerService.findCustomerByPhone(phone);

      if (!customer) {
        return ApiResponse.failure("Customer not found", 404);
      }

      return ApiResponse.success(customer, "Customer found successfully");
    } catch (error: any) {
      console.error("Error finding customer by phone:", error);
      return ApiResponse.failure(
        "Internal server error while finding customer",
        500
      );
    }
  }
}
