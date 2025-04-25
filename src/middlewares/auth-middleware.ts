import { Context, Next } from "hono";
import { AuthService } from "../services/AuthService";
import { ApiResponse } from "../utils/ApiResponse";
import { getCookie } from "hono/cookie";
import { getToken } from "../utils/getToken";

async function authMiddleware(c: Context, next: Next) {
  const token = getToken(c);

  if (!token) {
    return ApiResponse.failure("Unauthenticated", 401, {
      token: "Invalid or expired token, login again",
    });
  }

  const user = await AuthService.getAuthUser(token);
  if (!user) {
    return ApiResponse.failure("Unauthenticated", 401, {
      token: "Invalid or expired token, login again",
    });
  }

  c.set("user", user);
  await next();
}

const currentUserMiddleware = async (c: Context, next: Next) => {
  const token = getToken(c);
  if (!token) return ApiResponse.failure("Unauthorized", 401);

  const user = await AuthService.getAuthUser(token);
  if (!user) return ApiResponse.failure("Invalid token", 401);

  c.set("user", user);
  await next();
};

export { authMiddleware, currentUserMiddleware };
