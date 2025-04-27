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

// const db = drizzle(dbUrl as string);

// // Create drizzle instance with the pool
// const dbPool = drizzle(poolConnection);

// export { db, dbUrl, PORT, JWT_SECRET };

import "dotenv/config";
import { env } from "./env";
import { drizzle } from "drizzle-orm/mysql2"; // we'll conditionally import
import * as mysql from "mysql2/promise";
// import { Pool as PgPool } from "pg";
// import Database from "better-sqlite3";

// Env variables
const DB_CONNECTION = env("DB_CONNECTION", "mysql");
const DB_URL = env("DATABASE_URL", undefined);

const DB_HOST = env("DB_HOST", "127.0.0.1");
const DB_PORT = env("DB_PORT", "3306");
const DB_DATABASE = env("DB_DATABASE", "hono");
const DB_USERNAME = env("DB_USERNAME", "hono");
const DB_PASSWORD = env("DB_PASSWORD", "");

// Variables
let db: any;
let client: any;

// Setup connections
if (DB_CONNECTION === "mysql") {
  const connectionConfig = DB_URL
    ? DB_URL
    : {
        host: DB_HOST,
        port: parseInt(DB_PORT, 10),
        database: DB_DATABASE,
        user: DB_USERNAME,
        password: DB_PASSWORD,
        waitForConnections: true,
        connectionLimit: 10,
        queueLimit: 0,
      };

  const pool = mysql.createPool(connectionConfig);
  db = drizzle(pool);
  client = pool;
} else if (
  DB_CONNECTION === "pgsql" ||
  DB_CONNECTION === "postgres" ||
  DB_CONNECTION === "postgresql"
) {
  const connectionString = DB_URL
    ? { connectionString: DB_URL }
    : {
        host: DB_HOST,
        port: parseInt(DB_PORT, 10),
        database: DB_DATABASE,
        user: DB_USERNAME,
        password: DB_PASSWORD,
      };

  const pool = new PgPool(connectionString);
  db = pool; // You can integrate drizzle/postgres adapter if needed
  client = pool;
} else if (DB_CONNECTION === "sqlite") {
  const databasePath = DB_DATABASE || "./database.sqlite";
  const sqlite = new Database(databasePath);
  db = sqlite;
  client = sqlite;
} else {
  throw new Error(`Unsupported DB_CONNECTION: ${DB_CONNECTION}`);
}

export { db, client };
