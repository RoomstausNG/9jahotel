import { env } from "./env";

const dbUrl = env("DATABASE_URL", undefined, { required: true });

// Get port with a default value
const PORT = env("PORT", "3003");

export { dbUrl, PORT };
