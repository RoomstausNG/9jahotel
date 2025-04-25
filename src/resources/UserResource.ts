import { User } from "../types/user";
import { users } from "../database/migrations/schema";

export class UserResource {
  // Transform a single user with optional field selection
  static toResource(user: User, fields?: string[]) {
    // Default resource shape
    const resource = {
      id: user.id,
      firstName: user.firstname,
      lastName: user.lastname,
      userName: user.username,
      email: user.email,
      phone: user.phone,
      avatar: user.avatar,
      designation: user.designation,
      departmentId: user.department_id,
      createdAt: user.created_at,
      updatedAt: user.updated_at,
    };

    // If specific fields are requested, filter to only those fields
    if (fields && fields.length > 0) {
      return Object.fromEntries(
        Object.entries(resource).filter(([key]) => fields.includes(key))
      );
    }

    return resource;
  }

  // Transform a collection with pagination
  static toCollection(
    users: User[],
    total: number,
    page: number,
    perPage: number,
    fields?: string[]
  ) {
    // Map users to resources
    const mappedUsers = users.map((user) => this.toResource(user, fields));

    // Base response without pagination
    const response: any = {
      users: mappedUsers,
    };

    // Only include pagination metadata if total exceeds perPage
    if (total > perPage) {
      const lastPage = Math.ceil(total / perPage);
      const hasMorePages = page < lastPage;
      const isFirstPage = page === 1;

      response.meta = {
        pagination: {
          total,
          count: users.length,
          per_page: perPage,
          current_page: page,
          total_pages: lastPage,
          has_more_pages: hasMorePages,
          first_page: isFirstPage,
          last_page: page === lastPage,
          next_page: hasMorePages ? page + 1 : null,
          prev_page: page > 1 ? page - 1 : null,
        },
      };
    }

    return response;
  }

  // Method for detailed user profile (when more data is needed)
  static toDetailedResource(user: User) {
    return {
      ...this.toResource(user),
      dateOfBirth: user.date_of_birth,
      dateOfEmployment: user.date_of_employment,
      address: user.address,
      gender: user.gender,
    };
  }
}
