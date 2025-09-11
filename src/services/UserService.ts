import { injectable } from "inversify";
import { customers as users } from "../database/migrations/schema";
import { eq, count, or, like } from "drizzle-orm";
import { CustomerResource } from "../resources/CustomerResource";
import { db } from "../config/database";
import { ApiResponse } from "../utils/ApiResponse";

@injectable()
export class UserService {
  // No constructor injection for database

  async getAllUsers(page: number, perPage: number, search: string) {
    const offset = (page - 1) * perPage;

    // Prepare the search filter
    const searchFilter = search
      ? or(
          like(users.firstname, `%${search}%`),
          like(users.lastname, `%${search}%`),
          like(users.email, `%${search}%`)
        )
      : undefined;

    // Fetch users with optional search filter
    const userList = await db
      .select()
      .from(users)
      .where(searchFilter)
      .limit(perPage)
      .offset(offset);

    // Fetch total count based on the search filter
    const [{ count: totalUsers }] = await db
      .select({ count: count() })
      .from(users)
      .where(searchFilter);

    return CustomerResource.toCollection(userList, totalUsers, page, perPage);
  }

  async getUserById(id: number) {
    const user = await db.select().from(users).where(eq(users.id, id)).limit(1);
    if (!user.length) throw ApiResponse.failure("User not found", 404);
    return CustomerResource.toResource(user[0]);
  }

  async createUser(data: any) {
    const newUserId = await db.insert(users).values(data).$returningId();
    return this.getUserById(newUserId[0] as number);
  }
  async updateAvatarUrl(userId: number, avatarUrl: string) {
    // Update the user avatar
    await db
      .update(users)
      .set({
        avatar: avatarUrl,
        updated_at: new Date().toISOString().slice(0, 19).replace("T", " "),
      })
      .where(eq(users.id, userId));

    // Use the same pattern as getUserById to fetch and return updated user
    return this.getUserById(userId);
  }
}
