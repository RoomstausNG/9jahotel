import { Context } from "hono";
import { HTTPException } from "hono/http-exception";
import { ZodError } from "zod";

export const errorHandler = async (c: Context, next: Function) => {
  try {
    await next();
  } catch (err) {
    if (err instanceof HTTPException) {
      return c.json({ error: err.message }, err.status);
    }
    if (err instanceof ZodError) {
      const errors = err.flatten().fieldErrors;
      return c.json({ errors }, 422);
    }
    return c.json({ error: "Internal Server Error" }, 500);
  }
};
