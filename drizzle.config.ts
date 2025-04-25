import { defineConfig } from "drizzle-kit";
import { dbUrl } from "./src/config/database";

export default defineConfig({
  dialect: "mysql",
  schema: "./src/database/schemas/index.ts",
  out: "./src/database/migrations",
  // outDir: "./src/database/schemas",

  introspect: {
    casing: "preserve",
  },

  dbCredentials: {
    url: dbUrl,
  },
});
