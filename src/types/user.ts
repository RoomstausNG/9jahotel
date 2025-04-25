import { z } from "zod";
import { InferSelectModel } from "drizzle-orm";
import { users } from "../database/migrations/schema";

// User model type from database schema
export type User = InferSelectModel<typeof users>;

// User DTO Types (from UserDTO.ts)
export type CreateUserDtoType = z.infer<typeof import("../dtos/UserDTO").CreateUserDto>;
export type UpdateUserDtoType = z.infer<typeof import("../dtos/UserDTO").UpdateUserDto>;
export type UserResponseDtoType = z.infer<typeof import("../dtos/UserDTO").UserResponseDto>;