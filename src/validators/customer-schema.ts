import { z } from "zod";
import { db } from "../config/database";
import { customers } from "../database/migrations/schema";
import { eq } from "drizzle-orm";

// Email validation function
const uniqueCustomerEmail = async (email: string): Promise<boolean> => {
  const existingCustomer = await db
    .select()
    .from(customers)
    .where(eq(customers.email, email));
  return existingCustomer.length === 0;
};

// Customer schema based on the database schema
export const customerSchema = z.object({
  firstname: z.string().min(2, "First name must be at least 2 characters"),
  lastname: z.string().min(2, "Last name must be at least 2 characters"),
  phone: z.string().min(10, "Phone number must be at least 10 characters"),
  email: z.string().email("Invalid email address").refine(uniqueCustomerEmail, {
    message: "Email address already exists",
  }),
  password: z.string().min(6, "Password must be at least 6 characters"),
  referral_code: z.string().optional(),
});

// Login schema
export const loginSchema = z.object({
  email: z.string().email("Invalid email address"),
  password: z.string().min(1, "Password is required"),
});
