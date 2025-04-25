import { Context } from "hono";

export const getToken = (c: Context): string | undefined => {
  if (!c || !c.req || typeof c.req.header !== "function") return undefined;
  const authHeader = c.req.header("Authorization");
  return authHeader && authHeader.startsWith("Bearer ")
    ? authHeader.split(" ")[1]
    : undefined;
};
