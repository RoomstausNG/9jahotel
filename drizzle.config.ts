import { defineConfig } from "drizzle-kit";
import { DB_CONNECTION, DB_CONFIG } from "./src/config/database";

// Map DB_CONNECTION to the correct dialect value expected by Drizzle
const dialectMap: Record<string, string> = {
  postgres: "postgresql",
  postgresql: "postgresql",
  mysql: "mysql",
  sqlite: "sqlite",
};

// Use the mapped dialect or fallback to the original value
const dialect = dialectMap[DB_CONNECTION] || DB_CONNECTION;

export default defineConfig({
  dialect: dialect as
    | "mysql"
    | "sqlite"
    | "postgresql"
    | "turso"
    | "singlestore"
    | "gel",
  schema: "./src/database/schemas/index.ts",
  out: "./src/database/migrations",
  // outDir: "./src/database/schemas",

  introspect: {
    casing: "preserve",
  },

  dbCredentials: DB_CONFIG,
});
