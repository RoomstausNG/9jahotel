import { injectable } from "inversify";
import { customers } from "../database/migrations/schema";
import { eq, or } from "drizzle-orm";
import { UserResource } from "../resources/UserResource";
import { db } from "../config/database";
import { ApiResponse } from "../utils/ApiResponse";

@injectable()
export class CustomerService {
  /**
   * Find a customer by email or phone
   * @param identifier - Email or phone number
   * @returns Customer details or null if not found
   */
  async findCustomerByEmailOrPhone(identifier: string) {
    try {
      // Clean the identifier (remove spaces, special chars for phone)
      const cleanIdentifier = identifier.trim();

      // Search for customer by email or phone
      const customer = await db
        .select()
        .from(customers)
        .where(
          or(
            eq(customers.email, cleanIdentifier),
            eq(customers.phone, cleanIdentifier)
          )
        )
        .limit(1);

      if (!customer.length) {
        return null;
      }

      return UserResource.toResource(customer[0]);
    } catch (error: any) {
      console.error("Error finding customer:", error);
      throw new Error("Failed to find customer");
    }
  }

  /**
   * Find a customer by email
   * @param email - Customer email
   * @returns Customer details or null if not found
   */
  async findCustomerByEmail(email: string) {
    try {
      const customer = await db
        .select()
        .from(customers)
        .where(eq(customers.email, email.trim()))
        .limit(1);

      if (!customer.length) {
        return null;
      }

      return UserResource.toResource(customer[0]);
    } catch (error: any) {
      console.error("Error finding customer by email:", error);
      throw new Error("Failed to find customer by email");
    }
  }

  /**
   * Find a customer by phone
   * @param phone - Customer phone number
   * @returns Customer details or null if not found
   */
  async findCustomerByPhone(phone: string) {
    try {
      const customer = await db
        .select()
        .from(customers)
        .where(eq(customers.phone, phone.trim()))
        .limit(1);

      if (!customer.length) {
        return null;
      }

      return UserResource.toResource(customer[0]);
    } catch (error: any) {
      console.error("Error finding customer by phone:", error);
      throw new Error("Failed to find customer by phone");
    }
  }

  /**
   * Get customer by ID
   * @param id - Customer ID
   * @returns Customer details
   */
  async getCustomerById(id: number) {
    try {
      const customer = await db
        .select()
        .from(customers)
        .where(eq(customers.id, id))
        .limit(1);

      if (!customer.length) {
        throw ApiResponse.failure("Customer not found", 404);
      }

      return UserResource.toResource(customer[0]);
    } catch (error: any) {
      if (error instanceof Response) {
        throw error;
      }
      console.error("Error getting customer by ID:", error);
      throw new Error("Failed to get customer");
    }
  }
}
