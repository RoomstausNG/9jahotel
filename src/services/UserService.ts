import { injectable } from "inversify";
import { users } from "../database/migrations/schema";
import { eq, count, or, like } from "drizzle-orm";
import { UserResource } from "../resources/UserResource";
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
          like(users.username, `%${search}%`)
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

    return UserResource.toCollection(userList, totalUsers, page, perPage);
  }

  async getUserById(id: number) {
    const user = await db.select().from(users).where(eq(users.id, id)).limit(1);
    if (!user.length) throw ApiResponse.failure("User not found", 404);
    return UserResource.toResource(user[0]);
  }

  async createUser(data: any) {
    const newUserId = await db.insert(users).values(data).$returningId();
    return this.getUserById(newUserId[0] as number);
  }
}
