import { ZodSchema } from "zod";
import { zValidator as zv } from "@hono/zod-validator";
import type { ValidationTargets } from "hono";
import { ApiResponse } from "../utils/ApiResponse";

export const zValidator = <
  T extends ZodSchema,
  Target extends keyof ValidationTargets
>(
  target: Target,
  schema: T
) =>
  zv(target, schema, (result, c) => {
    if (!result.success) {
      // Convert the validation errors to an object with field names as keys
      const formattedErrors = result.error.errors.reduce((acc, err) => {
        const key = err.path.join(".");
        acc[key] = err.message;
        return acc;
      }, {} as Record<string, string>);

      // Return a structured error response
      return ApiResponse.failure("Validation error", 422, formattedErrors);
    }
  });
