import { z } from "zod";
import { db } from "../config/database";
import { rooms } from "../database/migrations/schema";
import { eq } from "drizzle-orm";

/**
 * Validator to check if a room exists in the database
 * @param id Room ID to validate
 * @returns The room ID if valid
 */
export const existsRoom = async (id: number): Promise<number> => {
  // Using select syntax instead of findFirst
  const result = await db
    .select({ id: rooms.id })
    .from(rooms)
    .where(eq(rooms.id, id))
    .limit(1);

  if (!result.length) {
    throw new Error(`Room with ID ${id} does not exist`);
  }

  return id;
};

/**
 * Zod validator to check if an array of room IDs exist in the database
 */
export const existsRoomsValidator = z.array(z.number()).refine(
  async (ids) => {
    try {
      // Check each room ID
      await Promise.all(ids.map(existsRoom));
      return true;
    } catch (error) {
      return false;
    }
  },
  {
    message: "Invalid room number. One or more rooms do not exist.",
  }
);
