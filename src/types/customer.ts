import { z } from "zod";
import { InferSelectModel } from "drizzle-orm";
import { customers } from "../database/migrations/schema";

// Customer model type from database schema
export type Customer = InferSelectModel<typeof customers>;

// Create a type for customer response with only specific fields
export type CustomerResponse = Pick<
  Customer,
  | "id"
  | "firstname"
  | "lastname"
  | "email"
  | "phone"
  | "referral_code"
  | "address"
  | "parent_id"
  | "latitude"
  | "longitude"
  | "avatar"
>;

// If you have DTOs, you can import and infer them like this:
export type CreateCustomerDtoType = z.infer<
  typeof import("../dtos/UserDTO").CreateUserDto
>;
export type UpdateCustomerDtoType = z.infer<
  typeof import("../dtos/UserDTO").UpdateUserDto
>;
