// import "dotenv/config";
// import { env } from "./env";
// import { drizzle } from "drizzle-orm/mysql2";
// import * as mysql from "mysql2/promise";

// const dbUrl = env("DATABASE_URL");
// const PORT = env("PORT", "3003");

// const JWT_SECRET = env("JWT_SECRET", "supersecret");

// // Create a connection pool
// const poolConnection = mysql.createPool({
//   host: "host",
//   user: "user",
//   database: "database",
// });

// // Create drizzle instance with the pool
// const dbPool = drizzle(poolConnection);

// export { db, dbUrl, PORT, JWT_SECRET };
import "dotenv/config";
import { env } from "./env";
import { AnyMySql2Connection, drizzle } from "drizzle-orm/mysql2"; // drizzle for MySQL
import * as mysql from "mysql2/promise";
import { Pool as PgPool } from "pg";
import { drizzle as sqliteDrizzle } from "drizzle-orm/better-sqlite3";
// Env variables
export const DB_CONNECTION = env("DB_CONNECTION", "mysql");
export const DB_URL: string | null = env("DATABASE_URL");
export const DB_HOST = env("DB_HOST", "127.0.0.1");
export const DB_PORT: number = parseInt(env("DB_PORT", "3306"));
export const DB_DATABASE = env("DB_DATABASE", "hono");
export const DB_USERNAME = env("DB_USERNAME", "hono");
export const DB_PASSWORD = env("DB_PASSWORD", "");

// Initialize DB_CONFIG based on connection type
let dbConfigValue: any;

if (DB_CONNECTION.toLowerCase() === "sqlite") {
  const databasePath = DB_DATABASE || "./database.sqlite";
  dbConfigValue = { path: databasePath };
} else if (
  DB_CONNECTION === "mysql" ||
  DB_CONNECTION === "pgsql" ||
  DB_CONNECTION === "postgres" ||
  DB_CONNECTION === "postgresql"
) {
  dbConfigValue = DB_URL
    ? { url: DB_URL }
    : {
        host: DB_HOST,
        port: DB_PORT,
        user: DB_USERNAME,
        password: DB_PASSWORD,
        database: DB_DATABASE,
        ssl: true,
      };
} else {
  throw new Error(`Unsupported DB_CONNECTION: ${DB_CONNECTION}`);
}

// Export the config as a constant
export const DB_CONFIG = dbConfigValue;

const poolConnection = mysql.createPool(dbConfigValue);

// Exported variables
let db: any;
db = drizzle(DB_URL as string);
let dbPool = drizzle(poolConnection);
let client: any;

export { db, dbPool, client };
