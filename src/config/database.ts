import "dotenv/config";
import { env } from "./env";
import { drizzle } from "drizzle-orm/mysql2";
import * as mysql from "mysql2/promise";

const dbUrl = env("DATABASE_URL", undefined, { required: true });
const PORT = env("PORT", "3003");

const JWT_SECRET = env("JWT_SECRET", "supersecret");

// Create a connection pool
const poolConnection = mysql.createPool({ uri: dbUrl });

// Create drizzle instance with the pool
const db = drizzle(poolConnection);

export { db, dbUrl, PORT, JWT_SECRET };
