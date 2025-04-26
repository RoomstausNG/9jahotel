import { Context } from "hono";
import { setCookie } from "hono/cookie";

export function clearAuthCookie(c: Context, cookieName: string) {
  setCookie(c, cookieName, "", {
    maxAge: 0,
    expires: new Date(0),
    path: "/",
    httpOnly: true,
    secure: process.env.NODE_ENV === "production",
    sameSite: "strict",
  });
  // Optionally, try clearing with domain if you set it productionally
  setCookie(c, cookieName, "", {
    maxAge: 0,
    expires: new Date(0),
    path: "/",
    httpOnly: true,
    secure: process.env.NODE_ENV === "production",
    sameSite: "strict",
    domain: process.env.COOKIE_DOMAIN, // set this if you use a custom domain
  });
}
